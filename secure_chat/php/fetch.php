<?php
    session_start();
    header('Content-Type: application/json');

    require_once '../db.php';

    $current_user_id = $_SESSION['user_id'] ?? 0;
    $other_user_id = intval($_GET['receiver_id'] ?? 0);

    // Validate user IDs
    if ($current_user_id === 0 || $other_user_id === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid user IDs']);
        exit;
    }

    // Delete messages older than 24 hours
    $delete_sql = "DELETE FROM messages WHERE timestamp < NOW() - INTERVAL 1 DAY";
    if (!mysqli_query($conn, $delete_sql)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete old messages']);
        exit;
    }

    // Prepare message fetch query
    $sql = "SELECT m.*, u.username AS sender_name, u.profile_path 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
        OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.timestamp ASC";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $messages = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }

    // Output messages as JSON
    echo json_encode($messages);
?>