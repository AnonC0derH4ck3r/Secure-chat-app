<?php
    require_once '../db.php';

    session_start();

    header('Content-Type: application/json');

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in'
        ]);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    $sql = "SELECT username, profile_path FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $username, $profile_pic);
        
        if (mysqli_stmt_fetch($stmt)) {
            echo json_encode([
                'success' => true,
                'user' => [
                    'username' => $username,
                    'profile_pic' => $profile_pic ? $profile_pic : null
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'User not found'
            ]);
        }

        mysqli_stmt_close($stmt);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database error'
        ]);
    }

    mysqli_close($conn);
?>