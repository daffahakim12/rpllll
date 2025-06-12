<?php
session_start();
include_once 'includes/functions.php';
requireLogin(); // Ensure user is logged in

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $expertise = sanitizeInput($_POST['expertise']);
    $available_schedule = sanitizeInput($_POST['available_schedule']);
    $rate_per_session = sanitizeInput($_POST['rate_per_session']);
    $mentoring_method = sanitizeInput($_POST['mentoring_method']);

    // Insert mentor application into database
    $conn = connectDB();
    $user_id = $_SESSION['user_id'];
    $sql = "INSERT INTO mentor_applications (user_id, expertise, available_schedule, rate_per_session, mentoring_method, status) 
            VALUES ('$user_id', '$expertise', '$available_schedule', '$rate_per_session', '$mentoring_method', 'Pending Review')";

    if ($conn->query($sql) === TRUE) {
        echo "Your mentor application has been submitted!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Mentor - LCIS</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <header>
        <h1>Apply to Become a Mentor</h1>
    </header>
    <main>
        <form method="POST" action="">
            <label for="expertise">Expertise:</label>
            <input type="text" name="expertise" required>
            <label for="available_schedule">Available Schedule:</label>
            <input type="text" name="available_schedule" required>
            <label for="rate_per_session">Rate per Session:</label>
            <input type="text" name="rate_per_session" required>
            <label for="mentoring_method">Mentoring Method:</label>
            <input type="text" name="mentoring_method" required>
            <button type="submit">Submit Application</button>
        </form>
    </main>
</body>
</html>
