<?php
session_start();
include_once 'includes/functions.php';
include_once 'includes/db_connect.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

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
        $error_message = "Task title is required.";
    } elseif ($price < 0) {
        $error_message = "Price cannot be negative.";
    } elseif ($semester < 1 || $semester > 8) {
        $error_message = "Please select a valid semester.";
    } elseif (empty($course)) {
        $error_message = "Please select a course.";
    } else {
        // Insert task into database
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, price, semester, course, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issdis", $user_id, $title, $description, $price, $semester, $course);
        
        if ($stmt->execute()) {
            $task_id = $conn->insert_id;
            $success_message = "Task created successfully!";
            
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
                        $stmt = $conn->prepare("INSERT INTO task_files (task_id, file_name, file_path, file_size) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("issi", $task_id, $file_name, $file_path, $file_size);
                        $stmt->execute();
                        $stmt->close();
                    }
                }
            }
            
            // Clear form data on success
            $title = $description = $course = '';
            $price = $semester = 0;
        } else {
            $error_message = "Error creating task: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Task - LCIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .input-focus {
                @apply focus:ring-2 focus:ring-primary-500 focus:border-primary-500;
            }
            .animate-fade-in {
                animation: fadeIn 0.3s ease-in-out;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
        }
    </style>
    <script>
        function updateCourses() {
            const semesterSelect = document.getElementById('semester');
            const courseSelect = document.getElementById('course');
            const semester = semesterSelect.value;
            
            // Clear existing options
            courseSelect.innerHTML = '<option value="">Select Course</option>';
            
            if (semester) {
                // Get courses for selected semester from PHP variable
                const courses = <?php echo json_encode($courses_by_semester); ?>;
                const semesterCourses = courses[semester] || [];
                
                // Add new options
                semesterCourses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course;
                    option.textContent = course;
                    courseSelect.appendChild(option);
                });
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-tooltip-target]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                const tooltipId = tooltipTriggerEl.getAttribute('data-tooltip-target');
                const tooltipEl = document.getElementById(tooltipId);
                
                tooltipTriggerEl.addEventListener('mouseenter', function() {
                    tooltipEl.classList.remove('hidden');
                });
                
                tooltipTriggerEl.addEventListener('mouseleave', function() {
                    tooltipEl.classList.add('hidden');
                });
            });
            
            // Format price input
            const priceInput = document.getElementById('price');
            if (priceInput) {
                priceInput.addEventListener('input', function(e) {
                    // Remove non-numeric characters
                    let value = e.target.value.replace(/[^0-9]/g, '');
                    e.target.value = value;
                });
            }
        });
    </script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <!-- Back button -->
        <a href="dashboard.php" class="inline-flex items-center text-primary-600 hover:text-primary-800 mb-6 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
        </a>
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Create New Task</h1>
            <p class="text-gray-600">Fill out the form below to upload a new task</p>
        </div>
        
        <!-- Alerts -->
        <?php if ($success_message): ?>
            <div class="animate-fade-in mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="animate-fade-in mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Form -->
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow-md p-6">
            <!-- Task Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Task Title <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" name="title" 
                       value="<?php echo htmlspecialchars($title ?? ''); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm input-focus" 
                       placeholder="Enter task title..." required>
            </div>
            
            <!-- Semester and Course -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Semester -->
                <div>
                    <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">
                        Semester <span class="text-red-500">*</span>
                    </label>
                    <select id="semester" name="semester" required onchange="updateCourses()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm input-focus">
                        <option value="">Select Semester</option>
                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo ($semester ?? 0) == $i ? 'selected' : ''; ?>>
                                Semester <?php echo $i; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <!-- Course -->
                <div>
                    <label for="course" class="block text-sm font-medium text-gray-700 mb-1">
                        Mata Kuliah <span class="text-red-500">*</span>
                    </label>
                    <select id="course" name="course" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm input-focus">
                        <option value="">Select Course</option>
                        <?php if (isset($semester) && $semester > 0): ?>
                            <?php foreach ($courses_by_semester[$semester] as $course): ?>
                                <option value="<?php echo htmlspecialchars($course); ?>" <?php echo ($course ?? '') == $course ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            
            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea id="description" name="description" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm input-focus"
                          placeholder="Describe your task in detail..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            
            <!-- Price -->
            <div class="mb-6">
                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                    Price (Rp)
                </label>
                <div class="relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">Rp</span>
                    </div>
                    <input type="number" id="price" name="price" 
                           value="<?php echo htmlspecialchars($price ?? 0); ?>" 
                           class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-md input-focus" 
                           placeholder="0" min="0" step="1000">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">IDR</span>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Set to 0 if you want to share this task for free</p>
            </div>
            
            <!-- File Upload -->
            <div class="mb-8">
                <label for="task_file" class="block text-sm font-medium text-gray-700 mb-1">
                    Attach File (Optional)
                </label>
                <div class="mt-1 flex items-center">
                    <label for="task_file" class="cursor-pointer">
                        <span class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <i class="fas fa-paperclip mr-2"></i>
                            Choose File
                        </span>
                        <input id="task_file" name="task_file" type="file" class="sr-only" 
                               accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.gif">
                    </label>
                    <span id="file-name" class="ml-2 text-sm text-gray-500">No file chosen</span>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Allowed file types: PDF, DOC, DOCX, TXT, JPG, PNG, GIF (Max: 5MB)
                </p>
                
                <script>
                    document.getElementById('task_file').addEventListener('change', function(e) {
                        const fileName = e.target.files.length ? e.target.files[0].name : 'No file chosen';
                        document.getElementById('file-name').textContent = fileName;
                    });
                </script>
            </div>
            
            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="window.location.href='dashboard.php'" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Cancel
                </button>
                <button type="submit" 
                        class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Create Task
                </button>
            </div>
        </form>
    </div>
</body>
</html>