<?php
    session_start();
    echo json_encode([
        "user_id" => $_SESSION['user_id'] ?? null,
        "username" => $_SESSION['username'] ?? null
    ]);
?>