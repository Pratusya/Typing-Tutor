<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('db.php');
require('vendor/autoload.php'); // For PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$message = "";

if (isset($_POST['email'])) {
    $email = stripslashes($_REQUEST['email']);
    $email = mysqli_real_escape_string($con, $email);

    $stmt = $con->prepare("SELECT * FROM `users` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $token = bin2hex(random_bytes(50));
        $stmt = $con->prepare("UPDATE `users` SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        $reset_link = "http://localhost/Typing-Tutor1/reset_password.php?token=" . $token;

        // Send email with reset link
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP host
            $mail->SMTPAuth   = true;
            $mail->Username   = 'harsora2006@gmail.com'; // Replace with your email
            $mail->Password   = 'vwoj onhq nqhu flve'; // Replace with your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            //Recipients
            $mail->setFrom('harsora2006@gmail.com', 'harsora');
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "To reset your password, please click on this link: <a href='$reset_link'>Reset Password</a><br>This link will expire in 1 hour.";

            $mail->send();
            $message = "A password reset link has been sent to your email. Please check your inbox.";
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $message = "No account found with that email address.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style123.css"/>
    <script>
    function showMessage() {
        var message = <?php echo json_encode($message); ?>;
        if (message) {
            alert(message);
        }
    }
    </script>
</head>
<body onload="showMessage()">
    <form class="form" method="post">
        <h1 class="login-title">Forgot Password</h1>
        <input type="email" class="login-input" name="email" placeholder="Email" required autofocus>
        <input type="submit" value="Reset Password" name="submit" class="login-button"/>
        <p class="link"><a href="login.php">Back to Login</a></p>
    </form>
</body>
</html>