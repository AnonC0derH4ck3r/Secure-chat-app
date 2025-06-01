<?php
    // starts the session
    session_start();

    // database connection file
    require_once '../db.php';

    // Tells the browser, the response is in JSON format
    header('Content-Type: application/json');

    // Read JSON input
    $data = json_decode(file_get_contents('php://input'), true);

    // if username is empty
    if (empty($data['username'])) {

        // return 400 (Bad Request)
        http_response_code(400);

        // returns appropriate message
        echo json_encode(['success' => false, 'message' => 'Username required']);

        // stops the script
        exit;
    }


    // $username has the value given by the user
    $username = $data['username'];

    // Prepare statement to get credential_id and public_key for the username
    $stmt = mysqli_prepare($conn, "SELECT credential_id, public_key FROM passkeys WHERE username = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    // if there is no user with the username
    if (mysqli_stmt_num_rows($stmt) === 0) {
        // close the statement
        mysqli_stmt_close($stmt);

        // returns no user found
        echo json_encode(['success' => false, 'message' => 'User not found']);

        // terminates the script
        exit;
    }

    // if username is found
    // it binds the results to variables $credentialId, $publicKey
    mysqli_stmt_bind_result($stmt, $credentialId, $publicKey);
    // fetch the results
    mysqli_stmt_fetch($stmt);

    // closes the statement
    mysqli_stmt_close($stmt);

    // Function to do base64url encoding
    function base64url_encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Credential ID is binary data stored in DB, so encode as base64url
    $credentialIdB64 = base64url_encode($credentialId);

    // Generate a cryptographically random challenge (32 bytes)
    $challenge = random_bytes(32);
    $challengeB64 = base64url_encode($challenge);

    // Save challenge in session for verification later
    $_SESSION['login_challenge'] = $challengeB64;

    // returns the challenge to the client
    $response = [
        'success' => true,
        'publicKey' => [
            'challenge' => $challengeB64,
            'timeout' => 60000,
            'rpId' => $_SERVER['HTTP_HOST'],
            'allowCredentials' => [[
                'type' => 'public-key',
                'id' => $credentialIdB64,
                'transports' => ['internal', 'usb', 'ble', 'nfc'],
            ]],
            'userVerification' => 'preferred',
        ]
    ];

    // Returns in json format
    echo json_encode($response);
?>