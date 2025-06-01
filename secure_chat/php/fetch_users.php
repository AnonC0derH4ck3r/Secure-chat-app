<?php
    require_once '../db.php';

    session_start();
    $current_user_id = $_SESSION['user_id'] ?? 0;

    $sql = "SELECT id, username FROM users WHERE id != ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $current_user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    echo json_encode($users);
?>