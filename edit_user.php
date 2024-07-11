<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';
require_once 'utilities.php';



$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id === 0) {
    die("Invalid user ID");
}

// Fetch user data
$query = "SELECT id, username, email, registration_date, last_login, user_role FROM users WHERE id = ?";
$stmt = mysqli_prepare($con, $query);

if ($stmt === false) {
    die("Error preparing statement: " . mysqli_error($con));
}

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result === false) {
    die("Error fetching user data: " . mysqli_error($con));
}

$user = mysqli_fetch_assoc($result);

if ($user === null) {
    die("User not found");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $user_role = trim($_POST['user_role']);

    // Validate input
    if (empty($username) || empty($email) || empty($user_role)) {
        $error = "All fields are required";
    } else {
        // Update user information
        $update_query = "UPDATE users SET username = ?, email = ?, user_role = ? WHERE id = ?";
        $update_stmt = mysqli_prepare($con, $update_query);

        if ($update_stmt === false) {
            die("Error preparing update statement: " . mysqli_error($con));
        }

        mysqli_stmt_bind_param($update_stmt, "sssi", $username, $email, $user_role, $user_id);
        $result = mysqli_stmt_execute($update_stmt);

        if ($result === false) {
            $error = "Error updating user: " . mysqli_stmt_error($update_stmt);
        } else {
            $success = "User updated successfully";
            // Refresh user data
            $user['username'] = $username;
            $user['email'] = $email;
            $user['user_role'] = $user_role;
        }

        mysqli_stmt_close($update_stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Edit User</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="user_role" class="form-label">User Role</label>
                <select class="form-control" id="user_role" name="user_role" required>
                    <option value="user" <?php echo $user['user_role'] === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo $user['user_role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Registration Date</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['registration_date']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Last Login</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['last_login']); ?>" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>