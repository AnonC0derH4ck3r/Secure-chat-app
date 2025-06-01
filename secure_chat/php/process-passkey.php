<?php
session_start();
require_once '../db.php';

header('Content-Type: application/json');

// Base64url decode/encode helpers
function base64url_decode($data) {
    $data .= str_repeat('=', (4 - strlen($data) % 4) % 4);
    return base64_decode(strtr($data, '-_', '+/'));
}
function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Receive POST JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['response']['clientDataJSON'], $data['response']['attestationObject'], $data['rawId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$clientDataJSON = base64url_decode($data['response']['clientDataJSON']);
$attestationObject = base64url_decode($data['response']['attestationObject']);
$rawId = base64url_decode($data['rawId']);

// Validate clientDataJSON
$client = json_decode($clientDataJSON, true);
if (!isset($client['type'], $client['challenge']) || $client['type'] !== 'webauthn.create') {
    echo json_encode(['success' => false, 'message' => 'Invalid client data type']);
    exit;
}
if (!isset($_SESSION['register_challenge'])) {
    echo json_encode(['success' => false, 'message' => 'No challenge in session']);
    exit;
}
$expectedChallenge = base64url_encode($_SESSION['register_challenge']);
if ($client['challenge'] !== $expectedChallenge) {
    echo json_encode(['success' => false, 'message' => 'Challenge mismatch']);
    exit;
}

function parse_cbor_value(string $bin, int &$offset) {
    if ($offset >= strlen($bin)) {
        throw new Exception("CBOR parsing error: offset out of bounds");
    }
    $byte = ord($bin[$offset++]);
    $major = $byte >> 5;
    $additional = $byte & 0x1F;

    // Decode length based on additional info
    if ($additional < 24) {
        $length = $additional;
    } elseif ($additional === 24) {
        // next 1 byte
        if ($offset + 1 > strlen($bin)) throw new Exception("CBOR length byte overflow");
        $length = ord($bin[$offset]);
        $offset += 1;
    } elseif ($additional === 25) {
        // next 2 bytes
        if ($offset + 2 > strlen($bin)) throw new Exception("CBOR length bytes overflow");
        $length = unpack("n", substr($bin, $offset, 2))[1];
        $offset += 2;
    } elseif ($additional === 26) {
        // next 4 bytes
        if ($offset + 4 > strlen($bin)) throw new Exception("CBOR length bytes overflow");
        $length = unpack("N", substr($bin, $offset, 4))[1];
        $offset += 4;
    } elseif ($additional === 27) {
        // next 8 bytes (big number, PHP can't handle easily, just throw)
        throw new Exception("CBOR length 8 bytes not supported");
    } else {
        throw new Exception("Unsupported CBOR additional info $additional");
    }

    switch ($major) {
        case 0: // unsigned int
            return $length;
        case 1: // negative int
            return -1 - $length;
        case 2: // byte string
            if ($offset + $length > strlen($bin)) throw new Exception("CBOR bytestring overflow");
            $val = substr($bin, $offset, $length);
            $offset += $length;
            return $val;
        case 3: // text string
            if ($offset + $length > strlen($bin)) throw new Exception("CBOR textstring overflow");
            $val = substr($bin, $offset, $length);
            $offset += $length;
            return $val;
        case 4: // array
            $arr = [];
            for ($i = 0; $i < $length; $i++) {
                $arr[] = parse_cbor_value($bin, $offset);
            }
            return $arr;
        case 5: // map
            $map = [];
            for ($i = 0; $i < $length; $i++) {
                $key = parse_cbor_value($bin, $offset);
                $val = parse_cbor_value($bin, $offset);
                $map[$key] = $val;
            }
            return $map;
        default:
            throw new Exception("Unsupported CBOR major type $major");
    }
}

$offset = 0;
$attObj = parse_cbor_value($attestationObject, $offset);

if (!isset($attObj['authData'])) {
    echo json_encode(['success' => false, 'message' => 'Missing authData in attestationObject']);
    exit;
}

$authData = $attObj['authData'];

// Parse authData according to WebAuthn spec
function parse_auth_data(string $authData): array {
    if (strlen($authData) < 37) {
        throw new Exception("authData too short");
    }
    $offset = 0;
    $rpIdHash = substr($authData, $offset, 32);
    $offset += 32;
    $flags = ord($authData[$offset++]);
    $signCount = unpack("N", substr($authData, $offset, 4))[1];
    $offset += 4;

    $attestedCredentialDataPresent = ($flags & 0x40) !== 0;
    if (!$attestedCredentialDataPresent) {
        throw new Exception("Attested credential data flag not set");
    }

    if (strlen($authData) < $offset + 18) {
        throw new Exception("authData too short for attested credential data");
    }

    $aaguid = bin2hex(substr($authData, $offset, 16));
    $offset += 16;

    $credIdLen = unpack("n", substr($authData, $offset, 2))[1];
    $offset += 2;

    $credentialId = substr($authData, $offset, $credIdLen);
    $offset += $credIdLen;

    $publicKeyBytes = substr($authData, $offset);

    return [
        'rpIdHash' => bin2hex($rpIdHash),
        'flags' => $flags,
        'signCount' => $signCount,
        'aaguid' => $aaguid,
        'credentialId' => $credentialId,
        'publicKeyBytes' => $publicKeyBytes,
    ];
}

try {
    $auth = parse_auth_data($authData);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

// Convert COSE key (publicKeyBytes) to PEM (assume ES256)
function coseKeyToPEM(string $cose): string {
    $offset = 0;
    $map = parse_cbor_value($cose, $offset);
    if (!is_array($map)) {
        throw new Exception("Invalid COSE key");
    }
    $x = $map[-2] ?? null;
    $y = $map[-3] ?? null;
    if ($x === null || $y === null) {
        throw new Exception("Missing x or y coordinates in COSE key");
    }
    // x and y are byte strings
    $ecPoint = "\x04" . $x . $y;

    // ASN.1 DER encoding for the uncompressed EC public key for P-256
    $der = "\x30\x59\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01" .
           "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x03\x42\x00" . $ecPoint;

    return "-----BEGIN PUBLIC KEY-----\n" .
           chunk_split(base64_encode($der), 64, "\n") .
           "-----END PUBLIC KEY-----\n";
}

try {
    $publicKeyPEM = coseKeyToPEM($auth['publicKeyBytes']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Invalid public key: ' . $e->getMessage()]);
    exit;
}

// Ensure user in session
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not in session']);
    exit;
}

$username = $_SESSION['username'];

// Lookup user id
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $userId);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Insert passkey info to DB
$stmt = mysqli_prepare($conn, "INSERT INTO passkeys (user_id, username, credential_id, public_key, sign_count, aaguid) VALUES (?, ?, ?, ?, ?, ?)");
$credId = $auth['credentialId'];    // Will store as it is RAW BINARY Attribute in Database.
$signCount = $auth['signCount'];
$aaguid = hex2bin($auth['aaguid']);

mysqli_stmt_bind_param($stmt, "isssis", $userId, $username, $credId, $publicKeyPEM, $signCount, $aaguid);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode([
    'success' => $ok,
    'message' => $ok ? 'Passkey registered successfully' : 'Database insert failed'
]);
?>