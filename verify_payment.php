<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require('db.php');
require('vendor/autoload.php');
require('utilities.php');
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

logError("Payment verification process started", 'payment_verification.log');
logError("POST data: " . print_r($_POST, true), 'payment_verification.log');
logError("SESSION data: " . print_r($_SESSION, true), 'payment_verification.log');

if (!isset($_SESSION['username']) || !isset($_POST['razorpay_payment_id']) || !isset($_POST['razorpay_order_id']) || !isset($_POST['razorpay_signature'])) {
    logError("Missing required session or POST data", 'payment_verification.log');
    $_SESSION['payment_error'] = "Missing required data for payment verification";
    header("Location: payment.php");
    exit();
}

$razorpay_key_id = 'rzp_test_vmssaFysS6ROAD';
$razorpay_key_secret = 'gqhigy08YKnm9y43YNeMjFmF';

$api = new Api($razorpay_key_id, $razorpay_key_secret);

// New function to update payment status
function updatePaymentStatus($con, $username, $status) {
    $stmt = $con->prepare("UPDATE users SET payment_status = ? WHERE username = ?");
    $stmt->bind_param("ss", $status, $username);
    if ($stmt->execute()) {
        logError("Payment status updated to '$status' for user: $username", 'payment_verification.log');
        return true;
    } else {
        logError("Failed to update payment status: " . $stmt->error, 'payment_verification.log');
        return false;
    }
}

try {
    $attributes = array(
        'razorpay_order_id' => $_POST['razorpay_order_id'],
        'razorpay_payment_id' => $_POST['razorpay_payment_id'],
        'razorpay_signature' => $_POST['razorpay_signature']
    );
    
    $api->utility->verifyPaymentSignature($attributes);
    logError("Payment signature verified successfully", 'payment_verification.log');
    
    $payment = $api->payment->fetch($_POST['razorpay_payment_id']);
    logError("Payment status: " . $payment['status'], 'payment_verification.log');
    
    $username = $_SESSION['username'];
    
    if ($payment['status'] == 'captured') {
        if (updatePaymentStatus($con, $username, 'paid')) {
            $_SESSION['payment_status'] = 'paid';
            $_SESSION['payment_success'] = "Payment successful! You can now access the typing tutor.";
            unset($_SESSION['pending_payment_verification']);
            logError("Session data after payment verification: " . print_r($_SESSION, true), 'payment_verification.log');
            header("Location: index.php");
            exit();
        } else {
            throw new Exception("Failed to update payment status in the database.");
        }
    } else {
        if (updatePaymentStatus($con, $username, 'failed')) {
            $_SESSION['payment_status'] = 'failed';
            $_SESSION['payment_error'] = "Payment failed. Please try again.";
            logError("Payment failed. Status: " . $payment['status'], 'payment_verification.log');
            header("Location: payment.php");
            exit();
        } else {
            throw new Exception("Failed to update payment status in the database.");
        }
    }
} catch(SignatureVerificationError $e) {
    logError("Razorpay Signature Verification Error: " . $e->getMessage(), 'payment_verification.log');
    $_SESSION['payment_error'] = "Payment verification failed. Please try again or contact support.";
    updatePaymentStatus($con, $_SESSION['username'], 'failed');
} catch(Exception $e) {
    logError("Error: " . $e->getMessage(), 'payment_verification.log');
    $_SESSION['payment_error'] = "An error occurred during payment processing. Please try again or contact support.";
    updatePaymentStatus($con, $_SESSION['username'], 'failed');
}

header("Location: payment.php");
exit();
?>