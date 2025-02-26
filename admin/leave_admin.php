<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = "";

// Handle Approve/Reject Leave
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $leave_id = $_POST['leave_id'];
    $status = $_POST['status'];

    $update_query = "UPDATE leaves SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $leave_id);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Leave status updated successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating leave status.</div>";
    }
    $stmt->close();
}

// Fetch pending leave requests
$leaves_query = "
    SELECT l.id, l.start_date, l.end_date, l.leave_days, l.leave_type, l.reason, 
           e.full_name, u.email, e.department
    FROM leaves l
    JOIN employees e ON l.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    WHERE l.status = 'Pending'
    ORDER BY l.requested_at DESC";
$result = $conn->query($leaves_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 30px;
        }

        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
        }

        .btn-group button {
            width: 100px;
        }

        #main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>

<body id="container">
    <?php include('../includes/admin_sidebar.php'); ?>
    <div class="container">
        <h2 class="text-center mb-4">Pending Leave Requests</h2>
        <?= $message ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Employee</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Leave Dates</th>
                        <th>Days</th>
                        <th>Type</th>
                        <th>Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['full_name'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['department'] ?></td>
                            <td><?= $row['start_date'] ?> to <?= $row['end_date'] ?></td>
                            <td><?= $row['leave_days'] ?></td>
                            <td><?= $row['leave_type'] ?></td>
                            <td><?= $row['reason'] ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="leave_id" value="<?= $row['id'] ?>">
                                    <div class="btn-group" role="group">
                                        <button type="submit" name="status" value="Approved" class="btn btn-success btn-sm">Approve</button>
                                        <button type="submit" name="status" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>