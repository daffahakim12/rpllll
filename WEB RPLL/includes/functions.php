<?php
/// Include the DB connection
include_once 'db_connect.php'; // Make sure this includes the connection to the database

// Sanitize user inputs to prevent SQL injection
function sanitizeInput($data) {
    global $conn;  // Ensure that the $conn variable is available from the database connection
    if ($conn) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $conn->real_escape_string($data);
    }
    return $data;  // If the connection is null, just return the raw input
}

// Check if a user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to login page if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}
?>
