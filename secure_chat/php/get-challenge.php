<?php
    session_start();
    require_once '../db.php'; // make sure $conn is available (MySQLi connection)

    header('Content-Type: application/json');

    // Helper to base64url-encode
    function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Step 1: Check if user is logged in (by username)
    if (!isset($_SESSION['username'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    $username = $_SESSION['username'];

    // Step 2: Lookup user ID from database (prepared statement, procedural MySQLi)
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: prepare failed']);
        exit;
    }

    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userId);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // If user not found
    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Step 3: Generate random challenge (32 bytes)
    $challenge = random_bytes(32);
    $_SESSION['register_challenge'] = $challenge; // store raw binary challenge

    // Step 4: Prepare WebAuthn registration options
    echo json_encode([
        'challenge' => base64url_encode($challenge),
        'rp' => [
            'name' => 'My Secure App'
        ],
        'user' => [
            'id' => base64url_encode((string)$userId), // convert to string then encode
            'name' => $username,
            'displayName' => $username
        ],
        'pubKeyCredParams' => [
            ['type' => 'public-key', 'alg' => -7] // ES256
        ],
        'authenticatorSelection' => [
            'userVerification' => 'preferred'
        ],
        'attestation' => 'none',
        'timeout' => 60000
    ]);
?>