<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('db.php');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style12.css">
    <title>Register</title>
</head>
<body>
<?php
// When form submitted, insert values into the database.
if (isset($_REQUEST['username'])) {
    // removes backslashes
    $username = stripslashes($_REQUEST['username']);
    //escapes special characters in a string
    $username = mysqli_real_escape_string($con, $username);
    $email    = stripslashes($_REQUEST['email']);
    $email    = mysqli_real_escape_string($con, $email);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($con, $password);
    $create_datetime = date("Y-m-d H:i:s");
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Prepare and bind
    $stmt = $con->prepare("INSERT INTO users (username, password, email, create_datetime) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashed_password, $email, $create_datetime);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "<div class='form'>
              <center><h3>You are registered successfully.</h3><br/>
              <p>Redirecting to login page...</p></center>
              </div>";
        echo "<script>
              setTimeout(function() {
                  window.location.href = 'login.php';
              }, 3000);
              </script>";
    } else {
        echo "<div class='form'>
              <h3>Registration failed.</h3><br/>
              <p>Error: " . $stmt->error . "</p>
              <p class='link'>Click here to <a href='registration.php'>try again</a>.</p>
              </div>";
    }
    $stmt->close();
} else {
?>
<main>
    <form class="form" action="registration.php" method="post">
        <h1>Sign Up</h1>
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <div>
            <label for="agree">
                <input type="checkbox" name="agree" id="agree" value="yes" required/> I agree
                with the
                <a href="#" title="term of services">term of services</a>
            </label>
        </div>
        <button type="submit">Register</button>
        <footer>Already a member? <a href="login.php">Login here</a></footer>
    </form>
</main>
<?php
}
?>
</body>
</html>