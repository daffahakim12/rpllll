<?php
session_start();
include_once 'includes/functions.php';
include_once 'includes/db_connect.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch user data for navbar
$stmt_user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

// Course data by semester
$courses_by_semester = [
    1 => ['Pengantar Sistem Informasi', 'Matematika Dasar', 'Dasar-Dasar Pemrograman', 'Logika Matematika', 'Aljabar Linear', 'Bahasa Indonesia', 'Pendidikan Pancasila'],
    2 => ['Statistika Dasar', 'Struktur Data', 'Matematika Diskrit', 'Pemrograman Berorientasi Objek', 'Basis Data I', 'Kewarganegaraan', 'Bahasa Inggris'],
    3 => ['Sistem Operasi', 'Algoritma dan Pemrograman', 'Analisis dan Perancangan Sistem', 'Teori Automata dan Bahasa Formal', 'Kalkulus', 'E-commerce', 'Praktikum Basis Data'],
    4 => ['Jaringan Komputer', 'Pemrograman Web', 'Rekayasa Perangkat Lunak', 'Sistem Basis Data Lanjut', 'Sistem Informasi Manajemen', 'Sistem Informasi Geografis (SIG)', 'Etika Profesional di Teknologi Informasi'],
    5 => ['Manajemen Proyek TI', 'Keamanan Sistem Informasi', 'Pengolahan Citra Digital', 'Sistem Informasi Akuntansi', 'Business Intelligence', 'Pengembangan Aplikasi Mobile', 'Praktikum Jaringan Komputer'],
    6 => ['Data Mining', 'Enterprise Architecture', 'Sistem Pendukung Keputusan', 'Cloud Computing', 'Kecerdasan Buatan (Artificial Intelligence)', 'Praktikum Pemrograman Web Lanjut', 'Praktikum Rekayasa Perangkat Lunak'],
    7 => ['Manajemen Teknologi Informasi', 'Sistem Informasi Enterprise', 'Big Data dan Analitik', 'Sistem Informasi Kesehatan', 'Sistem Informasi Berbasis Web', 'Pengembangan Sistem Informasi Lanjut', 'Praktikum Big Data'],
    8 => ['Tugas Akhir / Skripsi', 'Praktikum Sistem Informasi', 'Manajemen dan Audit Sistem Informasi', 'Etika dan Hukum Teknologi Informasi', 'Entrepreneurship di Bidang TI']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $semester = intval($_POST['semester'] ?? 0);
    $course = sanitizeInput($_POST['course'] ?? '');

    // Validate required fields
    if (empty($title)) {
        $error_message = "Judul tugas wajib diisi.";
    } elseif ($price < 0) {
        $error_message = "Harga tidak boleh negatif.";
    } elseif ($semester < 1 || $semester > 8) {
        $error_message = "Silakan pilih semester yang valid.";
    } elseif (empty($course)) {
        $error_message = "Silakan pilih mata kuliah.";
    } else {
        // Insert task into database
        $stmt_task = $conn->prepare("INSERT INTO tasks (user_id, title, description, price, semester, course, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt_task->bind_param("issdis", $user_id, $title, $description, $price, $semester, $course);

        if ($stmt_task->execute()) {
            $task_id = $conn->insert_id;
            $success_message = "Tugas berhasil diupload!";

            // Handle file upload if provided
            if (isset($_FILES['task_file']) && $_FILES['task_file']['error'] == 0) {
                $upload_dir = 'uploads/tasks/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_name = $_FILES['task_file']['name'];
                $file_tmp = $_FILES['task_file']['tmp_name'];
                $file_size = $_FILES['task_file']['size'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                $allowed_types = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'gif'];

                if (in_array($file_ext, $allowed_types)) {
                    $new_file_name = $task_id . '_' . time() . '_' . $file_name;
                    $file_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $stmt_file = $conn->prepare("INSERT INTO task_files (task_id, file_name, file_path, file_size) VALUES (?, ?, ?, ?)");
                        $stmt_file->bind_param("issi", $task_id, $file_name, $file_path, $file_size);
                        $stmt_file->execute();
                        $stmt_file->close();
                    }
                }
            }

            // Clear form data on success
            $title = $description = $course = '';
            $price = $semester = 0;
        } else {
            $error_message = "Terjadi kesalahan saat upload: " . $conn->error;
        }
        $stmt_task->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Tugas - LCIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    </style>
    <script>
        function updateCourses() {
            const semesterSelect = document.getElementById('semester');
            const courseSelect = document.getElementById('course');
            const semester = semesterSelect.value;
            courseSelect.innerHTML = '<option value="">Pilih Mata Kuliah</option>';
            if (semester) {
                const courses = <?php echo json_encode($courses_by_semester); ?>;
                const semesterCourses = courses[semester] || [];
                semesterCourses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course;
                    option.textContent = course;
                    courseSelect.appendChild(option);
                });
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            updateCourses();
            // Set selected course jika pernah submit
            const courseSelect = document.getElementById('course');
            <?php if (!empty($course)) : ?>
                courseSelect.value = <?php echo json_encode($course); ?>;
            <?php endif; ?>
            // File name preview
            document.getElementById('task_file').addEventListener('change', function(e) {
                const fileName = e.target.files.length ? e.target.files[0].name : 'Belum ada file';
                document.getElementById('file-name').textContent = fileName;
            });
        });
    </script>
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
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="upload_task.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium nav-active shadow transition">
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
    <div class="flex-1 flex flex-col overflow-y-auto">
        <!-- Mobile Navbar -->
        <header class="md:hidden sticky top-0 z-10 bg-white shadow-sm">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center gap-2">
                    <button id="mobile-menu-button" class="text-indigo-600 text-xl">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="ml-2 text-2xl font-bold">LCIS</span>
                </div>
                <div>
                    <img class="w-9 h-9 rounded-full border-2 border-indigo-200" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=6366f1&color=fff" alt="User avatar">
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
                    <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium hover:bg-white hover:text-indigo-600 transition">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="upload_task.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium nav-active shadow transition">
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
        <main class="flex-1 p-6 bg-gray-50 min-h-screen">
            <div class="max-w-3xl mx-auto">
                <a href="dashboard.php" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 mb-6 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Dashboard
                </a>
                <div class="bg-white rounded-xl shadow-lg p-8 card-glow">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">Upload Tugas Baru</h1>
                        <p class="text-gray-600">Isi form di bawah untuk mengunggah tugas yang ingin kamu jual atau share.</p>
                    </div>
                    <?php if ($success_message): ?>
                        <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                <p><?php echo htmlspecialchars($success_message); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($error_message): ?>
                        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                <p><?php echo htmlspecialchars($error_message); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Judul -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Judul Tugas <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="title" name="title"
                                value="<?php echo htmlspecialchars($title ?? ''); ?>"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Masukkan judul tugas..." required>
                        </div>
                        <!-- Semester dan Mata Kuliah -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">
                                    Semester <span class="text-red-500">*</span>
                                </label>
                                <select id="semester" name="semester" required onchange="updateCourses()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Pilih Semester</option>
                                    <?php for ($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo ($semester ?? 0) == $i ? 'selected' : ''; ?>>
                                            Semester <?php echo $i; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label for="course" class="block text-sm font-medium text-gray-700 mb-1">
                                    Mata Kuliah <span class="text-red-500">*</span>
                                </label>
                                <select id="course" name="course" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Pilih Mata Kuliah</option>
                                </select>
                            </div>
                        </div>
                        <!-- Deskripsi -->
                        <div class="mb-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Deskripsi Tugas
                            </label>
                            <textarea id="description" name="description" rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Jelaskan detail tugas..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        </div>
                        <!-- Harga -->
                        <div class="mb-6">
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                Harga (Rp)
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">Rp</span>
                                </div>
                                <input type="number" id="price" name="price"
                                    value="<?php echo htmlspecialchars($price ?? 0); ?>"
                                    class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="0" min="0" step="1000">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">IDR</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Isi 0 jika ingin membagikan tugas secara gratis</p>
                        </div>
                        <!-- File Upload -->
                        <div class="mb-8">
                            <label for="task_file" class="block text-sm font-medium text-gray-700 mb-1">
                                Lampirkan File (Opsional)
                            </label>
                            <div class="mt-1 flex items-center">
                                <label for="task_file" class="cursor-pointer">
                                    <span class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-paperclip mr-2"></i>
                                        Pilih File
                                    </span>
                                    <input id="task_file" name="task_file" type="file" class="sr-only"
                                        accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif">
                                </label>
                                <span id="file-name" class="ml-2 text-sm text-gray-500">Belum ada file</span>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Tipe file yang diijinkan: PDF, DOC, DOCX, TXT, JPG, PNG, GIF (Max: 5MB)
                            </p>
                        </div>
                        <!-- Aksi Form -->
                        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-200">
                            <button type="button" onclick="window.location.href='dashboard.php'"
                                class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Batal
                            </button>
                            <button type="submit"
                                class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <i class="fas fa-plus-circle mr-2"></i>
                                Upload Tugas
                            </button>
                        </div>
                    </form>
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
</script>
</body>
</html>
