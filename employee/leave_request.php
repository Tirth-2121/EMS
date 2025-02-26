<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$employee_query = "SELECT id FROM employees WHERE user_id = ?";
$stmt = $conn->prepare($employee_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

if (!$employee) {
    die("Error: Employee record not found.");
}

$employee_id = $employee['id'];
$message = "";

// Handle Leave Request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $leave_type = $_POST['leave_type'];
    $reason = $_POST['reason'];

    if (strtotime($start_date) < strtotime(date("Y-m-d"))) {
        $message = "<div class='alert alert-danger'>Cannot apply leave for past dates.</div>";
    } else {
        $leave_days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;

        $query = "INSERT INTO leaves (employee_id, start_date, end_date, leave_days, leave_type, reason) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ississ", $employee_id, $start_date, $end_date, $leave_days, $leave_type, $reason);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Leave request submitted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error submitting leave request.</div>";
        }
        $stmt->close();
    }
}

// Fetch past leave requests
$leaves_query = "SELECT * FROM leaves WHERE employee_id = ? ORDER BY requested_at DESC";
$stmt = $conn->prepare($leaves_query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$leaves_result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Request Leave</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body id="container" class="d-flex">
    <?php include('../includes/employee_sidebar.php'); ?>
    <div id="main-content" class="container mt-4">
        <div class="card shadow p-4">
            <h2 class="mb-3">Leave Request</h2>
            <?= $message ?>
            <form method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type of Leave</label>
                    <select name="leave_type" class="form-select" required>
                        <option value="Casual Leave">Casual Leave</option>
                        <option value="Marriage Leave">Marriage Leave</option>
                        <option value="Compensatory">Compensatory</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Reason</label>
                    <textarea name="reason" class="form-control" rows="3" required></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Request Leave</button>
                </div>
            </form>
        </div>

        <div class="card shadow p-4 mt-4">
            <h3 class="mb-3">Past Leave Requests</h3>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Days</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $leaves_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['start_date'] ?></td>
                                <td><?= $row['end_date'] ?></td>
                                <td><?= $row['leave_days'] ?></td>
                                <td><?= $row['leave_type'] ?></td>
                                <td>
                                    <span class="badge bg-<?= $row['status'] == 'Approved' ? 'success' : ($row['status'] == 'Pending' ? 'warning' : 'danger') ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>