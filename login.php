<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('db.php');
require('utilities.php');
session_start();

logError("Session data at login start: " . print_r($_SESSION, true), 'login.log');

// Check if there's a registered username in the session
$registered_username = isset($_SESSION['registered_username']) ? $_SESSION['registered_username'] : '';
// Clear the session variable after using it
unset($_SESSION['registered_username']);

// When form submitted, check and create user session.
if (isset($_POST['username'])) {
    $username = stripslashes($_REQUEST['username']);    // removes backslashes
    $username = mysqli_real_escape_string($con, $username);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($con, $password);
    
    logError("Login attempt for user: " . $username, 'login.log');

    // Check user is exist in the database
    $stmt = $con->prepare("SELECT * FROM `users` WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        logError("User data from database: " . print_r($user, true), 'login.log');
        
        if (password_verify($password, $user['password'])) {
            // Check if the user is verified
            if ($user['verified'] == 1) {
                logError("Password verified for user: " . $username, 'login.log');
                $_SESSION['username'] = $username;
                $_SESSION['payment_status'] = $user['payment_status'];
                logError("Login successful for user: " . $username, 'login.log');
                header("Location: index.php");
                exit();
            } else {
                logError("User not verified: " . $username, 'login.log');
                echo "<script>alert('Please verify your email before logging in.');</script>";
            }
        } else {
            logError("Incorrect password for user: " . $username, 'login.log');
            echo "<script>alert('Incorrect Username/password.');</script>";
        }
    } else {
        logError("User not found: " . $username, 'login.log');
        echo "<script>alert('Incorrect Username/password.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style123.css"/>
</head>
<body>
<form class="form" method="post" name="login">
        <h1 class="login-title">Login</h1>
        <input type="text" class="login-input" name="username" placeholder="Username" autofocus="true" value="<?php echo htmlspecialchars($registered_username); ?>"/>
        <input type="password" class="login-input" name="password" placeholder="Password"/>
        <input type="submit" value="Login" name="submit" class="login-button"/>
        <p class="link">Don't have an account? <a href="registration.php">Registration Now</a></p>
        <p class="link"><a href="forgot_password.php">Forgot Password?</a></p>
    </form>
</body>
</html>