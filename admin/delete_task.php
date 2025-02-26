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

// Check if task ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: tasks.php");
    exit();
}

$task_id = (int)$_GET['id'];

// Delete task if it belongs to the admin
$query = "DELETE FROM tasks WHERE id = ? AND admin_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $task_id, $admin_id);
$stmt->execute();

// Check if any row was affected
if ($stmt->affected_rows > 0) {
    // Success message using session
    $_SESSION['success_msg'] = "Task deleted successfully.";
} else {
    // Error message using session
    $_SESSION['error_msg'] = "Error deleting task or task not found.";
}

// Redirect back to tasks page
header("Location: tasks.php");
exit();
