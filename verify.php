<?php
require('db.php');

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $con->prepare("SELECT * FROM users WHERE verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        $update_stmt = $con->prepare("UPDATE users SET verified = TRUE, verification_token = NULL WHERE id = ?");
        $update_stmt->bind_param("i", $user['id']);
        
        if ($update_stmt->execute()) {
            echo "<script>
                  alert('Your email has been verified. You can now log in.');
                  window.location.href = 'login.php';
                  </script>";
        } else {
            echo "Error verifying email. Please try again or contact support.";
        }
        
        $update_stmt->close();
    } else {
        echo "Invalid verification token.";
    }
    
    $stmt->close();
} else {
    echo "No verification token provided.";
}
?>