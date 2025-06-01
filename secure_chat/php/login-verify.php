<?php
    session_start();
    header('Content-Type: application/json');

    // Read input
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!isset($data['id'], $data['response']['clientDataJSON'], $data['response']['authenticatorData'], $data['response']['signature'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // === Decode values from base64url ===
    function base64url_decode($data) {
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        return base64_decode(str_pad($data, strlen($data) % 4 ? strlen($data) + 4 - strlen($data) % 4 : strlen($data), '=', STR_PAD_RIGHT));
    }

    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    $credentialId      = base64url_decode($data['id']);
    $clientDataJSON    = base64url_decode($data['response']['clientDataJSON']);
    $authenticatorData = base64url_decode($data['response']['authenticatorData']);
    $signature         = base64url_decode($data['response']['signature']);

    // === Verify stored challenge ===
    if (!isset($_SESSION['login_challenge'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No challenge stored']);
        exit;
    }
    $expectedChallenge = $_SESSION['login_challenge'];

    // === Parse clientDataJSON and verify challenge ===
    $clientData = json_decode($clientDataJSON, true);
    if (!$clientData || $clientData['type'] !== 'webauthn.get') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid clientDataJSON']);
        exit;
    }

    // Decode clientData.challenge from base64url to raw binary
    $decodedChallenge = base64url_decode($clientData['challenge']);
    if (base64url_encode($decodedChallenge) !== $expectedChallenge) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Challenge mismatch']);
        exit;
    }

    // === Connect to DB and fetch user by credential_id ===
    $mysqli = mysqli_connect("localhost", "root", "", "secure_chat");
    if (!$mysqli) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB connection failed']);
        exit;
    }

    $stmt = mysqli_prepare($mysqli, "SELECT username, public_key FROM passkeys WHERE credential_id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Prepare statement failed']);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "s", $credentialId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Credential ID not recognized']);
        exit;
    }

    mysqli_stmt_bind_result($stmt, $username, $publicKeyPem);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // === Verify signature ===
    // Hash clientDataJSON (SHA-256)
    $clientDataHash = hash('sha256', $clientDataJSON, true);

    // Create "signed data" = authenticatorData || hash(clientDataJSON)
    $signedData = $authenticatorData . $clientDataHash;

    // Verify using OpenSSL
    $pubKeyRes = openssl_pkey_get_public($publicKeyPem);
    $verified = openssl_verify($signedData, $signature, $pubKeyRes, OPENSSL_ALGO_SHA256);
    openssl_free_key($pubKeyRes);

    if ($verified !== 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Signature verification failed']);
        exit;
    }

    // === Success ===
    // Set session variables so user can access chat.php
    $_SESSION['username'] = $username;

    // Optionally fetch user_id from users table if exists
    $stmt = mysqli_prepare($mysqli, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_id);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['user_id'] = $user_id ?? null;  // null if not found

    echo json_encode([
        'success' => true,
        'message' => 'Authentication successful',
        'username' => $username
    ]);
    exit;
?>