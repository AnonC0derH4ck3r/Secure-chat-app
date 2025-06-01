<?php
session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['username'])
) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Chat</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logout-btn {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: #e63939;
        }
    </style>
</head>

<body>
    <div class="top-bar">
        <h2>Secure Chat</h2>
        <div>
            <a href="manage-account.php" class="manage-account-btn" title="Manage Account"
                style="margin-right:10px; color:#ffffff; text-decoration:none; font-weight:bold;">
                Manage Account
            </a>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </div>

    <div class="container">
        <div id="usersList" class="users-list">
            <h3>Users</h3>
            <ul id="usersUl"></ul>
        </div>

        <div class="chat-section">
            <div id="welcomeMessage" class="welcome-message"></div>
            <div id="chatBox"
                style="height:300px; overflow:auto; background:#121212; padding:10px; border-radius:5px; margin-bottom:10px;">
            </div>

            <form id="chatForm" class="input-area">
                <input type="text" id="message" placeholder="Type a message..." autocomplete="off" required />
        
                <!-- File upload button -->
                <button type="button" id="uploadBtn" title="Upload File" style="background:#18A8A9; border:none; margin-right:6px;">
                    <i class="fas fa-paperclip" style="color:white; font-size:20px;"></i>
                </button>
                
                <!-- Send button -->
                <button type="submit" title="Send" style="background:#18A8A9; border:none;">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="white" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
                    </svg>
                </button>
            </form>

        <!-- Hidden file input outside the form -->
        <form id="uploadForm" enctype="multipart/form-data" style="display: none;">
            <input type="file" id="fileInput" name="file" />
        </form>

        </div>
    </div>

    <script>
        function logout() {
            sessionStorage.clear(); // Clear frontend session data
            window.location.href = 'php/logout.php'; // Redirect to PHP logout
        }
    </script>
    <script src="js/chat.js"></script>
</body>

</html>