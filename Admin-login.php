<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'db.php';
require_once 'utilities.php';

function debug_log($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'admin_login_debug.log');
}

debug_log("Admin login page loaded");

// Always create/update admin user
$admin_username = 'admin';
$admin_password = password_hash('123', PASSWORD_DEFAULT); // Change this password!
$admin_email = 'admin@example.com';

$insert_query = "INSERT INTO admins (username, password, email) 
                 VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE password = VALUES(password), email = VALUES(email)";
$insert_stmt = mysqli_prepare($con, $insert_query);
if ($insert_stmt) {
    mysqli_stmt_bind_param($insert_stmt, "sss", $admin_username, $admin_password, $admin_email);
    if (mysqli_stmt_execute($insert_stmt)) {
        debug_log("Admin user created or updated successfully");
    } else {
        debug_log("Error creating/updating admin user: " . mysqli_stmt_error($insert_stmt));
    }
    mysqli_stmt_close($insert_stmt);
} else {
    debug_log("Failed to prepare admin user insert/update statement");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    debug_log("Login attempt - Username: " . $username);

    $query = "SELECT id, username, password FROM admins WHERE username = ?";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                debug_log("Admin found: " . print_r($row, true));
                if (password_verify($password, $row['password'])) {
                    debug_log("Password verified");
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_username'] = $row['username'];
                    debug_log("Admin login successful. Redirecting to dashboard.");
                    record_admin_login($con, $row['id']);
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    debug_log("Password verification failed");
                    $error = "Invalid username or password";
                }
            } else {
                debug_log("Admin not found");
                $error = "Invalid username or password";
            }
        } else {
            debug_log("Query execution failed: " . mysqli_stmt_error($stmt));
            $error = "Database error. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    } else {
        debug_log("Statement preparation failed: " . mysqli_error($con));
        $error = "Database error. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Admin Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>