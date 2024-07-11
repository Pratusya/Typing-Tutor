<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('db.php');
require('utilities.php');
session_start();

logError("Checking payment status. Session data: " . print_r($_SESSION, true), 'payment_check.log');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$stmt = $con->prepare("SELECT payment_status FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

logError("User payment status from database: " . print_r($user, true), 'payment_check.log');

if ($user['payment_status'] === 'paid') {
    $_SESSION['payment_status'] = 'paid';
    unset($_SESSION['pending_payment_verification']);
    logError("Payment status verified and session updated for user: " . $username, 'payment_check.log');
    header("Location: index.php");
    exit();
} else {
    logError("Payment not verified for user: " . $username, 'payment_check.log');
    $_SESSION['payment_error'] = "Your payment has not been verified. Please complete the payment process.";
    header("Location: payment.php");
    exit();
}
?>