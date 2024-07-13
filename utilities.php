<?php
// Check if functions are already defined to avoid redeclaration
if (!function_exists('safe_input')) {
    function safe_input($con, $input) {
        return mysqli_real_escape_string($con, trim($input));
    }
}

if (!function_exists('logError')) {
    function logError($error, $file = "error_log.txt") {
        $timestamp = date("Y-m-d H:i:s");
        $log_entry = "[$timestamp] $error\n";
        file_put_contents($file, $log_entry, FILE_APPEND);
    }
}

if (!function_exists('record_user_login')) {
    function record_user_login($con, $user_id) {
        $query = "INSERT INTO user_logins (user_id) VALUES (?)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            logError("Failed to record user login: " . mysqli_error($con));
        }
        mysqli_stmt_close($stmt);
    }
}

if (!function_exists('record_admin_login')) {
    function record_admin_login($con, $admin_id) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        $query = "INSERT INTO admin_logins (admin_id, ip_address) VALUES (?, ?)";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "is", $admin_id, $ip_address);
        
        if (!mysqli_stmt_execute($stmt)) {
            logError("Failed to record admin login: " . mysqli_error($con));
        }
        mysqli_stmt_close($stmt);
    }
}

// Add any other utility functions you have here, using the same pattern:
// if (!function_exists('function_name')) { ... }
?>