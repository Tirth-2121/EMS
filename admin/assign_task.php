<?php

include '../config.php'; // Database connection
if (session_status() === PHP_SESSION_NONE) {
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

// Fetch all active employees with their emails from the users table
$query = "SELECT e.user_id, e.full_name, e.department, u.email 
          FROM employees e 
          JOIN users u ON e.user_id = u.id
          WHERE e.status = 'Active' 
          ORDER BY e.full_name ASC";
$result = $conn->query($query);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = trim($_POST['employee_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($employee_id) || empty($title) || empty($description)) {
        $error = "All fields are required.";
    } else {
        $query = "INSERT INTO tasks (admin_id, employee_id, title, description, status) 
                  VALUES (?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiss", $admin_id, $employee_id, $title, $description);

        if ($stmt->execute()) {
            $success = "Task assigned successfully!";
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/toast.css">
    <script src="../assets/toast.js"></script>
    <style>
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 5px;
        }
    </style>
</head>

<body id="container">
    <?php include('../includes/admin_sidebar.php'); ?>
    <div id="main-content">
        <div class="container mt-4">
            <h3>Assign New Task</h3>
            <?php //if ($error) echo "<div class='alert alert-danger'>$error</div>"; 
            ?>
            <?php //if ($success) echo "<div class='alert alert-success'>$success</div>"; 
            ?>
            <?php if ($success): ?>
                <script>
                    showToast("<?= $success ?>", "success");
                </script>
            <?php endif; ?>

            <?php if ($error): ?>
                <script>
                    showToast("<?= $error ?>", "error");
                </script>
            <?php endif; ?>


            <form method="POST" action="">
                <div class="mb-3">
                    <label for="employee_id" class="form-label">Assign To Employee*</label>
                    <select name="employee_id" id="employee_id" class="form-select" required>
                        <option value="">-- Select Employee --</option>
                        <?php while ($employee = $result->fetch_assoc()): ?>
                            <option value="<?= $employee['user_id'] ?>">
                                <?= htmlspecialchars($employee['full_name']) ?>
                                (<?= htmlspecialchars($employee['department']) ?>) -
                                <?= htmlspecialchars($employee['email']) ?>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#employee_id').select2({
                placeholder: "Search for an employee...",
                allowClear: true
            });
        });
    </script>
</body>

</html>

<?php $conn->close(); ?>