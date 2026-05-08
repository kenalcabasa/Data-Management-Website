<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Clear session token from database if exists
if (isset($_COOKIE['session_token'])) {
    $conn = getDBConnection();
    $token = $_COOKIE['session_token'];
    
    $stmt = $conn->prepare("DELETE FROM sessions WHERE session_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    
    // Remove cookie
    setcookie('session_token', '', time() - 3600, '/');
}

// Destroy session
$_SESSION = array();
session_destroy();

// Redirect to index
redirect('index.php');
?>