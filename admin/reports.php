<?php
// Include database connection
include '../config.php';

if (isset($_GET['report'])) {
    $reportType = $_GET['report'];

    switch ($reportType) {
        case 'employees':
            generateEmployeeReport($conn);
            break;
        case 'holidays':
            generateHolidayReport($conn);
            break;
        case 'leaves':
            generateLeaveReport($conn);
            break;
        case 'queries':
            generateQueryReport($conn);
            break;
    }
}

function generateEmployeeReport($conn)
{
    $filename = "employee_report.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['Employee Name', 'Email', 'Username', 'Department', 'Position', 'Salary', 'Join Date']);

    $query = "SELECT e.full_name, u.email, u.username, e.department, e.position, e.salary, e.join_date 
              FROM employees e 
              JOIN users u ON e.user_id = u.id";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

function generateHolidayReport($conn)
{
    $filename = "holiday_report.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['Holiday Date', 'Holiday Name', 'Description']);

    $query = "SELECT holiday_date, holiday_name, description FROM holidays";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

function generateLeaveReport($conn)
{
    $startDate = $_GET['start_date'];
    $endDate = $_GET['end_date'];

    $filename = "leave_report.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['Employee Name', 'Start Date', 'End Date', 'Leave Days', 'Leave Type', 'Reason', 'Status']);

    $query = "SELECT e.full_name, l.start_date, l.end_date, l.leave_days, l.leave_type, l.reason, l.status 
              FROM leaves l 
              JOIN employees e ON l.employee_id = e.id 
              WHERE l.start_date BETWEEN ? AND ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $startDate, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

function generateQueryReport($conn)
{
    $filename = "query_report.csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen("php://output", "w");
    fputcsv($output, ['Employee Name', 'Subject', 'Description', 'Status', 'Submitted At']);

    $query = "SELECT e.full_name, q.subject, q.description, q.status, q.submitted_at 
              FROM queries q 
              JOIN employees e ON q.employee_id = e.id";
    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}
?>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .report-card {
            height: 200px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border: none;
            border-radius: 15px;
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .employee-card {
            background: linear-gradient(135deg, #c2e9fb 0%, #a1c4fd 100%);
        }

        .holiday-card {
            background: linear-gradient(135deg, #fccb90 0%, #d57eeb 100%);
        }

        .leaves-card {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }

        .queries-card {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .report-card h5 {
            color: #2c3e50;
            font-weight: 600;
            margin-top: 10px;
        }

        .card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .report-card {
                height: 150px;
                margin-bottom: 20px;
            }

            .card-icon {
                font-size: 2.5rem;
            }
        }
    </style>
</head>

<body id="container">
    <?php include('../includes/admin_sidebar.php'); ?>
    <div id="main-content">
        <div class="container mt-5">
            <h3 class="mb-4">
                <i class="bi bi-file-earmark-bar-graph me-2"></i>
                Admin Reports
            </h3>
            <div class="row g-4">
                <!-- Employee Report Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card employee-card" onclick="downloadReport('employees')">
                        <div class="card-content">
                            <i class="bi bi-people-fill card-icon"></i>
                            <h5>Employee Report</h5>
                            <small class="text-muted">View complete employee data</small>
                        </div>
                    </div>
                </div>

                <!-- Holiday Report Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card holiday-card" onclick="downloadReport('holidays')">
                        <div class="card-content">
                            <i class="bi bi-calendar-event card-icon"></i>
                            <h5>Holiday Report</h5>
                            <small class="text-muted">Check holiday schedule</small>
                        </div>
                    </div>
                </div>

                <!-- Leaves Report Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card leaves-card" onclick="showLeaveModal()">
                        <div class="card-content">
                            <i class="bi bi-calendar2-check card-icon"></i>
                            <h5>Leaves Report</h5>
                            <small class="text-muted">Employee leave statistics</small>
                        </div>
                    </div>
                </div>

                <!-- Queries Report Card -->
                <div class="col-md-6 col-lg-3">
                    <div class="card report-card queries-card" onclick="downloadReport('queries')">
                        <div class="card-content">
                            <i class="bi bi-chat-square-text card-icon"></i>
                            <h5>Queries Report</h5>
                            <small class="text-muted">View all query records</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Original Scripts (Unchanged) -->
    <script>
        function showLeaveModal() {
            Swal.fire({
                title: 'Select Date Range',
                html: `
                    <input type="date" id="start_date" class="swal2-input">
                    <input type="date" id="end_date" class="swal2-input">
                `,
                showCancelButton: true,
                confirmButtonText: 'Generate Report',
                preConfirm: () => {
                    const start_date = document.getElementById('start_date').value;
                    const end_date = document.getElementById('end_date').value;
                    if (!start_date || !end_date) {
                        Swal.showValidationMessage('Both dates are required!');
                    } else {
                        window.location.href = `?report=leaves&start_date=${start_date}&end_date=${end_date}`;
                    }
                }
            });
        }

        function downloadReport(type) {
            window.location.href = `?report=${type}`;
        }
    </script>
</body>

</html>