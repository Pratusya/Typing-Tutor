<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db.php';
require_once 'utilities.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $user_id = intval($_POST['id']);
    $query = "DELETE FROM users WHERE id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        echo 'success';
    } else {
        logError("Error deleting user: " . mysqli_error($con), "error_log.txt");
        echo 'error';
    }
    mysqli_stmt_close($stmt);
} else {
    echo 'error';
}