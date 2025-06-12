<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../config/database.php';

requireAdmin();

$database = new Database();
$db = $database->getConnection();
$user = getUserInfo();

// Get statistics
$stats = [];

// Total users
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'mahasiswa'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pending tasks
$query = "SELECT COUNT(*) as total FROM tasks WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_tasks'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Pending mentor applications
$query = "SELECT COUNT(*) as total FROM mentor_applications WHERE status = 'pending'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['pending_mentors'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total revenue (simplified)
$query = "SELECT SUM(amount) as total FROM task_purchases WHERE payment_status = 'completed'";
$stmt = $db->prepare($query);
$stmt->execute();
$stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;

// Recent activities
$query = "SELECT al.*, u.full_name 
          FROM admin_logs al 
          JOIN users u ON al.admin_id = u.id 
          ORDER BY al.created_at DESC LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pending tasks for review
$query = "SELECT t.*, u.full_name as uploader_name, c.name as category_name 
          FROM tasks t 
          JOIN users u ON t.user_id = u.id 
          JOIN categories c ON t.category_id = c.id 
          WHERE t.status = 'pending' 
          ORDER BY t.created_at DESC LIMIT 5";
$stmt = $db->prepare($query);
$stmt->execute();
$pending_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LCIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-graduation-cap"></i> LCIS Admin
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user"></i> <?php echo $user['full_name']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../dashboard.php">User Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">
                                <i class="fas fa-users"></i> Kelola Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="review_tasks.php">
                                <i class="fas fa-tasks"></i> Review Tugas
                                <?php if ($stats['pending_tasks'] > 0): ?>
                                    <span class="badge bg-warning"><?php echo $stats['pending_tasks']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="review_mentors.php">
                                <i class="fas fa-chalkboard-teacher"></i> Review Mentor
                                <?php if ($stats['pending_mentors'] > 0): ?>
                                    <span class="badge bg-warning"><?php echo $stats['pending_mentors']; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_payments.php">
                                <i class="fas fa-credit-card"></i> Kelola Pembayaran
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-line"></i> Laporan
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Admin Dashboard</h1>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-users fa-2x me-3"></i>
                                    <div>
                                        <h5 class="card-title"><?php echo $stats['total_users']; ?></h5>
                                        <p class="card-text">Total Users</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock fa-2x me-3"></i>
                                    <div>
                                        <h5 class="card-title"><?php echo $stats['pending_tasks']; ?></h5>
                                        <p class="card-text">Tugas Pending</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-chalkboard-teacher fa-2x me-3"></i>
                                    <div>
                                        <h5 class="card-title"><?php echo $stats['pending_mentors']; ?></h5>
                                        <p class="card-text">Aplikasi Mentor</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill fa-2x me-3"></i>
                                    <div>
                                        <h5 class="card-title">Rp <?php echo formatPrice($stats['total_revenue']); ?></h5>
                                        <p class="card-text">Total Revenue</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Pending Tasks -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-tasks"></i> Tugas Perlu Review</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($pending_tasks)): ?>
                                    <p class="text-muted">Tidak ada tugas yang perlu direview.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Judul</th>
                                                    <th>Uploader</th>
                                                    <th>Kategori</th>
                                                    <th>Tanggal</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pending_tasks as $task): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                                                    <td><?php echo $task['uploader_name']; ?></td>
                                                    <td><?php echo $task['category_name']; ?></td>
                                                    <td><?php echo formatDate($task['created_at']); ?></td>
                                                    <td>
                                                        <a href="task_review.php?id=<?php echo $task['id']; ?>" class="btn btn-sm btn-primary">
                                                            Review
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="review_tasks.php" class="btn btn-outline-primary">Lihat Semua</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-history"></i> Aktivitas Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_activities)): ?>
                                    <p class="text-muted">Belum ada aktivitas.</p>
                                <?php else: ?>
                                    <?php foreach ($recent_activities as $activity): ?>
                                    <div class="mb-3 pb-3 border-bottom">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($activity['action']); ?></h6>
                                        <p class="text-muted small mb-1"><?php echo htmlspecialchars($activity['description']); ?></p>
                                        <small class="text-muted">
                                            <?php echo $activity['full_name']; ?> - <?php echo formatDate($activity['created_at']); ?>
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>