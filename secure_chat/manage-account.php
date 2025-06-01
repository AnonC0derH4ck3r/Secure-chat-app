<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
if(isset($_GET['profile']) && intval($_GET['profile']) === 1){
    require_once 'db.php';
    if($conn) {
        $username = $_SESSION['username'];
        $query = "SELECT profile_path FROM users where username = '$username'";
        $exec = mysqli_query($conn, $query);
        if(mysqli_num_rows($exec) > 0) {
            $results = mysqli_fetch_assoc($exec);
            echo json_encode([
                'success' => true,
                'user' => [ 'username' => $username, 'profile_pic' => $results['profile_path'] ]
            ]);
        }
    }
    mysqli_close($conn);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Account</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1E1E2F;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container {
            background: #1E1E2F;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(255, 255, 255, 0.1);
            padding: 40px 35px;
            max-width: 500px;
            width: 100%;
        }

        h2 {
            text-align: center;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }

        .profile-pic {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-pic img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 12px;
            border: 3px solid #4a90e2;
            transition: transform 0.3s ease;
        }

        .profile-pic img:hover {
            transform: scale(1.05);
            cursor: pointer;
        }

        input[type="file"] {
            border: none;
            font-size: 14px;
            color: #555;
            cursor: pointer;
        }

        label {
            font-weight: 600;
            color: #fff7f7;
            margin-bottom: 8px;
            display: block;
            font-size: 14px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            font-size: 15px;
            border-radius: 8px;
            border: 1.8px solid #ddd;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
            margin-bottom: 25px;
        }

        input[type=text]:disabled {
            background-color: transparent;
            color: #55554F;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 6px rgba(74, 144, 226, 0.4);
        }

        .passkey-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 14px 0;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 35px;
            width: 100%;
            box-shadow: 0 4px 10px rgba(74, 144, 226, 0.4);
            transition: background-color 0.3s ease;
        }

        .passkey-btn i {
            margin-right: 10px;
            font-size: 18px;
        }

        .passkey-btn:hover {
            background-color: #3a78d0;
        }

        .save-btn {
            width: 100%;
            background-color: #28a745;
            border: none;
            padding: 14px;
            color: white;
            font-size: 17px;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.4);
            transition: background-color 0.3s ease;
        }

        .save-btn:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <div style="display:flex; justify-content:flex-start; margin-bottom:20px;">
        <a href="chat.php" style="
          position: fixed;
          top: 1rem;
          left:2rem;
          background-color: #4a90e2; 
          color: white; 
          padding: 8px 16px; 
          text-decoration: none; 
          border-radius: 5px; 
          font-weight: 600;
          box-shadow: 0 3px 6px rgba(74,144,226,0.4);
          transition: background-color 0.3s ease;
        " onmouseover="this.style.backgroundColor='#357ABD'" onmouseout="this.style.backgroundColor='#4a90e2'">
            ← Back to Chat
        </a>
    </div>
    <div class="container">
        <h2>Manage Account</h2>

        <form id="manageAccountForm" enctype="multipart/form-data">
            <div class="profile-pic">
                <img id="profileImage" alt="Profile Picture" title="Click to change profile picture" />
                <label for="profilePicUpload">Change Profile Picture</label>
                <input type="file" id="profilePicUpload" name="profile_pic" accept="image/*" />
            </div>

            <label for="username">Username (username can't be changed)</label>
            <input type="text" id="username" name="username" value=<?php echo '"' . $_SESSION['username'] . '"'; ?> placeholder="Enter new username" required disabled />

            <div id="passkeyStatus" style="margin-top: 10px;"></div>
            <button type="button" class="passkey-btn" id="addPasskeyBtn" title="Add Passkey">
                <i class="fa-solid fa-key"></i> Add Passkey
            </button>
        </form>
    </div>
    <script src="js/manage.js"></script>
</body>

</html>