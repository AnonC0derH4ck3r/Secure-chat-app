<?php
    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        exit();
    }

    if (!isset($_FILES['profile_pic']) || $_FILES['profile_pic']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit();
    }

    // Server config
    $uploadDir = __DIR__ . '/../profile/';  // Fixed path with slashes
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Validate file
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = basename($_FILES['profile_pic']['name']);
    $fileSize = $_FILES['profile_pic']['size'];
    $fileType = mime_content_type($fileTmpPath);

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB max

    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed.']);
        exit();
    }

    if ($fileSize > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size is 2MB.']);
        exit();
    }

    // Generate unique file name to avoid collisions
    $ext = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $newFileName;

    // Move the file
    if (!move_uploaded_file($fileTmpPath, $destination)) {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
        exit();
    }

    // Save relative path for DB (relative to your public folder)
    $relativePath = 'profile/' . $newFileName;

    require_once '../db.php';

    if (!$conn) {
        // Delete uploaded file if DB connection fails
        unlink($destination);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }

    // Prepare and execute update query using procedural MySQLi
    $sql = "UPDATE users SET profile_path = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        unlink($destination);
        echo json_encode(['success' => false, 'message' => 'Database statement preparation failed']);
        exit();
    }

    $userId = $_SESSION['user_id'];
    mysqli_stmt_bind_param($stmt, 'si', $relativePath, $userId);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully', 'profile_path' => $relativePath]);
    } else {
        // Delete uploaded file if DB update fails
        unlink($destination);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile picture in database']);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
?>