<?php
// starts new or resume existing session
session_start();

// checks if the $_SESSION global variable has 'user_id' and 'username' set
// In simple words, it checks if the user has already logged in or not
if(isset($_SESSION['user_id']) && isset($_SESSION['username'])){
    // if so, then send a RAW HTTP Header (Location)
    // which is used for Temporary or Permanent Redirects (301 or 302)
    // In this case, we're redirecting users to chat.php page
    header('Location: chat.php');
}
?>
<!-- Otherwise, the user is presented with a login page -->
<!DOCTYPE html>
<html>
<head>
    <title>Secure Chat</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="auth-container">
    <h2>Login to Secure Chat</h2>
    <!-- Inside your form -->
    <form id="loginForm">
        <input type="text" name="username" id="username" placeholder="Enter your username" required>
        <input type="password" name="password" id="password" placeholder="Enter your password" required>

        <button type="submit"><i class="fa-solid fa-lock"></i> Login</button>

        <!-- Create Account button -->
        <button type="button" onclick="window.location.href='signup.php'" style="margin: 10px 0; background-color: #007bff; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer;">
            <i class="fa-solid fa-user-plus"></i> Create an Account
        </button>
    </form>

    <!-- Hidden until passkey required -->
    <p id="passkeyLoginParagraph" style="margin-top:10px; display:none;">For your security, we need to verify your identity using a passkey.</p>
    <button id="passkeyLoginBtn" style="display:none; margin-top:10px;">
        <i class="fas fa-fingerprint"></i> Verify with Passkey
    </button>

</div>

<script>
    // It targets the login form and adds an Event Listener (onclick) to it
    document.getElementById('loginForm').onsubmit = async function (e) {
        // once the form is submitted, it prevents the form from submitting
        e.preventDefault();

        // gets the username and removes whitespace from left or right
        const username = document.getElementById('username').value.trim();

        // gets the password and removes whitespace from left or right
        const password = document.getElementById('password').value.trim();

        // creates a formData object, which will hold the data which will be sent to the server
        const formData = new FormData();

        // appends username
        formData.append("username", username);

        // appends password
        formData.append("password", password);

        // make a POST request to 'php/auth.php'
        const res = await fetch('php/auth.php', {
            method: 'POST',
            // send the formData Object in the request body
            body: formData
        });

        // catches the response received by the server in json format
        const result = await res.json();

        // if result.success is a falsy value (undefined, null, false)
        if (!result.success) {
            // shows an custom alert box with an error
            Swal.fire('Login Failed', result.message || 'Invalid credentials.', 'error');
            
            // stops the script
            return;
        }

        // if user has Passkey setup to their account
        if (result.requirePasskey) {

            // show the user a message and a button to verify their identity using passkeys
            document.getElementById('loginForm').style.display = 'none';
            documment.getElementById('passkeyLoginParagraph').style.display = 'inline-block';
            document.getElementById('passkeyLoginBtn').style.display = 'inline-block';
            // sets the username to sessionStorage
            // Why use sessionStorage instead of localStorage? Because, by default PHP's sessions gets destroyed when the tab or browser is closed.
            // Hence, if we store the value in localStorage, the username will still be there even if the user closes the browser and reopens it.
            // But, in case of sessionStorage, the data is deleted automatically as soon as user closes the browser.
            sessionStorage.setItem('username', username);
        } else {
            // if the user doesn't has a passkey setup with their account
            // still set the username to sessionStorage
            sessionStorage.setItem('username', username);
            
            // redirect to chat.php
            window.location.href = 'chat.php';
        }
    };

    // targets the verify passkey button and listens for click event
    document.getElementById('passkeyLoginBtn').onclick = function () {
        // captures the username from sessionStorage
        const username = sessionStorage.getItem('username');
        
        // passes the username to 'loginwithPasskey' function
        loginWithPasskey(username);
    };
</script>
<!-- Contains the logic for login -->
<script src="js/login.js"></script>
</body>
</html>