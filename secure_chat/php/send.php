<?php
    require_once '../db.php';

    $sender = $_POST['sender'];
    $enc_msg = $_POST['message'];

    $sender = mysqli_real_escape_string($conn, $sender);
    $enc_msg = mysqli_real_escape_string($conn, $enc_msg);

    $sql = "SELECT id FROM users WHERE username = '$sender'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $sender_id = $row['id'];
    $receiver_id = $sender_id; // for now send to self

    $sql = "INSERT INTO messages (sender_id, receiver_id, encrypted_msg) VALUES ($sender_id, $receiver_id, '$enc_msg')";
    mysqli_query($conn, $sql);
?>