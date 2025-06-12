<?php
session_start();
require '../includes/db_connect.php'; // pastikan path dan koneksi sudah benar

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    header("Location: ../login.php?error=empty");
    exit();
}

$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Email ditemukan, cek password
    if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['user_email'] = $row['email'];
        header("Location: ../index.php");
        exit();
    } else {
        header("Location: ../login.php?error=password");
        exit();
    }
} else {
    // Email tidak terdaftar
    header("Location: ../login.php?error=notfound");
    exit();
}
?>