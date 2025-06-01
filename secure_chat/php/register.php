<?php
    session_start();
    header('Content-Type: application/json');

    // DB credentials - update as needed
    require_once '../db.php';

    // Connect to DB
    $conn = mysqli_connect($host, $user, $pass, $db);
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
        exit;
    }

    // Clean input function
    function clean_input($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    // Get and sanitize input
    $username = isset($_POST['username']) ? clean_input($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        mysqli_close($conn);
        exit;
    }

    if (strlen($username) > 50) {
        echo json_encode(['success' => false, 'message' => 'Username too long (max 50 characters).']);
        mysqli_close($conn);
        exit;
    }

    // Check if username exists
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already taken.']);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        exit;
    }
    mysqli_stmt_close($stmt);

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    if (!$password_hash) {
        echo json_encode(['success' => false, 'message' => 'Password hashing failed.']);
        mysqli_close($conn);
        exit;
    }

    // Insert new user
    $sql = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password_hash);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
?>