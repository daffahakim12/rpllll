<?php
session_start();
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
    <title>LCIS - Login Baru</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6dd5ed, #2193b0);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 400px;
        }
        .login-card .form-control:focus {
            border-color: #2193b0;
            box-shadow: none;
        }
        .brand-title {
            font-weight: 700;
            color: #2193b0;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            font-size: 2.8rem; /* Tambahkan ini untuk memperbesar tulisan LCIS */
        }
        .small-link {
            font-size: 0.95em;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="brand-title">LCIS</div>
            <p class="text-muted mb-1">Silakan login untuk melanjutkan</p>
        </div>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger mt-3">
                <?php
                if ($_GET['error'] == 'notfound') {
                    echo "Akun tidak ditemukan. Silakan daftar terlebih dahulu.";
                } elseif ($_GET['error'] == 'password') {
                    echo "Password salah.";
                } elseif ($_GET['error'] == 'empty') {
                    echo "Email dan password wajib diisi.";
                }
                ?>
            </div>
        <?php endif; ?>
        <form action="process/login_process.php" method="POST" autocomplete="off">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label d-flex justify-content-between">
                    <span>Password</span>
                    <a href="forgot_password.php" class="small-link">Lupa Password?</a>
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" name="password" id="password" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="d-grid mb-2">
                <button type="submit" class="btn btn-primary">Masuk</button>
            </div>
        </form>
        <div class="text-center mt-3">
            <span>Belum punya akun? <a href="register.php" class="small-link">Daftar</a></span>
            <br>
            <a href="admin/admin_login.php" class="small-link text-secondary mt-2 d-inline-block">
                <i class="fa fa-user-shield"></i> Admin Login
            </a>
        </div>
        <?php if (isset($_GET['error']) && $_GET['error'] == 'db'): ?>
        <div class="alert alert-warning mt-3">
            Terjadi masalah dengan database. <a href="fix_users_table.php" class="alert-link">Klik di sini</a> untuk memperbaiki.
        </div>
        <?php endif; ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>