<?php
require_once '../db.php';
session_start();

// Get current user from session
$current_user_id = $_SESSION['user_id'] ?? 0;

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Extract values from JSON
$receiver_id = intval($data['receiver_id'] ?? 0);
$encrypted_msg = mysqli_real_escape_string($conn, $data['message'] ?? '');

// Validate required fields
if (!$current_user_id || !$receiver_id || !$encrypted_msg) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// Insert the message
$sql = "INSERT INTO messages (sender_id, receiver_id, encrypted_msg, timestamp) 
        VALUES ($current_user_id, $receiver_id, '$encrypted_msg', NOW())";

if (mysqli_query($conn, $sql)) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
}
?>