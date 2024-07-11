<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('db.php');
session_start();

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $con->prepare("SELECT * FROM `users` WHERE reset_token = ? AND reset_token_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        if (isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
            $new_password = stripslashes($_REQUEST['new_password']);
            $confirm_password = stripslashes($_REQUEST['confirm_password']);

            if ($new_password !== $confirm_password) {
                $error_message = "Passwords do not match. Please try again.";
            } else {
                $new_password = mysqli_real_escape_string($con, $new_password);
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $con->prepare("UPDATE `users` SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE reset_token = ?");
                $stmt->bind_param("ss", $hashed_password, $token);
                $stmt->execute();

                echo "<script>
                    alert('Password has been reset successfully.');
                    window.location.href = 'login.php';
                </script>";
                exit();
            }
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Password</title>
            <link rel="stylesheet" href="style123.css"/>
        </head>
        <body>
            <form class="form" method="post">
                <h1 class="login-title">Reset Password</h1>
                <?php
                if (isset($error_message)) {
                    echo "<p class='error'>$error_message</p>";
                }
                ?>
                <input type="password" class="login-input" name="new_password" id="new_password" placeholder="New Password" required autofocus>
                <input type="password" class="login-input" name="confirm_password" id="confirm_password" placeholder="Confirm New Password" required>
                <p id="password-match-error" class="error" style="display: none;">Passwords do not match.</p>
                <input type="submit" value="Reset Password" name="submit" class="login-button"/>
            </form>

            <script>
            document.getElementById('confirm_password').addEventListener('input', function() {
                var newPassword = document.getElementById('new_password').value;
                var confirmPassword = this.value;
                var errorElement = document.getElementById('password-match-error');
                
                if (newPassword !== confirmPassword) {
                    errorElement.style.display = 'block';
                } else {
                    errorElement.style.display = 'none';
                }
            });
            </script>
        </body>
        </html>
        <?php
    } else {
        echo "Invalid or expired reset token.";
    }
} else {
    echo "No reset token provided.";
}
?>