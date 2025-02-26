<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all tasks
$query = "SELECT t.id, t.title, t.description, t.status, e.full_name 
          FROM tasks t 
          JOIN employees e ON t.employee_id = e.user_id 
          ORDER BY t.id DESC";
$result = $conn->query($query);

// Delete Task
if (isset($_GET['delete'])) {
    $task_id = intval($_GET['delete']);
    $conn->query("DELETE FROM tasks WHERE id = $task_id");
    header("Location: tasks.php");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>

<body id="container">
    <?php include('../includes/admin_sidebar.php'); ?>
    <div id="main-content">
        <div class="container mt-4">
            <h3>Task List</h3>
            <a href="assign_task.php" class="btn btn-primary mb-3">Assign New Task</a>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Employee</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($task = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['full_name']) ?></td>
                            <td><?= htmlspecialchars($task['title']) ?></td>
                            <td><?= htmlspecialchars($task['description']) ?></td>
                            <td>
                                <?php if ($task['status'] == 'Completed') : ?>
                                    <span class="badge bg-success"><?= $task['status'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning"><?= $task['status'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="tasks.php?delete=<?= $task['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
<?php $conn->close(); ?>