<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch admin details
$query = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

// Handle Password Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = md5($_POST['new_password']);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $admin_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Password updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update password.";
    }

    $stmt->close();
    $conn->close();
    header("Location: admin_profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
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
                    <input type="text" class="form-control" value="<?= htmlspecialchars($admin['username']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label>Email:</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($admin['email']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label>New Password:</label>
                    <input type="password" name="new_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Update Password</button>
            </form>
        </div>
    </div>
</body>

</html>