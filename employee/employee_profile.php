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

// Fetch employee details
$query = "SELECT u.username, u.email, e.full_name, e.position, e.department, e.join_date 
          FROM users u 
          JOIN employees e ON u.id = e.user_id 
          WHERE u.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $password = !empty($_POST['password']) ? md5($_POST['password']) : null;

    if ($password) {
        $updateQuery = "UPDATE employees e, users u SET e.full_name = ?, u.password = ? WHERE u.id = ? AND e.user_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssii", $full_name, $password, $employee_id, $employee_id);
    } else {
        $updateQuery = "UPDATE employees SET full_name = ? WHERE user_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $full_name, $employee_id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully!";
        header("Location: employee_profile.php");
        exit();
    } else {
        $_SESSION['error'] = "Failed to update profile.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Profile</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
            <h3>Manage Profile</h3>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'];
                                                    unset($_SESSION['message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'];
                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label>Username:</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($employee['username']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label>Email:</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($employee['email']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label>Full Name:</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($employee['full_name']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Position:</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($employee['position']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label>Department:</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($employee['department']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label>Joining Date:</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($employee['join_date']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label>New Password (Leave blank if not changing):</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <button type="submit" class="btn btn-success">Update Profile</button>
            </form>
        </div>
    </div>
</body>

</html>