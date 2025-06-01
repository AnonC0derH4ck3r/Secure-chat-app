<?php
    // set timezone to Asia/Kolkata
    // in all PHP scripts
    // which will include it
    date_default_timezone_set('Asia/Kolkata');
    $host = "localhost";    // server name
    $user = "root";         // database username
    $pass = "";             // database password (empty by default)
    $db   = "secure_chat";  // database name

    // connect to the mysqli database
    $conn = mysqli_connect($host, $user, $pass, $db);

    // if connection fails, stop the script and display the reason for database connection error.
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>