<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure employee is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch holidays for the current year
$current_year = date("Y");
$query = "SELECT * FROM holidays WHERE YEAR(holiday_date) = ? ORDER BY holiday_date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $current_year);
$stmt->execute();
$result = $stmt->get_result();
$holidays = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Holiday Calendar</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body id="container">
    <?php include('../includes/employee_sidebar.php'); ?>
    <div id="main-content" class="p-4">
        <div class="container">
            <h2 class="mb-4">Holiday Calendar - <?= $current_year ?></h2>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Holiday Name</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($holidays as $holiday): ?>
                            <tr>
                                <td><?= date("d M Y", strtotime($holiday['holiday_date'])) ?></td>
                                <td><?= $holiday['holiday_name'] ?></td>
                                <td><?= $holiday['description'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
