<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $stmt = $con->prepare("SELECT * FROM typing_history WHERE username = ? ORDER BY timestamp DESC LIMIT 10");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['success' => true, 'history' => $history]);
} else {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
}

$con->close();
?>