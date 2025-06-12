<?php
session_start();
include_once 'includes/functions.php';
include_once 'includes/db_connect.php';
requireLogin();
$user_id = $_SESSION['user_id'];

// Get task ID from URL
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch task details
$task = null;
if ($task_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
    $stmt->close();
}

// Handle task update
if (isset($_POST['update_task']) && $task) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    
    $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $title, $description, $status, $task_id, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Task updated successfully!";
        // Refresh task data
        $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);
        $stmt->execute();
        $task = $stmt->get_result()->fetch_assoc();
    } else {
        $error_message = "Error updating task.";
    }
    $stmt->close();
}

// Handle task deletion
if (isset($_POST['delete_task']) && $task) {
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?deleted=1");
        exit();
    } else {
        $error_message = "Error deleting task.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Details - LCIS</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .task-container { max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group textarea, .form-group select { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; 
        }
        .form-group textarea { height: 120px; resize: vertical; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .task-meta { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #007bff; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="task-container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <?php if (!$task): ?>
            <div class="alert alert-danger">Task not found or you don't have permission to view it.</div>
        <?php else: ?>
            <h1>Task Details</h1>
            
            <div class="task-meta">
                <strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($task['created_at'] ?? 'now')); ?><br>
                <strong>Last Updated:</strong> <?php echo date('M j, Y g:i A', strtotime($task['updated_at'] ?? 'now')); ?><br>
                <strong>Current Status:</strong> <?php echo ucfirst($task['status'] ?? 'pending'); ?>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="title">Task Title:</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($task['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status">Status:</label>
                    <select id="status" name="status">
                        <option value="pending" <?php echo ($task['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in_progress" <?php echo ($task['status'] ?? '') == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo ($task['status'] ?? '') == 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($task['status'] ?? '') == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" name="update_task" class="btn btn-primary">Update Task</button>
                    <button type="button" class="btn btn-secondary" onclick="window.location.href='dashboard.php'">Cancel</button>
                    <button type="submit" name="delete_task" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to delete this task? This action cannot be undone.')">Delete Task</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>