<?php
session_start();
if(isset($_SESSION['user_id']) && isset($_SESSION['username'])){
    header('Location: chat.php');
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Create Account - Secure Chat</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="auth-container">
        <h2>Create Your Account</h2>
        <form id="signupForm">
            <input type="text" name="username" id="username" placeholder="Choose a username" required>
            <input type="password" name="password" id="password" placeholder="Create a password" required>
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password"
                required>

            <button type="submit"><i class="fa-solid fa-user-plus"></i> Sign Up</button>
        </form>

        <p style="margin-top: 15px;">Already have an account? <a href="index.php">Login here</a></p>
    </div>

    <script>
        document.getElementById('signupForm').onsubmit = async function (e) {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            const confirmPassword = document.getElementById('confirm_password').value.trim();

            if (password !== confirmPassword) {
                Swal.fire('Error', 'Passwords do not match!', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);

            try {
                const res = await fetch('php/register.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await res.json();

                if (result.success) {
                    Swal.fire('Success', 'Account created successfully! You can now log in.', 'success')
                        .then(() => {
                            window.location.href = 'index.php';
                        });
                } else {
                    Swal.fire('Error', result.message || 'Registration failed.', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
            }
        };
    </script>
</body>

</html>