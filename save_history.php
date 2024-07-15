<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $wpm = intval($_POST['wpm']);
    $cpm = intval($_POST['cpm']);
    $accuracy = floatval($_POST['accuracy']);
    $errors = intval($_POST['errors']);
    $backspaces = intval($_POST['backspaces']);
    $timestamp = date('Y-m-d H:i:s');

    try {
        $stmt = $con->prepare("INSERT INTO typing_history (username, wpm, cpm, accuracy, errors, backspaces, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $con->error);
        }

        $stmt->bind_param("siidiis", $username, $wpm, $cpm, $accuracy, $errors, $backspaces, $timestamp);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Fetch updated history
        $history_stmt = $con->prepare("SELECT wpm, accuracy, timestamp FROM typing_history WHERE username = ? ORDER BY timestamp DESC LIMIT 10");
        $history_stmt->bind_param("s", $username);
        $history_stmt->execute();
        $result = $history_stmt->get_result();
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }

        echo json_encode(['success' => true, 'message' => 'Data saved successfully', 'history' => $history]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($history_stmt)) {
            $history_stmt->close();
        }
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request or user not logged in']);
}

$con->close();
?>