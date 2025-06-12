<?php
session_start();

// Jika sudah login, arahkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LCIS - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Assets/LR.css">
</head>
<body>
    <div class="container-fluid p-0 m-0">
        <div class="row g-0">
            <div class="col-md-6 form-container d-flex flex-column justify-content-center">
                <div class="form-wrapper">
                    <div class="logo-container mb-4 text-center">
                        <!-- Logo dan Nama Aplikasi -->
                        <img src="Assets/Logo.png" alt="LCIS Logo" class="logo mb-2">
                        <h2 class="fw-bold mb-0">LCIS</h2>
                        <small class="text-muted">Layanan Customer Information System</small>
                    </div>
                    <div class="mb-4 text-center">
                        <p class="lead">Selamat Datang di <b>LCIS</b></p>
                    </div>
                    <form class="mb-3" id="loginForm" action="process/login_process.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#0d6efd" class="bi bi-eye" viewBox="0 0 16 16">
                                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Tombol Login -->
                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user me-2"></i>Log in
                            </button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p>Belum Punya Akun? <a href="register.php" class="text-decoration-none">Daftar</a></p>
                        <p class="mt-2">
                            <a href="admin/admin_login.php" class="text-decoration-none text-muted">
                                <i class="fas fa-user-shield me-1"></i>Login sebagai Admin
                            </a>
                        </p>
                    </div>
                    <?php if (isset($_GET['error']) && $_GET['error'] == 'db'): ?>
                    <div class="alert alert-warning mt-3">
                        <p class="mb-0">Terjadi masalah dengan database. <a href="fix_users_table.php" class="alert-link">Klik di sini</a> untuk memperbaiki masalah.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6 image-container p-0">
                <img src="https://i.pinimg.com/736x/95/2b/b2/952bb2892be9b847466c6d00a6653bb2.jpg" alt="Colorful Buses" class="img-fluid bus-image">
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    // Toggle password visibility
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        // Optional: Ganti ikon ketika password terlihat
        this.querySelector('svg').classList.toggle('bi-eye');
        this.querySelector('svg').classList.toggle('bi-eye-slash');
    });
    </script>
</body>
</html>