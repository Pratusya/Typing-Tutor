<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $wpm = intval($_POST['wpm']);
    $cpm = intval($_POST['cpm']);
    $accuracy = floatval($_POST['accuracy']);
    $errors = intval($_POST['errors']);
    $backspaces = intval($_POST['backspaces']);
    $create_datetime = date("Y-m-d H:i:s");

    $stmt = $con->prepare("INSERT INTO typing_history (username, wpm, cpm, accuracy, errors, backspaces, create_datetime) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siidiii", $username, $wpm, $cpm, $accuracy, $errors, $backspaces, $create_datetime);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request or user not logged in']);
}

$con->close();
?>