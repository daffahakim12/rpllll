<?php
session_start();

// Koneksi ke database (MySQL)
$host = "localhost";
$user = "root";
$pass = ""; // ganti jika password MySQL kamu berbeda
$db   = "lcis_db"; // GANTI dengan nama database kamu

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (!$username || !$email || !$password || !$confirm) {
        $message = "Semua field wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
    } elseif ($password !== $confirm) {
        $message = "Password dan konfirmasi tidak sama!";
    } else {
        // Cek email atau username sudah dipakai atau belum
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Email atau Username sudah terdaftar!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert->bind_param('sss', $username, $email, $hash);
            if ($insert->execute()) {
                header("Location: login.php?success=register");
                exit();
            } else {
                $message = "Registrasi gagal. Coba lagi!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LCIS - Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6dd5ed, #2193b0);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 420px;
        }
        .brand-title {
            font-weight: 700;
            color: #2193b0;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            font-size: 2.8rem;
        }
        .small-link {
            font-size: 0.95em;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="text-center mb-4">
            <div class="brand-title">LCIS</div>
            <p class="text-muted mb-1">Buat akun baru untuk melanjutkan</p>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" autocomplete="off">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" id="username" required autofocus>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" name="confirm" id="confirm" required>
            </div>
            <div class="d-grid mb-2">
                <button type="submit" class="btn btn-primary">Daftar</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <span>Sudah punya akun? <a href="login.php" class="small-link">Login</a></span>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>