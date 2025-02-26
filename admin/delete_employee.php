<?php
include '../config.php'; // Database connection

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid Employee ID.");
}

$employee_id = $_GET['id'];

// Fetch the user_id before deletion
$query = "SELECT user_id FROM employees WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Employee not found.");
}

$employee = $result->fetch_assoc();
$user_id = $employee['user_id'];

// Start transaction to ensure both records are deleted together
$conn->begin_transaction();

try {
    // Delete from employees table
    $delete_employee = "DELETE FROM employees WHERE id = ?";
    $stmt = $conn->prepare($delete_employee);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();

    // Delete from users table
    $delete_user = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_user);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Commit the transaction
    $conn->commit();
    echo "<script>alert('Employee deleted successfully!'); window.location.href='employees.php';</script>";
} catch (Exception $e) {
    $conn->rollback();
    die("Error deleting employee: " . $e->getMessage());
}

$conn->close();
