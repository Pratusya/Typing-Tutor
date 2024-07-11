<?php
session_start();
require('db.php');
require('vendor/autoload.php');
require('utilities.php');
use Razorpay\Api\Api;

logError("Session data at payment start: " . print_r($_SESSION, true), 'payment.log');

// Check if user is logged in and payment is not yet made
if (!isset($_SESSION['username']) || (isset($_SESSION['payment_status']) && $_SESSION['payment_status'] === 'paid')) {
    logError("User not logged in or already paid. Redirecting to login.", 'payment.log');
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
$stmt = $con->prepare("SELECT payment_status FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user['payment_status'] === 'paid') {
        header("Location: index.php");
        exit();
    }
} else {
    // Handle case where user is not found in database
    echo "User not found. Please contact support.";
    exit();
}

$razorpay_key_id = 'rzp_test_sC9wQzWpja3MGt';
$razorpay_key_secret = 'h6DAMnUJo2QrP9Xl4lmzBDIG';

$api = new Api($razorpay_key_id, $razorpay_key_secret);
$order = $api->order->create(array(
    'receipt' => 'rcptid_' . time(),
    'amount' => 2000, // Amount in paise (20 rupees)
    'currency' => 'INR'
));

logError("Razorpay order created: " . print_r($order, true), 'payment.log');

$_SESSION['razorpay_order_id'] = $order['id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <h1>Complete Your Payment</h1>
    <p>To access the typing tutor, please complete your payment of 20 rupees.</p>
    <button id="pay-button">Pay Now</button>

    <form name='razorpayform' id='razorpay-form' action="verify_payment.php" method="POST">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
    </form>

    <script>
    var options = {
        "key": "<?php echo $razorpay_key_id; ?>",
        "amount": "<?php echo $order['amount']; ?>",
        "currency": "INR",
        "name": "Your Company Name",
        "description": "Registration Fee",
        "image": "your_logo_url",
        "order_id": "<?php echo $order['id']; ?>",
        "handler": function (response){
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
            document.getElementById('razorpay_signature').value = response.razorpay_signature;
            document.getElementById('razorpay-form').submit();
        },
        "prefill": {
            "name": "<?php echo $username; ?>",
            "email": "<?php echo isset($email) ? $email : ''; ?>",
            "contact": "<?php echo isset($phone) ? $phone : ''; ?>"
        },
        "theme": {
            "color": "#3399cc"
        }
    };
    var rzp1 = new Razorpay(options);
    document.getElementById('pay-button').onclick = function(e){
        rzp1.open();
        e.preventDefault();
    }
    </script>
</body>
</html>