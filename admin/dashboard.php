<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config.php';

$todaysDate = date('Y-m-d');

// Total Employees
$totalEmployeesQuery = $conn->query("SELECT COUNT(*) as total FROM employees");
$totalEmployees = $totalEmployeesQuery->fetch_assoc()['total'];

// Pending Leaves
$pendingLeavesQuery = $conn->query("SELECT COUNT(*) as total FROM leaves WHERE status = 'Pending'");
$pendingLeaves = $pendingLeavesQuery->fetch_assoc()['total'];

// Pending Queries
$pendingQueriesQuery = $conn->query("SELECT COUNT(*) as total FROM queries WHERE status = 'Pending'");
$pendingQueries = $pendingQueriesQuery->fetch_assoc()['total'];

// Today's Attendance
$attendanceQuery = $conn->prepare("
    SELECT COUNT(DISTINCT employee_id) as present 
    FROM attendance 
    WHERE DATE(punch_in) = ?
");
$attendanceQuery->bind_param("s", $todaysDate);
$attendanceQuery->execute();
$attendanceResult = $attendanceQuery->get_result();
$presentCount = $attendanceResult->fetch_assoc()['present'] ?? 0;

// Absent Count = Total Employees - Present Employees
$absentCount = $totalEmployees - $presentCount;

// Leave Statistics
$leaveQuery = $conn->query("
    SELECT 
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected
    FROM leaves
");
$leaveStats = $leaveQuery->fetch_assoc();

$approvedLeaves = $leaveStats['approved'] ?? 0;
$pendingLeavesChart = $leaveStats['pending'] ?? 0;
$rejectedLeaves = $leaveStats['rejected'] ?? 0;

// Query Statistics
$queryStatsQuery = $conn->query("
    SELECT 
        status,
        COUNT(*) as count 
    FROM queries 
    GROUP BY status
    ORDER BY FIELD(status, 'Pending', 'Approved', 'Hold', 'Rejected')
");

// Initialize array with all possible statuses set to 0
$queryStats = [
    'Pending' => 0,
    'Approved' => 0,
    'Hold' => 0,
    'Rejected' => 0
];

// Fill in actual counts
while ($row = $queryStatsQuery->fetch_assoc()) {
    $queryStats[$row['status']] = $row['count'];
}

// Prepare arrays for Chart.js
$queryLabels = array_keys($queryStats);
$queryCounts = array_values($queryStats);

// Convert to JSON for JavaScript
$queryLabelsJSON = json_encode($queryLabels);
$queryCountsJSON = json_encode($queryCounts);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .chart-container {
            height: 400px;
            margin-bottom: 20px;
        }

        #attendanceChart {
            max-width: 300px;
            max-height: 300px;
            margin: auto;
        }
    </style>
</head>

<body>
    <div id="container">
        <?php include('../includes/admin_sidebar.php'); ?>
        <div id="main-content">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <!-- <h2>Welcome, <?php //echo htmlspecialchars($_SESSION['role']); 
                                ?>!</h2> -->

            <!-- First Row: Stats Cards -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white text-center">
                        <div class="card-body">
                            <h4>Total Employees</h4>
                            <h2><?php echo $totalEmployees; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white text-center">
                        <div class="card-body">
                            <h4>Pending Leaves</h4>
                            <h2><?php echo $pendingLeaves; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white text-center">
                        <div class="card-body">
                            <h4>Today's Attendance</h4>
                            <h2><?php echo $presentCount; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white text-center">
                        <div class="card-body">
                            <h4>Pending Queries</h4>
                            <h2><?php echo $pendingQueries; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Row: Leave and Query Charts -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body chart-container">
                            <h5 class="card-title">Leave Status Distribution</h5>
                            <canvas id="leaveChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body chart-container">
                            <h5 class="card-title">Query Status Distribution</h5>
                            <canvas id="queryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Third Row: Attendance Pie Chart -->
            <div class="row mt-4">
                <div class="col-md-6 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-center">Today's Attendance Overview</h5>
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Leave Chart
        const leaveData = {
            approved: <?php echo $approvedLeaves; ?>,
            pending: <?php echo $pendingLeavesChart; ?>,
            rejected: <?php echo $rejectedLeaves; ?>
        };

        const ctx1 = document.getElementById('leaveChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{
                    label: 'Leave Status',
                    data: [leaveData.approved, leaveData.pending, leaveData.rejected],
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.7)', // Approved - Success green
                        'rgba(255, 193, 7, 0.7)', // Pending - Warning yellow
                        'rgba(220, 53, 69, 0.7)' // Rejected - Danger red
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)', // Approved
                        'rgba(255, 193, 7, 1)', // Pending
                        'rgba(220, 53, 69, 1)' // Rejected
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Count: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });

        // Query Chart
        // Query Chart
        const ctx3 = document.getElementById('queryChart').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: <?php echo $queryLabelsJSON; ?>,
                datasets: [{
                    label: 'Query Status',
                    data: <?php echo $queryCountsJSON; ?>,
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.7)', // Pending - Warning yellow
                        'rgba(40, 167, 69, 0.7)', // Approved - Success green
                        'rgba(23, 162, 184, 0.7)', // Hold - Info blue
                        'rgba(220, 53, 69, 0.7)' // Rejected - Danger red
                    ],
                    borderColor: [
                        'rgba(255, 193, 7, 1)', // Pending
                        'rgba(40, 167, 69, 1)', // Approved
                        'rgba(23, 162, 184, 1)', // Hold
                        'rgba(220, 53, 69, 1)' // Rejected
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Count: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });

        // Attendance Chart
        const attendanceData = {
            present: <?php echo $presentCount; ?>,
            absent: <?php echo $absentCount; ?>
        };

        const ctx2 = document.getElementById('attendanceChart').getContext('2d');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Present', 'Absent'],
                datasets: [{
                    data: [attendanceData.present, attendanceData.absent],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>