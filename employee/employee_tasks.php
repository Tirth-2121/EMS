<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit();
}

$employee_id = $_SESSION['user_id'];

// Handle Marking Task as Completed
if (isset($_GET['complete'])) {
    $task_id = intval($_GET['complete']);
    $conn->query("UPDATE tasks SET status='Completed' WHERE id=$task_id AND employee_id=$employee_id");
    header("Location: employee_tasks.php");
}

// Check if viewing completed tasks
$show_completed = isset($_GET['view_completed']) ? true : false;

// Fetch tasks for this employee
$query = "SELECT id, title, description, status FROM tasks WHERE employee_id = $employee_id";
$query .= $show_completed ? " AND status='Completed'" : " AND status!='Completed'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>

<body id="container">
    <?php include('../includes/employee_sidebar.php'); ?>
    <div id="main-content">
        <div class="container mt-4">
            <h3>My Tasks</h3>

            <div class="d-flex justify-content-between mb-3">
                <h5><?= $show_completed ? "Completed Tasks" : "Pending Tasks" ?></h5>
                <a href="employee_tasks.php?<?= $show_completed ? "" : "view_completed" ?>" class="btn btn-<?= $show_completed ? "secondary" : "success" ?>">
                    <?= $show_completed ? "Back to Pending Tasks" : "View Completed Tasks" ?>
                </a>
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <?php if (!$show_completed): ?>
                            <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($task = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['title']) ?></td>
                            <td><?= htmlspecialchars($task['description']) ?></td>
                            <?php if (!$show_completed): ?>
                                <td>
                                    <a href="employee_tasks.php?complete=<?= $task['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Mark this task as completed?')">
                                        <div class="fas fa-check"></div>
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>

                    <?php if ($result->num_rows === 0): ?>
                        <tr>
                            <td colspan="3" class="text-center">No <?= $show_completed ? "completed" : "pending" ?> tasks found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
<?php $conn->close(); ?>