<?php
require('db.php');
session_start();

if (!isset($_SESSION['temp_username']) || !isset($_SESSION['temp_email'])) {
    header("Location: registration.php");
    exit();
}

$username = $_SESSION['temp_username'];
$email = $_SESSION['temp_email'];

if (isset($_POST['verify'])) {
    $entered_otp = $_POST['otp'];
    
    $stmt = $con->prepare("SELECT otp FROM users WHERE username = ? AND email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($entered_otp === $user['otp']) {
            // OTP is correct, update user as verified
            $update_stmt = $con->prepare("UPDATE users SET is_verified = 1, otp = NULL WHERE username = ?");
            $update_stmt->bind_param("s", $username);
            $update_stmt->execute();
            
            // Clear session variables
            unset($_SESSION['temp_username']);
            unset($_SESSION['temp_email']);
            
            // Set registered username for login page
            $_SESSION['registered_username'] = $username;
            
            // Redirect to login page
            echo "<script>
                  alert('Email verified successfully. You can now log in.');
                  window.location.href = 'login.php';
                  </script>";
            exit();
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    } else {
        $error = "User not found. Please register again.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Email Verification</title>
    <link rel="stylesheet" href="style123.css"/>
</head>
<body>
    <form class="form" method="post">
        <h1 class="login-title">Email Verification</h1>
        <?php
        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }
        ?>
        <input type="text" class="login-input" name="otp" placeholder="Enter OTP" required />
        <input type="submit" name="verify" value="Verify" class="login-button">
    </form>
</body>
</html>