<?php
include '../config.php'; // Database connection

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Employee ID.");
}

$employee_id = $_GET['id'];
$message = ''; // Initialize message variable

// Fetch employee details
$query = "SELECT e.*, u.username, u.email FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Employee not found.");
}

$employee = $result->fetch_assoc();

// Fetch departments for dropdown
$departments = ["Development", "QA", "Designer", "Sales"]; // Example departments

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $position = trim($_POST['position']);
    $department = trim($_POST['department']);
    $salary = floatval($_POST['salary']);
    $status = trim($_POST['status']);
    $password = trim($_POST['password']);

    // Update employee details
    $update_query = "UPDATE employees SET full_name = ?, position = ?, department = ?, salary = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssdsd", $full_name, $position, $department, $salary, $status, $employee_id);

    if ($stmt->execute()) {
        // Update password if provided
        if (!empty($password)) {
            $hashed_password = MD5($password);
            $update_password_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_password_query);
            $stmt->bind_param("si", $hashed_password, $employee['user_id']);
            $stmt->execute();
        }
        $message = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        Employee updated successfully!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
        header("refresh:2;url=employees.php");
    } else {
        $message = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                        Error updating employee!
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        #container {
            display: flex;
        }

        #main-content {
            flex-grow: 1;
            padding: 20px;
            position: relative;
        }

        .alert-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            min-width: 300px;
            max-width: 600px;
        }
    </style>
</head>

<body>
    <div id="container">
        <?php include('../includes/admin_sidebar.php'); ?>
        <div id="main-content">
            <?php if (!empty($message)): ?>
                <div class="alert-container">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="container mt-4">
                <h2>Edit Employee</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($employee['full_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control" value="<?= htmlspecialchars($employee['position']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-control" required>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept ?>" <?= ($employee['department'] == $dept) ? "selected" : "" ?>><?= $dept ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Salary</label>
                        <input type="number" step="0.01" name="salary" class="form-control" value="<?= htmlspecialchars($employee['salary']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="Active" <?= ($employee['status'] == "Active") ? "selected" : "" ?>>Active</option>
                            <option value="Inactive" <?= ($employee['status'] == "Inactive") ? "selected" : "" ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password (Leave blank to keep current password)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                    <a href="employees.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>