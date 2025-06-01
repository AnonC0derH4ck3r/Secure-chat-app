<?php
    // starts the session
    session_start();

    // sets default time zone to Asia/Kolkata
    date_default_timezone_set('Asia/Kolkata');
    error_reporting(0); // use 0 instead of NULL to disable error reporting
    
    // database connection file
    require_once '../db.php';

    // gets credentials from $_POST if not falsy value, otherwise set to ''
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // empty array for response
    $response = [];

    // if username or password is empty
    if (empty($username) || empty($password)) {

        // response.success = false
        $response['success'] = false;

        // response.message = 'Username and password required'
        $response['message'] = "Username and password required";

        // terminates and shows the error
        exit(json_encode($response));
    }

    // Brute force protection parameters
    $max_attempts = 5;  // max attempts
    $lockout_minutes = 15;  // cooldown time (in mins)

    // Check login attempts
    $check_attempts_sql = "SELECT attempts, last_attempt FROM login_attempts WHERE username = ?";

    // prepares the statement
    $check_stmt = mysqli_prepare($conn, $check_attempts_sql);

    // if error
    if (!$check_stmt) {

        // response.success = false
        $response['success'] = false;

        // response.message = 'Database error'
        $response['message'] = "Database error";

        // terminates and shows the error
        exit(json_encode($response));
    }

    // binds the variable to markers (?) with 's' as string datatype
    mysqli_stmt_bind_param($check_stmt, "s", $username);

    // executes the statement
    mysqli_stmt_execute($check_stmt);

    // store the results returned by $check_stmt
    mysqli_stmt_store_result($check_stmt);

    // binds results to variable $attempts and $last_attempt
    mysqli_stmt_bind_result($check_stmt, $attempts, $last_attempt);

    // fetches the results
    mysqli_stmt_fetch($check_stmt);

    // gets the current time (Asia/Kolkata)
    $current_time = time();

    // lockout_time = last_attempt_time + lockout_minutes * 60
    $lockout_time = strtotime($last_attempt) + ($lockout_minutes * 60);

    // checks if attempts is greater than max attempts (5)
    // and if the user is trying to login within 15 minutes (cool down time)
    if ($attempts >= $max_attempts && $current_time < $lockout_time) {

        // response.success = false
        $response['success'] = false;

        // response.message = Too many failed attempts. Try again in {$lockout_minutes} minutes.
        $response['message'] = "Too many failed attempts. Try again in {$lockout_minutes} minutes.";

        // closes the stmt
        mysqli_stmt_close($check_stmt);

        // exits and prints the message
        exit(json_encode($response));
    } elseif ($attempts >= $max_attempts) {
        // Reset attempts after lockout time passed
        $reset_sql = "UPDATE login_attempts SET attempts = 0 WHERE username = ?";

        // prepares the statement
        $reset_stmt = mysqli_prepare($conn, $reset_sql);

        // if successfully prepared 
        if ($reset_stmt) {

            // binds the username to SQL query
            mysqli_stmt_bind_param($reset_stmt, "s", $username);

            // executes
            mysqli_stmt_execute($reset_stmt);

            // close the statement
            mysqli_stmt_close($reset_stmt);
        }
    }

    // close the check_stmt
    mysqli_stmt_close($check_stmt);

    // Fetch user credentials
    $sql = "SELECT id, password_hash FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $response['success'] = false;
        $response['message'] = "Database error";
        exit(json_encode($response));
    }
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_bind_result($stmt, $user_id, $hashed_password);
        mysqli_stmt_fetch($stmt);

        if (password_verify($password, $hashed_password)) {

            // Reset login attempts on successful login
            $reset_sql = "DELETE FROM login_attempts WHERE username = ?";
            $reset_stmt = mysqli_prepare($conn, $reset_sql);
            if ($reset_stmt) {
                mysqli_stmt_bind_param($reset_stmt, "s", $username);
                mysqli_stmt_execute($reset_stmt);
                mysqli_stmt_close($reset_stmt);
            }

            // Check if user has a passkey
            $check_passkey_sql = "SELECT id FROM passkeys WHERE user_id = ? LIMIT 1";
            $passkey_stmt = mysqli_prepare($conn, $check_passkey_sql);
            if ($passkey_stmt) {
                mysqli_stmt_bind_param($passkey_stmt, "i", $user_id);
                mysqli_stmt_execute($passkey_stmt);
                mysqli_stmt_store_result($passkey_stmt);

                $hasPasskey = mysqli_stmt_num_rows($passkey_stmt) > 0;
                mysqli_stmt_close($passkey_stmt);
            } else {
                $hasPasskey = false; // default fallback
            }

            // if the user doesn't has a passkey
            // only then create a session
            // otherwise the session won't be created
            // and the user will be asked to verify using passkeys
            // 'login-verify.php' will create a valid session for the user on successfull verification via passkeys
            if(!$hasPasskey){
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
            }

            $response['success'] = true;
            $response['message'] = 'Login successful.';
            $response['requirePasskey'] = $hasPasskey;
        } else {
            // Wrong password, increase login attempts
            $response['success'] = false;
            $response['message'] = "Wrong password";

            $upsert_sql = "INSERT INTO login_attempts (username, attempts, last_attempt)
                        VALUES (?, 1, NOW())
                        ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()";
            $upsert_stmt = mysqli_prepare($conn, $upsert_sql);
            if ($upsert_stmt) {
                mysqli_stmt_bind_param($upsert_stmt, "s", $username);
                mysqli_stmt_execute($upsert_stmt);
                mysqli_stmt_close($upsert_stmt);
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        // User not found
        $response['success'] = false;
        $response['message'] = "User not found";
        mysqli_stmt_close($stmt);
    }

    exit(json_encode($response));
?>