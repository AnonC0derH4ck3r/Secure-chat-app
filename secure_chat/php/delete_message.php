<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$msg_id = intval($_POST['message_id'] ?? 0);

if ($msg_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid message ID']);
    exit;
}

// Escape values to prevent SQL Injection
$msg_id_escaped = mysqli_real_escape_string($conn, $msg_id);

// Check if message belongs to this user
$sql = "SELECT sender_id FROM messages WHERE id = '$msg_id_escaped'";
$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Message not found']);
    exit;
}

$row = mysqli_fetch_assoc($result);

if (intval($row['sender_id']) !== $user_id) {
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Delete the message
$delete_sql = "DELETE FROM messages WHERE id = '$msg_id_escaped'";
if (mysqli_query($conn, $delete_sql)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete message']);
}
?>