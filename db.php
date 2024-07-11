<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enter your host name, database username, password, and database name.
// If you have not set database password on localhost then set empty.
$con = mysqli_connect("localhost","root","","LoginSystem");
// Check connection
if (mysqli_connect_errno()){
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit();
}

// Add new columns if they don't exist
$alter_query = "
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS otp VARCHAR(6) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS verified TINYINT(1) DEFAULT 0";

if (!mysqli_query($con, $alter_query)) {
    echo "Error altering table: " . mysqli_error($con);
    exit();
}
?>