<?php
include '../config.php'; // Database connection
if (session_status() === PHP_SESSION_NONE
) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.phsp");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Check if task ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: tasks.php");
    exit();
}

$task_id = (int)$_GET['id'];

// Fetch the task details with employee info
$query = "SELECT t.*, e.full_name as employee_name, e.department, e.position 
          FROM tasks t 
          JOIN employees e ON t.employee_id = e.id 
          WHERE t.id = ? AND t.admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $task_id, $admin_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if task exists and belongs to the admin
if ($result->num_rows === 0) {
    header("Location: tasks.php");
    exit();
}

$task = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Task Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .task-details p {
            margin-bottom: 0.5rem;
        }

        .task-description {
            white-space: pre-line;
        }
    </style>
</head>

<body id='container'>
    <?php include('../includes/admin_sidebar.php'); ?>
    <div id="main-content">
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Task Details</h4>
                            <div>
                                <a href="tasks.php" class="btn btn-sm btn-light">Back to List</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h5 class="border-bottom pb-2"><?= htmlspecialchars($task['title']) ?></h5>
                                    <div class="task-details">
                                        <p><strong>Status:</strong>
                                            <span class="badge <?= $task['status'] === 'Completed' ? 'bg-success' : 'bg-warning' ?>">
                                                <?= $task['status'] ?>
                                            </span>
                                        </p>
                                        <p><strong>Created:</strong> <?= date('F d, Y h:i A', strtotime($task['created_at'])) ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <h6 class="text-muted">Assigned To</h6>
                                    <div class="task-details">
                                        <p><strong>Employee:</strong> <?= htmlspecialchars($task['employee_name']) ?></p>
                                        <p><strong>Department:</strong> <?= htmlspecialchars($task['department']) ?></p>
                                        <p><strong>Position:</strong> <?= htmlspecialchars($task['position']) ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="text-muted">Task Description</h6>
                                    <div class="card">
                                        <div class="card-body task-description">
                                            <?= htmlspecialchars($task['description']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end">
                                <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-primary me-2">Edit Task</a>
                                <a href="delete_task.php?id=<?= $task['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this task?')">Delete Task</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>