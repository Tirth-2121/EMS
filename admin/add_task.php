<?php
include '../config.php'; // Database connection
if (
    session_status() === PHP_SESSION_NONE
) {
    session_start();
}

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch all active employees
$query = "SELECT e.user_id, e.full_name, e.department 
          FROM employees e 
          WHERE e.status = 'Active' 
          ORDER BY e.full_name ASC";

$result = $conn->query($query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = trim($_POST['employee_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    // Validate input
    if (empty($employee_id) || empty($title) || empty($description)) {
        $error = "All fields are required.";
    } else {
        // Insert task
        $query = "INSERT INTO tasks (admin_id, employee_id, title, description) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiss", $admin_id, $employee_id, $title, $description);

        if ($stmt->execute()) {
            $success = "Task assigned successfully!";
            // Redirect after short delay
            header("Refresh: 1; URL=tasks.php");
        } else {
            $error = "Error assigning task: " . $conn->error;
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
    <title>Assign New Task</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
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
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">Assign New Task</h4>
                        </div>
                        <div class="card-body">

                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <?php if (!empty($success)): ?>
                                <div class="alert alert-success"><?= $success ?></div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="employee_id" class="form-label">Assign To Employee*</label>
                                    <select name="employee_id" id="employee_id" class="form-select" required>
                                        <option value="">-- Select Employee --</option>
                                        <?php while ($employee = $result->fetch_assoc()): ?>
                                            <option value="<?= $employee['user_id'] ?>">
                                                <?= htmlspecialchars($employee['full_name']) ?> (<?= htmlspecialchars($employee['department']) ?>)
                                            </option>

                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="title" class="form-label">Task Title*</label>
                                    <input type="text" name="title" id="title" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Task Description*</label>
                                    <textarea name="description" id="description" class="form-control" rows="5" required></textarea>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="tasks.php" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Assign Task</button>
                                </div>
                            </form>
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