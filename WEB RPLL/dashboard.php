<?php
session_start();
include_once 'includes/functions.php';
requireLogin();

$conn = connectDB();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LCIS</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .card-glow:hover {
            box-shadow: 0 8px 32px 0 rgba(99,102,241,0.10), 0 1.5px 8px 0 rgba(0,0,0,0.06);
            transform: translateY(-2px) scale(1.01);
        }
        .sidebar-gradient {
            background: linear-gradient(135deg, #6366f1, #3b82f6 80%);
        }
        .nav-active {
            background: #fff;
            color: #6366f1 !important;
        }
        .nav-active i { color: #6366f1 !important; }
        .notif-badge {
            background: #ef4444;
            color: #fff;
            font-size: 0.7em;
            border-radius: 99px;
            padding: 2px 7px;
            position: absolute;
            right: -10px;
            top: -5px;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-50">
<div class="flex h-screen">
    <!-- Sidebar -->
    <aside class="hidden md:flex w-64 flex-col sidebar-gradient text-white">
        <div class="flex items-center justify-center h-16 bg-indigo-800">
            <span class="text-2xl font-bold tracking-wide">LCIS</span>
        </div>
        <div class="flex flex-col flex-grow px-4 py-4 overflow-y-auto">
            <div class="flex items-center px-4 py-3 mt-2 rounded-lg bg-indigo-600 shadow">
                <img class="w-9 h-9 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=6366f1&color=fff" alt="User avatar">
                <div class="ml-3">
                    <span class="font-semibold text-lg"><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></span>
                    <div class="text-xs text-indigo-100 opacity-70"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
            </div>
            <nav class="mt-7 space-y-2">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium nav-active shadow transition">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="upload_task.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition">
                    <i class="fas fa-upload"></i> Upload Tugas
                </a>
                <a href="search_task.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition">
                    <i class="fas fa-search"></i> Cari Tugas
                </a>
                <a href="mentor/mentor_application.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition">
                    <i class="fas fa-chalkboard-teacher"></i> Mentor Program
                </a>
            </nav>
            <div class="mt-auto pt-4 border-t border-indigo-400/40">
                <a href="logout.php" class="flex items-center gap-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition" onclick="return confirm('Keluar dari akun?')">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Mobile Navbar -->
        <header class="md:hidden sticky top-0 z-10 bg-white shadow-sm">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-2">
                    <button id="mobile-menu-button" class="text-indigo-600 text-xl">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="ml-2 text-2xl font-bold">LCIS</span>
                </div>
                <div class="relative">
                    <img class="w-9 h-9 rounded-full border-2 border-indigo-200" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=6366f1&color=fff" alt="User avatar">
                    <span class="notif-badge" title="1 Notifikasi">1</span>
                </div>
            </div>
        </header>

        <!-- Mobile Sidebar -->
        <div id="mobile-menu" class="hidden md:hidden absolute top-0 left-0 w-64 h-full z-40 sidebar-gradient text-white shadow-lg">
            <div class="flex flex-col h-full">
                <div class="flex items-center justify-center h-16 bg-indigo-800">
                    <span class="text-2xl font-bold tracking-wide">LCIS</span>
                </div>
                <div class="flex items-center px-4 py-3 mt-2 rounded-lg bg-indigo-600 shadow">
                    <img class="w-9 h-9 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=6366f1&color=fff" alt="User avatar">
                    <div class="ml-3">
                        <span class="font-semibold text-lg"><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></span>
                        <div class="text-xs text-indigo-100 opacity-70"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>
                </div>
                <nav class="mt-7 space-y-2 flex-1">
                    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium nav-active shadow transition">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="upload_task.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition">
                        <i class="fas fa-upload"></i> Upload Tugas
                    </a>
                    <a href="search_task.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition">
                        <i class="fas fa-search"></i> Cari Tugas
                    </a>
                    <a href="mentor/mentor_application.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition">
                        <i class="fas fa-chalkboard-teacher"></i> Mentor Program
                    </a>
                </nav>
                <div class="px-4 pb-4">
                    <a href="logout.php" class="flex items-center gap-3 px-4 py-2 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition" onclick="return confirm('Keluar dari akun?')">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Main -->
        <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Halo, <?php echo htmlspecialchars($user['username'] ?? 'User'); ?> 👋</h1>
                    <span class="bg-indigo-100 text-indigo-600 px-3 py-1 rounded-full text-sm font-medium hidden md:inline-block">Mahasiswa</span>
                </div>

                <!-- Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                    <!-- Upload Task -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 card-glow flex flex-col items-start">
                        <div class="flex items-center mb-4">
                            <span class="p-3 rounded-xl bg-indigo-100 text-indigo-600 text-xl"><i class="fas fa-upload"></i></span>
                            <span class="ml-4 text-lg font-semibold text-gray-800">Upload Tugas</span>
                        </div>
                        <p class="text-gray-500 mb-5">Jual tugas kamu dan dapatkan penghasilan tambahan.</p>
                        <a href="upload_task.php" class="mt-auto inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-plus mr-2"></i>Upload Sekarang
                        </a>
                    </div>
                    <!-- Cari Task -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 card-glow flex flex-col items-start">
                        <div class="flex items-center mb-4">
                            <span class="p-3 rounded-xl bg-blue-100 text-blue-600 text-xl"><i class="fas fa-search"></i></span>
                            <span class="ml-4 text-lg font-semibold text-gray-800">Cari Tugas</span>
                        </div>
                        <p class="text-gray-500 mb-5">Temukan tugas dari mahasiswa lain untuk membantumu belajar.</p>
                        <a href="search_task.php" class="mt-auto inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            <i class="fas fa-search mr-2"></i>Cari Tugas
                        </a>
                    </div>
                    <!-- Mentor -->
                    <div class="bg-white rounded-2xl shadow-lg p-6 card-glow flex flex-col items-start">
                        <div class="flex items-center mb-4">
                            <span class="p-3 rounded-xl bg-purple-100 text-purple-600 text-xl"><i class="fas fa-chalkboard-teacher"></i></span>
                            <span class="ml-4 text-lg font-semibold text-gray-800">Mentor Program</span>
                        </div>
                        <p class="text-gray-500 mb-5">Bagikan ilmu dan pengalamanmu dengan menjadi mentor.</p>
                        <a href="mentor/mentor_application.php" class="mt-auto inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                            <i class="fas fa-edit mr-2"></i>Daftar Mentor
                        </a>
                    </div>
                </div>

                <!-- Recent Activity / Info -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center mb-4">
                        <span class="p-2 rounded-lg bg-indigo-100 text-indigo-600 text-lg"><i class="fas fa-bell"></i></span>
                        <h2 class="ml-3 text-xl font-semibold text-gray-800">Aktivitas Terbaru</h2>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-green-100 text-green-600 mr-3">
                                <i class="fas fa-check"></i>
                            </span>
                            <div>
                                <p class="font-medium text-gray-900">Selamat datang di LCIS!</p>
                                <p class="text-gray-500 text-sm">Aplikasi jual beli tugas khusus mahasiswa. Upload tugasmu dan temukan tugas yang kamu butuhkan.</p>
                                <span class="text-xs text-gray-400">Baru saja</span>
                            </div>
                        </div>
                        <!-- Tambahkan aktivitas lain jika ada -->
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script>
    // Mobile menu toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
    // Confirm logout
    document.querySelectorAll('[href="logout.php"]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Keluar dari akun?')) e.preventDefault();
        });
    });
</script>
</body>
</html>
