<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$dbname = "LoginSystem";

// Create connection
$con = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (mysqli_connect_errno()) {
    die("Failed to connect to MySQL: " . mysqli_connect_error());
}

// Create or update the users table
$users_table_query = "
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `registration_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `user_role` enum('user','admin') DEFAULT 'user',
  `otp` varchar(6) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `paid` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!mysqli_query($con, $users_table_query)) {
    die("Error creating users table: " . mysqli_error($con));
}

// Create user_logins table
$user_logins_table_query = "
CREATE TABLE IF NOT EXISTS `user_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!mysqli_query($con, $user_logins_table_query)) {
    die("Error creating user_logins table: " . mysqli_error($con));
}

// Create admin_logins table
$admin_logins_table_query = "
CREATE TABLE IF NOT EXISTS `admin_logins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `login_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!mysqli_query($con, $admin_logins_table_query)) {
    die("Error creating admin_logins table: " . mysqli_error($con));
}

// Create admin table
$admin_table_query = "
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `registration_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if (!mysqli_query($con, $admin_table_query)) {
    die("Error creating admins table: " . mysqli_error($con));
}

// Check if the 'paid' column exists, if not, add it
$check_paid_column_query = "
SHOW COLUMNS FROM `users` LIKE 'paid'
";

$result = mysqli_query($con, $check_paid_column_query);

if (mysqli_num_rows($result) == 0) {
    $add_paid_column_query = "
    ALTER TABLE `users`
    ADD COLUMN `paid` tinyint(1) DEFAULT 0
    ";

    if (!mysqli_query($con, $add_paid_column_query)) {
        die("Error adding 'paid' column: " . mysqli_error($con));
    }
}

// Function to safely escape user inputs
function safe_input($con, $input) {
    return mysqli_real_escape_string($con, trim($input));
}

// Function to log errors
function logError($error, $file = "error_log.txt") {
    $timestamp = date("Y-m-d H:i:s");
    $log_entry = "[$timestamp] $error\n";
    file_put_contents($file, $log_entry, FILE_APPEND);
}

// Other utility functions can be added here as needed

?>