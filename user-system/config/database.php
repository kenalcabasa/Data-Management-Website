<?php
// InfinityFree Database Configuration
// Get these details from your InfinityFree control panel under "MySQL Databases"

define('DB_HOST', 'sql304.infinityfree.com'); // Replace with your InfinityFree MySQL host
define('DB_USER', 'if0_41634827'); // Replace with your InfinityFree database username
define('DB_PASS', 'O57nEre1y8v92gY'); // Replace with your database password
define('DB_NAME', 'if0_41634827_keneth'); // Replace with your database name

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set timezone (adjust as needed)
date_default_timezone_set('Asia/Manila');
?>