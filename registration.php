<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require('db.php');

$errors = array();

// AJAX email check
if (isset($_POST['check_email'])) {
    $email = mysqli_real_escape_string($con, $_POST['check_email']);
    $check_stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
    exit;
}

// AJAX username check (NEW)
if (isset($_POST['check_username'])) {
    $username = mysqli_real_escape_string($con, $_POST['check_username']);
    $check_stmt = $con->prepare("SELECT * FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
    exit;
}

// When form submitted, insert values into the database.
if (isset($_POST['username'])) {
    $username = stripslashes($_POST['username']);
    $username = mysqli_real_escape_string($con, $username);
    $email    = stripslashes($_POST['email']);
    $email    = mysqli_real_escape_string($con, $email);
    $password = stripslashes($_POST['password']);
    $password = mysqli_real_escape_string($con, $password);
    $create_datetime = date("Y-m-d H:i:s");
    
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
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare and bind
        $stmt = $con->prepare("INSERT INTO users (username, password, email, create_datetime) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $hashed_password, $email, $create_datetime);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Start the session if not already started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            // Set a session variable with the username
            $_SESSION['registered_username'] = $username;
            
            // Use JavaScript to show an alert and then redirect
            echo "<script>
                  alert('Your registration is successful. You will now be redirected to the login page.');
                  window.location.href = 'login.php';
                  </script>";
            exit();
        } else {
            $errors['general'] = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
    $check_stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style12.css">
    <title>Register</title>
    <style>
        .error {
            color: red;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<main>
    <form class="form" action="" method="post">
        <h1>Sign Up</h1>
        <?php if(isset($errors['general'])): ?>
            <p class="error"><?php echo $errors['general']; ?></p>
        <?php endif; ?>
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            <p id="username-error" class="error" style="display: none;">This username already exists.</p>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            <p id="email-error" class="error" style="display: none;">This email already exists.</p>
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
        <button type="submit" id="submit-btn">Register</button>
        <footer>Already a member? <a href="login.php">Login here</a></footer>
    </form>
</main>
<script>
document.querySelector('.form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent the form from submitting immediately

    // Get form data
    var formData = new FormData(this);

    // Send form data using fetch
    fetch('registration.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes("Your registration is successful")) {
            // If registration was successful, show alert and redirect
            alert('Your registration is successful. You will now be redirected to the login page.');
            window.location.href = 'login.php';
        } else {
            // If there was an error, update the page content
            document.body.innerHTML = data;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

document.getElementById('email').addEventListener('blur', function() {
    var email = this.value;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'registration.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.exists) {
                document.getElementById('email-error').style.display = 'block';
                document.getElementById('submit-btn').disabled = true;
            } else {
                document.getElementById('email-error').style.display = 'none';
                if (document.getElementById('username-error').style.display === 'none') {
                    document.getElementById('submit-btn').disabled = false;
                }
            }
        }
    };
    xhr.send('check_email=' + email);
});

document.getElementById('username').addEventListener('blur', function() {
    var username = this.value;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'registration.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status == 200) {
            var response = JSON.parse(this.responseText);
            if (response.exists) {
                document.getElementById('username-error').style.display = 'block';
                document.getElementById('submit-btn').disabled = true;
            } else {
                document.getElementById('username-error').style.display = 'none';
                if (document.getElementById('email-error').style.display === 'none') {
                    document.getElementById('submit-btn').disabled = false;
                }
            }
        }
    };
    xhr.send('check_username=' + username);
});
</script>
</body>
</html>