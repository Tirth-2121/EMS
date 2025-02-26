<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../auth/login.php");
    exit();
}

// Check if employee_id is set
if (!isset($_SESSION['employee_id'])) {
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    exit();
    die("Error: Employee ID is not set in the session. Please log in again.");
}

include '../config.php';

$employee_id = $_SESSION['employee_id'];  // Ensure this is set

// Fetch random quote
$quotes = [
    "The only way to do great work is to love what you do.",
    "Success is not final, failure is not fatal: It is the courage to continue that counts.",
    "Do what you can, with what you have, where you are.",
    "Act as if what you do makes a difference. It does.",
    "Happiness depends upon ourselves."
];
$random_quote = $quotes[array_rand($quotes)];

// Fetch upcoming 5 holidays
$holidays = [];
$query = "SELECT holiday_name, holiday_date FROM holidays WHERE holiday_date >= CURDATE() ORDER BY holiday_date ASC LIMIT 5";
$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $holidays[] = $row;
}

// Fetch attendance statistics for current month
$attendanceQuery = $conn->prepare("
    SELECT 
        TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(punch_out, punch_in)))), '%H:%i:%s') AS avg_work_hours, 
        TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(punch_in))), '%H:%i:%s') AS avg_punch_in, 
        TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(punch_out))), '%H:%i:%s') AS avg_punch_out 
    FROM attendance 
    WHERE employee_id = ? AND MONTH(punch_in) = MONTH(CURRENT_DATE())
");
if (!$attendanceQuery) {
    die("Query preparation failed: " . $conn->error);
}
$attendanceQuery->bind_param("i", $employee_id);
$attendanceQuery->execute();
$attendanceResult = $attendanceQuery->get_result();
$attendanceStats = $attendanceResult->fetch_assoc();

$avg_work_hours = $attendanceStats['avg_work_hours'] ?? 'N/A';
$avg_punch_in = $attendanceStats['avg_punch_in'] ?? 'N/A';
$avg_punch_out = $attendanceStats['avg_punch_out'] ?? 'N/A';

$queryStatsQuery = $conn->prepare("
    SELECT 
        status,
        COUNT(*) as count
    FROM queries 
    WHERE employee_id = ? 
    GROUP BY status
");

if (!$queryStatsQuery) {
    die("Query preparation failed: " . $conn->error);
}

$queryStatsQuery->bind_param("i", $employee_id);
$queryStatsQuery->execute();
$queryStatsResult = $queryStatsQuery->get_result();

$statusLabels = [];
$statusCounts = [];
$totalQueries = 0;

while ($row = $queryStatsResult->fetch_assoc()) {
    $statusLabels[] = $row['status'];
    $statusCounts[] = $row['count'];
    $totalQueries += $row['count'];
}

// Convert to JSON for JavaScript
$statusLabelsJSON = json_encode($statusLabels);
$statusCountsJSON = json_encode($statusCounts);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

            <!-- Quote -->
            <div class="alert alert-info text-center">
                <h5><?php echo htmlspecialchars($random_quote); ?></h5>
            </div>

            <!-- Work Statistics -->
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white text-center">
                        <div class="card-body">
                            <h4>Avg Work Hours</h4>
                            <h2><?php echo htmlspecialchars($avg_work_hours); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body">
                            <h4>Avg Punch In</h4>
                            <h2><?php echo htmlspecialchars($avg_punch_in); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-white text-center">
                        <div class="card-body">
                            <h4>Avg Punch Out</h4>
                            <h2><?php echo htmlspecialchars($avg_punch_out); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Holidays -->
            <div class="mt-4">
                <h4>Upcoming Holidays</h4>
                <ul class="list-group">
                    <?php if (count($holidays) > 0): ?>
                        <?php foreach ($holidays as $holiday): ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <span><?php echo htmlspecialchars($holiday['holiday_name']); ?></span>
                                <span><?php echo date("d M, Y", strtotime($holiday['holiday_date'])); ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">No upcoming holidays</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>