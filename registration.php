<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('db.php');
require('vendor/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Razorpay\Api\Api;

session_start();
$errors = array();

// Razorpay credentials
$razorpay_key_id = 'rzp_test_vmssaFysS6ROAD';
$razorpay_key_secret = 'gqhigy08YKnm9y43YNeMjFmF';

// AJAX email check
if (isset($_POST['check_email'])) {
    $email = mysqli_real_escape_string($con, $_POST['check_email']);
    $check_stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    echo json_encode(['exists' => $check_result->num_rows > 0]);
    exit;
}

// AJAX username check
if (isset($_POST['check_username'])) {
    $username = mysqli_real_escape_string($con, $_POST['check_username']);
    $check_stmt = $con->prepare("SELECT * FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    echo json_encode(['exists' => $check_result->num_rows > 0]);
    exit;
}

// Function to generate OTP
function generateOTP() {
    return sprintf("%06d", mt_rand(0, 999999));
}

// Function to send OTP email
function sendOTPEmail($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'harsora2006@gmail.com';
        $mail->Password   = 'vwoj onhq nqhu flve';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('harsora2006@gmail.com', 'Pratik');
        $mail->addAddress($email);

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Registration';
        $mail->Body    = "Your OTP for registration is: <strong>$otp</strong>. This OTP will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// When form submitted, process the registration
if (isset($_POST['username'])) {
    $username = stripslashes($_POST['username']);
    $username = mysqli_real_escape_string($con, $username);
    $email    = stripslashes($_POST['email']);
    $email    = mysqli_real_escape_string($con, $email);
    $phone    = stripslashes($_POST['phone']);
    $phone    = mysqli_real_escape_string($con, $phone);
    $password = stripslashes($_POST['password']);
    $password = mysqli_real_escape_string($con, $password);
    
    // Check if username or email already exists
    $check_stmt = $con->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $existing_user = $check_result->fetch_assoc();
        if ($existing_user['username'] === $username) {
            $errors['username'] = "Username already exists. Please choose a different username.";
        }
        if ($existing_user['email'] === $email) {
            $errors['email'] = "Email already exists. Please use a different email address.";
        }
    }
    
    if (empty($errors)) {
        // Generate OTP
        $otp = generateOTP();
        
        // Store registration data and OTP in session
        $_SESSION['registration'] = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'otp' => $otp,
            'otp_time' => time()
        ];
        
        // Send OTP email
        if (sendOTPEmail($email, $otp)) {
            echo json_encode(['success' => true, 'message' => 'OTP sent to your email. Please verify to complete registration.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send OTP. Please try again.']);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
}

// Verify OTP and initiate payment
if (isset($_POST['otp'])) {
    $entered_otp = $_POST['otp'];
    if (isset($_SESSION['registration'])) {
        $registration = $_SESSION['registration'];
        
        // Check if OTP is expired (10 minutes)
        if (time() - $registration['otp_time'] > 600) {
            echo json_encode(['success' => false, 'message' => 'OTP expired. Please try again.']);
            exit;
        }
        
        if ($entered_otp == $registration['otp']) {
            // OTP is correct, insert user data into the database
            $username = $registration['username'];
            $email = $registration['email'];
            $phone = $registration['phone'];
            $password = password_hash($registration['password'], PASSWORD_DEFAULT);
            $payment_status = 'pending';
            $create_datetime = date("Y-m-d H:i:s");
            $verified = 0; // Set verified status to 0 (unverified)

            $stmt = $con->prepare("INSERT INTO users (username, email, phone, password, payment_status, create_datetime, verified) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $username, $email, $phone, $password, $payment_status, $create_datetime, $verified);
            
            if ($stmt->execute()) {
                // Proceed with payment initiation
                $api = new Api($razorpay_key_id, $razorpay_key_secret);
                $order = $api->order->create(array(
                    'receipt' => 'rcptid_' . time(),
                    'amount' => 2000, // Amount in paise (20 rupees)
                    'currency' => 'INR'
                ));
                // Store order details in session
                $_SESSION['razorpay_order_id'] = $order['id'];
                // Return payment details to the client
                echo json_encode([
                    'success' => true,
                    'message' => 'Proceed to payment',
                    'order_id' => $order['id'],
                    'amount' => 2000,
                    'key' => $razorpay_key_id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to register user. Please try again.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Incorrect OTP. Please try again.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid session. Please start the registration process again.']);
    }
    exit;
}

// Resend OTP
if (isset($_POST['resend_otp'])) {
    if (isset($_SESSION['registration'])) {
        $registration = $_SESSION['registration'];
        $current_time = time();
        
        // Check if 1 minute has passed since the last OTP was sent
        if ($current_time - $registration['otp_time'] > 60) {
            $new_otp = generateOTP();
            $_SESSION['registration']['otp'] = $new_otp;
            $_SESSION['registration']['otp_time'] = $current_time;
            
            if (sendOTPEmail($registration['email'], $new_otp)) {
                echo json_encode(['success' => true, 'message' => 'New OTP sent to your email.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send new OTP. Please try again.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Please wait 1 minute before requesting a new OTP.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid session. Please start the registration process again.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style1.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
<main>
    <form id="registration-form" class="form" action="" method="post">
        <h1>Sign Up</h1>
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            <p id="username-error" class="error" style="display: none;">This username already exists.</p>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
            <p id="email-error" class="error" style="display: none;">This email already exists.</p>
        </div>
        <div>
            <label for="phone">Phone:</label>
            <input type="tel" name="phone" id="phone" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <label for="re-password">Re-enter Password:</label>
            <input type="password" name="re-password" id="re-password" required>
            <p id="password-match-error" class="error" style="display: none;">Passwords do not match.</p>
        </div>
        <div class="checkbox-container">
            <input type="checkbox" name="agree" id="agree" value="yes" required/>
            <label for="agree">
                I agree with the <a href="#" title="term of services">term of services</a>
            </label>
        </div>
        <button type="submit" id="submit-btn">Register</button>
        <footer>Already a member? <a href="login.php">Login here</a></footer>
    </form>

    <form id="otp-form" class="form" action="" method="post" style="display: none;">
        <h1>Verify OTP</h1>
        <div>
            <label for="otp">Enter OTP:</label>
            <input type="text" name="otp" id="otp" required>
        </div>
        <button type="submit" id="verify-btn">Verify OTP</button>
        <button type="button" id="resend-otp-btn" disabled>Resend OTP</button>
        <div id="timer"></div>
    </form>
</main>

<form name='razorpayform' id='razorpay-form' action="verify_payment.php" method="POST">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
</form>

<script>
// ... (JavaScript code remains the same as in the original file)
</script>
</body>
</html>