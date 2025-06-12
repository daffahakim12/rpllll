<?php
session_start();
include_once 'includes/functions.php';
include_once 'includes/db_connect.php';
requireLogin();
$user_id = $_SESSION['user_id'];

// Handle search
$search_query = '';
$search_results = [];
$recent_tasks = [];

// Get recent tasks from all students (limit to 12)
$stmt = $conn->prepare("SELECT t.*, u.username FROM tasks t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 12");
$stmt->execute();
$recent_tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (isset($_POST['search']) && !empty($_POST['search_query'])) {
    $search_query = $_POST['search_query'];
    
    // Search tasks from all students
    $stmt = $conn->prepare("SELECT t.*, u.username FROM tasks t JOIN users u ON t.user_id = u.id WHERE (t.title LIKE ? OR t.description LIKE ?) ORDER BY t.created_at DESC");
    $search_param = "%$search_query%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $search_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Tasks - LCIS</title>
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
            .task-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            }
            .price-tag {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-md h-screen sticky top-0">
            <div class="p-4 border-b border-gray-200">
                <h1 class="text-xl font-bold text-gray-800">LCIS</h1>
            </div>
            <nav class="p-4">
                <div class="mb-6">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Welcome back, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></p>
                </div>
                
                <ul class="space-y-2">
                    <li>
                        <a href="dashboard.php" class="flex items-center p-2 text-gray-600 hover:text-primary-600 hover:bg-gray-100 rounded-md transition-colors">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            <span>Your Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="your_tasks.php" class="flex items-center p-2 text-gray-600 hover:text-primary-600 hover:bg-gray-100 rounded-md transition-colors">
                            <i class="fas fa-tasks mr-3"></i>
                            <span>Your Tasks</span>
                        </a>
                    </li>
                    <li>
                        <a href="upload_task.php" class="flex items-center p-2 text-gray-600 hover:text-primary-600 hover:bg-gray-100 rounded-md transition-colors">
                            <i class="fas fa-plus-circle mr-3"></i>
                            <span>Upload New Task</span>
                        </a>
                    </li>
                    <li>
                        <a href="search_tasks.php" class="flex items-center p-2 text-primary-600 bg-primary-50 rounded-md">
                            <i class="fas fa-search mr-3"></i>
                            <span>Search Tasks</span>
                        </a>
                    </li>
                    <li>
                        <a href="mentor_application.php" class="flex items-center p-2 text-gray-600 hover:text-primary-600 hover:bg-gray-100 rounded-md transition-colors">
                            <i class="fas fa-user-graduate mr-3"></i>
                            <span>Apply as Mentor</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Search Tasks</h1>
                <p class="text-gray-600">Find tasks from all students</p>
            </div>

            <!-- Search Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <form method="POST" class="flex gap-4">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search_query" 
                               class="block w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md input-focus" 
                               placeholder="Search tasks by title, description, or student..." 
                               value="<?php echo htmlspecialchars($search_query); ?>">
                    </div>
                    <button type="submit" name="search" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <i class="fas fa-search mr-2"></i>
                        Search
                    </button>
                </form>
            </div>

            <!-- Content Section -->
            <div class="space-y-8">
                <?php if (isset($_POST['search'])): ?>
                    <!-- Search Results -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-800">Search Results</h2>
                            <p class="text-sm text-gray-600">Showing results for "<?php echo htmlspecialchars($search_query); ?>"</p>
                        </div>

                        <?php if (empty($search_results)): ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-700 mb-1">No tasks found</h3>
                                <p class="text-gray-500">Try different search terms</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                                <?php foreach ($search_results as $task): ?>
                                    <div class="task-card bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
                                        <div class="p-6">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-gray-500">
                                                    <?php echo htmlspecialchars($task['username']); ?>
                                                </span>
                                                <span class="text-xs font-semibold px-2 py-1 rounded-full <?php echo ($task['status'] == 'completed') ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                    <?php echo ucfirst($task['status'] ?? 'pending'); ?>
                                                </span>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-800 mb-2">
                                                <?php echo htmlspecialchars($task['title'] ?? 'Untitled Task'); ?>
                                            </h3>
                                            <p class="text-gray-600 text-sm mb-4">
                                                <?php echo htmlspecialchars(substr($task['description'] ?? '', 0, 100)); ?>
                                                <?php if (strlen($task['description'] ?? '') > 100): ?>...<?php endif; ?>
                                            </p>
                                            <div class="flex items-center justify-between">
                                                <div class="text-sm text-gray-500">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    <?php echo date('M j, Y', strtotime($task['created_at'] ?? 'now')); ?>
                                                </div>
                                                <div class="flex items-center">
                                                    <?php if ($task['price'] > 0): ?>
                                                        <span class="price-tag text-xs font-bold text-white px-2 py-1 rounded-full mr-2">
                                                            Rp<?php echo number_format($task['price'], 0, ',', '.'); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <a href="task_details.php?id=<?php echo $task['id']; ?>" class="text-sm font-medium text-primary-600 hover:text-primary-800">
                                                        View →
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Recent Tasks from All Students -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-800">Recent Tasks from Students</h2>
                            <p class="text-sm text-gray-600">Recently uploaded tasks from all students</p>
                        </div>

                        <?php if (empty($recent_tasks)): ?>
                            <div class="p-8 text-center">
                                <i class="fas fa-tasks text-4xl text-gray-300 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-700 mb-1">No tasks uploaded yet</h3>
                                <p class="text-gray-500">Be the first to upload a task</p>
                                <a href="upload_task.php" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    Upload Task
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                                <?php foreach ($recent_tasks as $task): ?>
                                    <div class="task-card bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-all duration-200">
                                        <div class="p-6">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-semibold text-gray-500">
                                                    <?php echo htmlspecialchars($task['username']); ?>
                                                </span>
                                                <span class="text-xs font-semibold px-2 py-1 rounded-full <?php echo ($task['status'] == 'completed') ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                    <?php echo ucfirst($task['status'] ?? 'pending'); ?>
                                                </span>
                                            </div>
                                            <h3 class="text-lg font-medium text-gray-800 mb-2">
                                                <?php echo htmlspecialchars($task['title'] ?? 'Untitled Task'); ?>
                                            </h3>
                                            <p class="text-gray-600 text-sm mb-4">
                                                <?php echo htmlspecialchars(substr($task['description'] ?? '', 0, 100)); ?>
                                                <?php if (strlen($task['description'] ?? '') > 100): ?>...<?php endif; ?>
                                            </p>
                                            <div class="flex items-center justify-between">
                                                <div class="text-sm text-gray-500">
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    <?php echo date('M j, Y', strtotime($task['created_at'] ?? 'now')); ?>
                                                </div>
                                                <div class="flex items-center">
                                                    <?php if ($task['price'] > 0): ?>
                                                        <span class="price-tag text-xs font-bold text-white px-2 py-1 rounded-full mr-2">
                                                            Rp<?php echo number_format($task['price'], 0, ',', '.'); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <a href="task_details.php?id=<?php echo $task['id']; ?>" class="text-sm font-medium text-primary-600 hover:text-primary-800">
                                                        View →
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 text-right">
                                <a href="search_tasks.php?show_all=1" class="text-sm font-medium text-primary-600 hover:text-primary-800">
                                    View all tasks <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>