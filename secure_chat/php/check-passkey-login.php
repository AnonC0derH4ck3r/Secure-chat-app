<?php
    require_once '../db.php';

    $data = json_decode(file_get_contents("php://input"), true);
    $username = $data['username'] ?? '';

    if (!$username) {
        echo json_encode(['success' => false, 'message' => 'No username provided']);
        exit;
    }

    $stmt = mysqli_prepare($conn, "SELECT id FROM passkeys WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    $hasPasskey = mysqli_stmt_num_rows($stmt) > 0;

    echo json_encode(['success' => true, 'hasPasskey' => $hasPasskey]);
?>