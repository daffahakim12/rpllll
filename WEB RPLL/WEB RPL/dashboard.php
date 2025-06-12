<?php
session_start();
include_once 'includes/functions.php';
requireLogin(); // Ensure user is logged in

// Fetch user data from the database
$conn = connectDB();
$user_id = $_SESSION['user_id'];

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Close statement
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LCIS</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation Sidebar -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 bg-indigo-700 text-white">
                <div class="flex items-center justify-center h-16 px-4 bg-indigo-800">
                    <span class="text-xl font-semibold">LCIS</span>
                </div>
                <div class="flex flex-col flex-grow px-4 py-4 overflow-y-auto">
                    <div class="flex items-center px-4 py-3 mt-2 rounded-lg bg-indigo-600">
                        <img class="w-8 h-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=random" alt="User avatar">
                        <span class="ml-3 font-medium"><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></span>
                    </div>
                    <nav class="mt-6">
                        <a href="dashboard.php" class="flex items-center px-4 py-3 mt-2 text-indigo-100 bg-indigo-600 rounded-lg">
                            <i class="fas fa-tachometer-alt"></i>
                            <span class="ml-3">Dashboard</span>
                        </a>
                        <a href="upload_task.php" class="flex items-center px-4 py-3 mt-2 text-indigo-200 hover:bg-indigo-600 hover:text-indigo-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-upload"></i>
                            <span class="ml-3">Upload Task</span>
                        </a>
                        <a href="search_task.php" class="flex items-center px-4 py-3 mt-2 text-indigo-200 hover:bg-indigo-600 hover:text-indigo-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-search"></i>
                            <span class="ml-3">Search Tasks</span>
                        </a>
                        <a href="mentor/mentor_application.php" class="flex items-center px-4 py-3 mt-2 text-indigo-200 hover:bg-indigo-600 hover:text-indigo-100 rounded-lg transition-colors duration-200">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span class="ml-3">Apply as Mentor</span>
                        </a>
                    </nav>
                </div>
                <div class="p-4 border-t border-indigo-800">
                    <a href="logout.php" class="flex items-center px-4 py-2 text-indigo-200 hover:bg-indigo-600 hover:text-indigo-100 rounded-lg transition-colors duration-200" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="ml-3">Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Mobile Header -->
            <header class="md:hidden bg-white shadow">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center">
                        <button id="mobile-menu-button" class="text-gray-500 focus:outline-none">
                            <i class="fas fa-bars"></i>
                        </button>
                        <span class="ml-3 text-lg font-semibold">LCIS</span>
                    </div>
                    <div class="flex items-center">
                        <img class="w-8 h-8 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=random" alt="User avatar">
                    </div>
                </div>
            </header>

            <!-- Mobile Sidebar (hidden by default) -->
            <div id="mobile-menu" class="hidden md:hidden bg-indigo-700 text-white">
                <nav class="px-2 py-4">
                    <a href="dashboard.php" class="flex items-center px-4 py-3 text-indigo-100 bg-indigo-600 rounded-lg">
                        <i class="fas fa-tachometer-alt"></i>
                        <span class="ml-3">Dashboard</span>
                    </a>
                    <a href="upload_task.php" class="flex items-center px-4 py-3 mt-2 text-indigo-200 hover:bg-indigo-600 hover:text-indigo-100 rounded-lg">
                        <i class="fas fa-upload"></i>
                        <span class="ml-3">Upload Task</span>
                    </a>
                    <a href="search_task.php" class="flex items-center px-4 py-3 mt-2 text-indigo-200 hover:bg-indigo-600 hover:text-indigo-100 rounded-lg">
                        <i class="fas fa-search"></i>
                        <span class="ml-3">Search Tasks</span>
                    </a>
                    <a href="mentor/mentor_application.php" class="flex items-center px-4 py-3 mt-2 text-indigo-200 hover:bg-indigo-600 hover:text-indigo-100 rounded-lg">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span class="ml-3">Apply as Mentor</span>
                    </a>
                    <a href="logout.php" class="flex items-center px-4 py-3 mt-2 text-indigo-200 hover:bg-indigo-600 hover:text-indigo-100 rounded-lg" onclick="return confirm('Are you sure you want to logout?')">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="ml-3">Logout</span>
                    </a>
                </nav>
            </div>

            <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="max-w-7xl mx-auto">
                    <div class="flex justify-between items-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-800">Welcome back, <?php echo htmlspecialchars($user['username'] ?? 'User'); ?></h1>
                    </div>

                    <!-- Dashboard Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        <!-- Task Card -->
                        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 card-hover">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                                        <i class="fas fa-tasks text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Your Tasks</h3>
                                        <p class="text-gray-500">Manage your uploaded tasks</p>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <a href="upload_task.php" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
                                        <i class="fas fa-plus mr-2"></i>
                                        Upload New Task
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Search Card -->
                        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 card-hover">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                        <i class="fas fa-search text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Find Tasks</h3>
                                        <p class="text-gray-500">Browse tasks from other students</p>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <a href="search_task.php" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                        <i class="fas fa-search mr-2"></i>
                                        Search Tasks
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Mentor Card -->
                        <div class="bg-white rounded-xl shadow-md overflow-hidden transition-all duration-300 card-hover">
                            <div class="p-6">
                                <div class="flex items-center">
                                    <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                                        <i class="fas fa-chalkboard-teacher text-xl"></i>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Mentor Program</h3>
                                        <p class="text-gray-500">Share your knowledge</p>
                                    </div>
                                </div>
                                <div class="mt-6">
                                    <a href="mentor/mentor_application.php" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200">
                                        <i class="fas fa-edit mr-2"></i>
                                        Apply as Mentor
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dashboard Content -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-8">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your Dashboard</h2>
                        <p class="text-gray-600 mb-6">Manage your tasks, mentor applications, and more!</p>
                        
                        <!-- Recent Activity Section -->
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 pt-1">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <i class="fas fa-bell text-indigo-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">Welcome to LCIS Dashboard</p>
                                        <p class="text-sm text-gray-500">You're all set up and ready to go!</p>
                                        <p class="text-xs text-gray-400 mt-1">Just now</p>
                                    </div>
                                </div>
                            </div>
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
                if (!confirm('Are you sure you want to logout?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>