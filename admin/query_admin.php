<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";
$show_past_queries = isset($_GET['show_past']) && $_GET['show_past'] == 1;

// Handle Query Approval/Rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $query_id = $_POST['query_id'];
    $status = $_POST['status'];

    $update_query = "UPDATE queries SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $status, $query_id);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Query status updated successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error updating query status.</div>";
    }
    $stmt->close();
}

// Fetch Queries Based on Filter
$query_condition = $show_past_queries ? "q.status != 'Pending'" : "q.status IN ('Pending', 'Hold')";


$queries_query = "
    SELECT q.id, q.subject, q.description, q.status, q.submitted_at,
           e.full_name, u.email, e.department
    FROM queries q
    JOIN employees e ON q.employee_id = e.id
    JOIN users u ON e.user_id = u.id
    WHERE $query_condition
    ORDER BY q.submitted_at DESC";
$result = $conn->query($queries_query);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Query Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h2 class="text-center mb-4"><?= $show_past_queries ? "Past Queries" : "Pending Queries" ?></h2>
            <?= $message ?>

            <div class="text-end mb-3">
                <a href="query_admin.php?show_past=<?= $show_past_queries ? '0' : '1' ?>" class="btn btn-primary">
                    <?= $show_past_queries ? "View Pending Queries" : "View Past Queries" ?>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['full_name'] ?></td>
                                <td><?= $row['email'] ?></td>
                                <td><?= $row['department'] ?></td>
                                <td><?= $row['subject'] ?></td>
                                <td><?= $row['description'] ?></td>
                                <td><?= $row['submitted_at'] ?></td>
                                <td><span class="badge bg-<?= $row['status'] == 'Pending' ? 'warning' : ($row['status'] == 'Approved' ? 'success' : 'danger') ?>">
                                        <?= $row['status'] ?></span></td>
                                <td>
                                    <?php if (!$show_past_queries): ?>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="query_id" value="<?= $row['id'] ?>">
                                            <button type="submit" name="status" value="Approved" class="btn btn-success btn-sm">Approve</button>
                                            <button type="submit" name="status" value="Hold" class="btn btn-info btn-sm">Hold</button>
                                            <button type="submit" name="status" value="Rejected" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">No action required</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>