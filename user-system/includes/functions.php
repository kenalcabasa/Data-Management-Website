<?php
require_once __DIR__ . '/../config/database.php';

// Sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to a page
function redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit();
    } else {
        echo "<script>window.location.href='$url';</script>";
        exit();
    }
}

// Display error message
function displayError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Display success message
function displaySuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Log user activity (optional)
function logActivity($user_id, $action) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $action);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

// Check if session token is valid
function validateSession() {
    if (isset($_COOKIE['session_token'])) {
        $conn = getDBConnection();
        $token = $_COOKIE['session_token'];
        
        $stmt = $conn->prepare("SELECT user_id FROM sessions WHERE session_token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            $_SESSION['user_id'] = $session['user_id'];
            return true;
        }
        
        $stmt->close();
        $conn->close();
    }
    return false;
}
?>