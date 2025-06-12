<?php
session_start();
include_once 'includes/functions.php'; // Include functions file
include_once 'includes/db_connect.php'; // Include the DB connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitizeInput($_POST['username']);
    $password = sanitizeInput($_POST['password']);
    $email = sanitizeInput($_POST['email']);

    // Insert user data into database
    $conn = connectDB();  // Make sure to establish the connection
    $sql = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['user_id'] = $conn->insert_id;
        header("Location: dashboard.php");
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!-- HTML Code -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LCIS</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Register</h1>
    </header>
    <main>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" name="username" required>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <button type="submit">Register</button>
        </form>
        <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </main>
</body>
</html>
