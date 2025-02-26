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

// Fetch employee ID
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

// Handle Query Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $description = $_POST['description'];

    $query = "INSERT INTO queries (employee_id, subject, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $employee_id, $subject, $description);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Query submitted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error submitting query.</div>";
    }
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch Past Queries
$queries_query = "SELECT * FROM queries WHERE employee_id = ? ORDER BY submitted_at DESC";
$stmt = $conn->prepare($queries_query);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$queries_result = $stmt->get_result();
$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Query</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>

<body class="d-flex" id="container">
    <?php include('../includes/employee_sidebar.php'); ?>
    <div id="main-content" class="container">
        <h2 class="mb-4">Submit a Query</h2>
        <?= $message ?>
        <div class="card shadow p-4 mb-4">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Subject:</label>
                    <input type="text" name="subject" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description:</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit Query</button>
            </form>
        </div>

        <h3 class="mb-3">Past Queries</h3>
        <div class="card shadow p-4">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Subject</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $queries_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['subject']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><span class="badge bg-<?= $row['status'] == 'Approved' ? 'success' : ($row['status'] == 'Pending' ? 'warning' : 'danger') ?>">
                                    <?= $row['status'] ?>
                                </span></td>
                            <td><?= htmlspecialchars($row['submitted_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>