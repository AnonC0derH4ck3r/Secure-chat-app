<?php
    // start the session
    session_start();

    // if SESSION with 'user_id' doesn't exists
    if (!isset($_SESSION['user_id'])) {
        // returns 401 (Unauthorized code)
        http_response_code(401);
        // returns appropriate message
        echo json_encode(['error' => 'Unauthorized']);
        // terminates the script
        exit();
    }

    // change the content type to application/json
    header('Content-Type: application/json');

    // generates the key using sender and receiver username
    function generateSharedKey($username1, $username2) {
        $sorted = [$username1, $username2];
        sort($sorted);
        $combined = $sorted[0] . ':' . $sorted[1];
        $hash = hash('sha256', $combined, true); // true = raw binary
        return base64_encode($hash);
    }

    // simple XOR-based encryption
    function simpleEncrypt($text, $key) {
        $result = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $result .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
        }
        return base64_encode($result);
    }

    function getReceiverUsername($id) {
        
        $dbHost = 'localhost';
        $dbUser = 'root';
        $dbPass = '';
        $dbName = 'secure_chat';

        // connects to the mysqli database
        $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
        $query = "SELECT username from users WHERE id = $id";
        $exec = mysqli_query($conn, $query);
        $results = mysqli_fetch_assoc($exec);
        $username = $results['username'];
        mysqli_free_result($exec);
        return $username;
    }

    // the constant __DIR__ holds the current directory of the file /secure_chat/php/
    // concatenates to /../uploads/, so the final will be '/secure_chat/php/../uploads/'
    $uploadDir = __DIR__ . '/../uploads/';
    // checks if directory doesn't exists
    // then creates a directory with 755 (owner has read, write and execute permissions, while the group and other users
    //  has read and execute permissions only.
    // true for recursive directory making.
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // if the $_FILE['file'] is not set or error while uploading a file
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        // echos the error occured while uploading a file
        echo json_encode(['error' => 'File upload failed']);
        // terminates the script
        exit();
    }

    // gets the user_id from the session
    $userId = $_SESSION['user_id'];
    // gets the receiver id by $_POST variable and converting to integer using intval() if set otherwise to 0
    $receiverId = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;

    // gets the files temp path
    $fileTmpPath = $_FILES['file']['tmp_name'];

    // gets the file name
    $fileName = basename($_FILES['file']['name']);
    
    // gets the file extension
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // list of extensions accepted by the server (images and documents)
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx'];

    // checks if the extension doesn't exists in the array list using 'in_array' function
    if (!in_array($fileExt, $allowedExts)) {
        // if it doesn't, show the error unsupported file type
        echo json_encode(['error' => 'Unsupported file type']);
        // terminates the script
        exit();
    }

    // extension for images
    $imageExts = ['jpg', 'jpeg', 'png', 'gif'];
    
    // if the file has these extensions (jpg, jpeg, png, gif), mark it as an 'image', otherwise 'document'
    $fileType = in_array($fileExt, $imageExts) ? 'image' : 'document';

    // creates a unique file name using a current time-based identifier (unique identifier)
    // uniqid accepts a prefix (file_) and it appends the unique identifier to it.
    // adding true adds an more_entrophy to ensure more uniqueness to the filename
    // finally appends the extension from the original image
    $uniqueFileName = uniqid('file_', true) . '.' . $fileExt;

    // destinationpath = /secure_chat/php/../uploads/ + file_6837f5cd427af0.13347996.jpg
    $destPath = $uploadDir . $uniqueFileName;

    // gets the content of the file
    $fileData = file_get_contents($fileTmpPath);
    // if false
    if ($fileData === false) {
        // shows appropriate error that file couldn't be red.
        echo json_encode(['error' => 'Failed to read uploaded file']);
        // terminates the script
        exit();
    }

    file_put_contents($destPath, $fileData);

    // Prepare placeholder message
    $placeholder = "file::{$uniqueFileName}|{$fileType}";
    $sharedKey = generateSharedKey($_SESSION['username'], getReceiverUsername($receiverId));
    $encrypt_msg = simpleEncrypt($placeholder, $sharedKey);

    // DB connection parameters (update as needed)
    $dbHost = 'localhost';
    $dbUser = 'root';
    $dbPass = '';
    $dbName = 'secure_chat';

    // connects to the mysqli database
    $conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
    if (!$conn) {
        // if connection failed, shows the appropriate message
        echo json_encode(['error' => 'Database connection failed']);
        // terminates the script
        exit();
    }

    // prepares the statement with insert query to insert the placeholder into the database
    // safe to prevent SQLi
    $stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, receiver_id, encrypted_msg, type) VALUES (?, ?, ?, 'file')");
    if (!$stmt) {
        // if failes shows appropriate message
        echo json_encode(['error' => 'DB prepare failed']);

        // exits
        exit();
    }

    // binds params with the markers (?)
    // 'iis' integer, integer, string (datatypes)
    mysqli_stmt_bind_param($stmt, "iis", $userId, $receiverId, $encrypt_msg);
    // executes the statement
    mysqli_stmt_execute($stmt);

    // gets the AUTO_INCREMENT last value returned by MySQL
    $messageId = mysqli_insert_id($conn);

    // closes the statement
    mysqli_stmt_close($stmt);

    // close the connection
    mysqli_close($conn);

    // returns appropriate data to the client
    echo json_encode([
        'success' => true,
        'fileName' => $uniqueFileName,
        'fileType' => $fileType,
        'messageId' => $messageId
    ]);
?>