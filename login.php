<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('db.php');
require('utilities.php');
session_start();

logError("Session data at login start: " . print_r($_SESSION, true), 'login.log');

$registered_username = isset($_SESSION['registered_username']) ? $_SESSION['registered_username'] : '';
unset($_SESSION['registered_username']);

$registration_closed = isset($_SESSION['registration_closed']) && $_SESSION['registration_closed'];

$login_error = null; // Initialize login error variable

if (isset($_POST['username'])) {
    $username = stripslashes($_REQUEST['username']);
    $username = mysqli_real_escape_string($con, $username);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($con, $password);
    
    logError("Login attempt for user: " . $username, 'login.log');

    $stmt = $con->prepare("SELECT * FROM `users` WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        logError("User data from database: " . print_r($user, true), 'login.log');
        
        if (password_verify($password, $user['password'])) {
            logError("Password verified for user: " . $username, 'login.log');
            if ($user['verified'] == 1) {
                $_SESSION['username'] = $username;
                $_SESSION['payment_status'] = $user['payment_status'];
                logError("Login successful for user: " . $username, 'login.log');
                header("Location: index.php");
                exit();
            } else {
                logError("Email not verified for user: " . $username, 'login.log');
                $login_error = "Your email is not verified. Please check your email for the verification OTP.";
            }
        } else {
            logError("Incorrect password for user: " . $username, 'login.log');
            $login_error = "Incorrect Username/password.";
        }
    } else {
        logError("User not found: " . $username, 'login.log');
        $login_error = "Incorrect Username/password.";
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
    <script>
    function checkRegistration() {
        <?php if ($registration_closed): ?>
        alert("Registration is closed.");
        return false;
        <?php endif; ?>
        return true;
    }

    <?php if ($login_error): ?>
    window.onload = function() {
        alert("<?php echo addslashes($login_error); ?>");
    }
    <?php endif; ?>
    </script>
</head>
<body>
    <form class="form" method="post" name="login">
        <h1 class="login-title">Login</h1>
        <input type="text" class="login-input" name="username" placeholder="Username" autofocus="true" value="<?php echo htmlspecialchars($registered_username); ?>"/>
        <input type="password" class="login-input" name="password" placeholder="Password"/>
        <input type="submit" value="Login" name="submit" class="login-button"/>
        <p class="link">Don't have an account? <a href="registration.php" onclick="return checkRegistration();">Registration Now</a></p>
        <p class="link"><a href="forgot_password.php">Forgot Password?</a></p>
    </form>
</body>
</html>