<?php
// Using database connection file and its variable
require_once '../db.php';

// starts the session
session_start();

// Tells the client the response is in json format
header('Content-Type: application/json');

// Make sure the user is logged in
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Forbidden. Not logged in."]);
    exit;
}

// gets the username via SESSION variable 'username'
$username = $_SESSION['username'];

// Fetch passkey for the user
$sql = "SELECT id FROM passkeys WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// returns the assosicative array of strings representing the fetched row.
if ($row = mysqli_fetch_assoc($result)) {

    // shows the appropriate message that passkey is linked to the account.
    echo json_encode([
        "success" => true,
        "hasPasskey" => true,
        "message" => "Passkey is linked to your account."
    ]);

    // If it returns NULL
} else {

    // Shows the passkey is not found for users account.
    echo json_encode([
        "success" => true,
        "hasPasskey" => false,
        "message" => "No passkey found for your account."
    ]);
}

// closes the prepared statement.
mysqli_stmt_close($stmt);

// closes the connection.
mysqli_close($conn);
?>