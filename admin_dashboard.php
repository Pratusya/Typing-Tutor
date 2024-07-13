<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';
require_once 'utilities.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: Admin-login.php");
    exit;
}

// Function to get all users (with pagination)
function getAllUsers($con, $page = 1, $limit = 10, $search = '') {
    $offset = ($page - 1) * $limit;
    $search = '%' . $search . '%';
    $query = "SELECT id, username, email, registration_date FROM users 
              WHERE (username LIKE ? OR email LIKE ?)
              LIMIT ? OFFSET ?";
    
    $stmt = mysqli_prepare($con, $query);
    if ($stmt === false) {
        logError("Prepare failed: " . mysqli_error($con), "error_log.txt");
        return false;
    }
    
    if (!mysqli_stmt_bind_param($stmt, "ssii", $search, $search, $limit, $offset)) {
        logError("Binding parameters failed: " . mysqli_stmt_error($stmt), "error_log.txt");
        mysqli_stmt_close($stmt);
        return false;
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        logError("Execute failed: " . mysqli_stmt_error($stmt), "error_log.txt");
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        logError("Getting result set failed: " . mysqli_stmt_error($stmt), "error_log.txt");
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $users;
}

// Function to get total number of users
function getTotalUsers($con, $search = '') {
    $search = '%' . $search . '%';
    $query = "SELECT COUNT(*) as total FROM users WHERE (username LIKE ? OR email LIKE ?)";
    $stmt = mysqli_prepare($con, $query);
    
    if ($stmt === false) {
        logError("Prepare failed: " . mysqli_error($con), "error_log.txt");
        return false;
    }
    
    if (!mysqli_stmt_bind_param($stmt, "ss", $search, $search)) {
        logError("Binding parameters failed: " . mysqli_stmt_error($stmt), "error_log.txt");
        mysqli_stmt_close($stmt);
        return false;
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        logError("Execute failed: " . mysqli_stmt_error($stmt), "error_log.txt");
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if ($result === false) {
        logError("Getting result set failed: " . mysqli_stmt_error($stmt), "error_log.txt");
        mysqli_stmt_close($stmt);
        return false;
    }
    
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row['total'];
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$users = getAllUsers($con, $page, $limit, $search);

if ($users === false) {
    die("Failed to retrieve users. Check the error log for details.");
}

$total_users = getTotalUsers($con, $search);
if ($total_users === false) {
    die("Failed to get total number of users. Check the error log for details.");
}

$total_pages = ceil($total_users / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="fas fa-users"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="add_admin.php">
                                <i class="fas fa-user-plus"></i> Add New Admin
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin-logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">User Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <form action="" method="get" class="row g-3">
                        <div class="col-auto">
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-3">Search</button>
                        </div>
                    </form>
                </div>

                <div class="mb-3">
                    <a href="add_user.php" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Add New User
                    </a>
                </div>

                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
                ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['registration_date']); ?></td>
                                <td>
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                    <button class="btn btn-sm btn-danger delete-user" data-id="<?php echo $user['id']; ?>"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let deleteUserId;

            $('.delete-user').click(function() {
                deleteUserId = $(this).data('id');
                $('#deleteModal').modal('show');
            });

            $('#confirmDelete').click(function() {
                $.ajax({
                    url: 'delete_user.php',
                    method: 'POST',
                    data: { id: deleteUserId },
                    success: function(response) {
                        if (response === 'success') {
                            location.reload();
                        } else {
                            alert('Error deleting user. Please try again.');
                        }
                    },
                    error: function() {
                        alert('Error deleting user. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>