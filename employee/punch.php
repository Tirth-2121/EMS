<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../auth/login.php");
    exit();
}

include '../config.php';

$employee_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $timestamp = date("Y-m-d H:i:s");

    if ($action == 'punch_in') {
        $query = "INSERT INTO attendance (employee_id, punch_in) VALUES ('$employee_id', '$timestamp')";
    } elseif ($action == 'punch_out') {
        $query = "UPDATE attendance SET punch_out='$timestamp' WHERE employee_id='$employee_id' AND DATE(punch_in) = CURDATE() AND punch_out IS NULL";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Punch recorded successfully!'); window.location.href='punch.php';</script>";
    } else {
        echo "<script>alert('Error recording punch!');</script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$punch_query = "SELECT punch_in, punch_out FROM attendance WHERE employee_id='$employee_id' AND DATE(punch_in) = CURDATE()";
$punch_result = mysqli_query($conn, $punch_query);
$punch_data = mysqli_fetch_assoc($punch_result);

// Calculate working time
$working_time = '';
if ($punch_data && $punch_data['punch_in']) {
    $punch_in_time = new DateTime($punch_data['punch_in']);
    if ($punch_data['punch_out']) {
        $end_time = new DateTime($punch_data['punch_out']);
    } else {
        $end_time = new DateTime(); // Current time
    }
    $interval = $punch_in_time->diff($end_time);
    $working_time = sprintf(
        '%02d hours, %02d minutes',
        $interval->h + ($interval->days * 24),
        $interval->i
    );
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punch In/Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
        }

        #main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .punch-card {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .time-display {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
    </style>
</head>

<body>
    <?php include('../includes/employee_sidebar.php'); ?>
    <div id="main-content">
        <div class="container mt-4">
            <div class="punch-card">
                <h2 class="mb-4"><i class="fas fa-clock"></i> Punch In & Out</h2>

                <div class="time-display">
                    <div class="row">
                        <div class="col-md-4">
                            <p><i class="fas fa-sign-in-alt"></i> Punch In:<br>
                                <strong><?php echo $punch_data['punch_in'] ?? 'Not yet punched in'; ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><i class="fas fa-sign-out-alt"></i> Punch Out:<br>
                                <strong><?php echo $punch_data['punch_out'] ?? 'Not yet punched out'; ?></strong>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><i class="fas fa-hourglass-half"></i> Working Time:<br>
                                <strong><?php echo $working_time ?: 'N/A'; ?></strong>
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" class="mt-4">
                    <?php if (!$punch_data) { ?>
                        <button type="submit" name="action" value="punch_in" class="btn btn-success btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Punch In
                        </button>
                    <?php } elseif (!$punch_data['punch_out']) { ?>
                        <button type="submit" name="action" value="punch_out" class="btn btn-danger btn-lg">
                            <i class="fas fa-sign-out-alt"></i> Punch Out
                        </button>
                    <?php } else { ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> You have completed today's work!
                        </div>
                    <?php } ?>
                </form>

                <a href="dashboard.php" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>