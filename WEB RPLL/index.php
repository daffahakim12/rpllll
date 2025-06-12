<?php
session_start();
include_once 'includes/functions.php';  // Include the functions file

// Redirect to dashboard if logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Center Information System</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Welcome to Learning Center Information System</h1>
        <nav>
            <ul>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section>
            <h2>Your Learning Hub</h2>
            <p>Start your journey with learning tasks and mentoring services!</p>
        </section>
    </main>
</body>
</html>
