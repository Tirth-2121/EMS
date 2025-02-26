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

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch employee_id from employees table
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

$employee_id = $employee['id']; // Now we have the correct employee_id

// Check if already punched in today
$date = date("Y-m-d");
$check_query = "SELECT * FROM attendance WHERE employee_id = ? AND DATE(punch_in) = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("is", $employee_id, $date);
$stmt->execute();
$result = $stmt->get_result();
$attendance = $result->fetch_assoc();
$stmt->close();

$punch_in_time = $attendance ? $attendance['punch_in'] : null;
$punch_out_time = $attendance ? $attendance['punch_out'] : null;
$working_hours = $attendance ? $attendance['working_hours'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['punch_in'])) {
        if ($attendance) {
            $message = "<div class='alert alert-warning'>You have already punched in today!</div>";
        } else {
            $punch_in_time = date("Y-m-d H:i:s");
            $insert_query = "INSERT INTO attendance (employee_id, punch_in) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("is", $employee_id, $punch_in_time);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Punched In Successfully at $punch_in_time</div>";
                $punch_out_time = null; // Reset punch out time
            } else {
                $message = "<div class='alert alert-danger'>Error punching in: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } elseif (isset($_POST['punch_out'])) {
        if (!$attendance) {
            $message = "<div class='alert alert-warning'>You need to punch in first!</div>";
        } elseif ($punch_out_time) {
            $message = "<div class='alert alert-warning'>You have already punched out today!</div>";
        } else {
            $punch_out_time = date("Y-m-d H:i:s");
            $punch_in_time_calc = strtotime($punch_in_time);
            $punch_out_time_calc = strtotime($punch_out_time);
            $working_hours = gmdate("H:i:s", $punch_out_time_calc - $punch_in_time_calc);

            $update_query = "UPDATE attendance SET punch_out = ?, working_hours = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $punch_out_time, $working_hours, $attendance['id']);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Punched Out Successfully at $punch_out_time. Working Hours: $working_hours</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error punching out: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Attendance Dashboard</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .dashboard {
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #333;
        }

        .time-box {
            background: #f8f9fa;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 18px;
        }

        .btn {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin: 5px;
            transition: 0.3s;
        }

        .btn-primary {
            background-color: #28a745;
            color: white;
        }

        .btn-primary:disabled {
            background-color: #a0cfa0;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:disabled {
            background-color: #e0a0a0;
        }

        .alert {
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        #main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>

<body id="container">
    <?php include('../includes/employee_sidebar.php'); ?>
    <div id="main-content">
        <div class="dashboard">
            <h2>Employee Attendance Dashboard</h2>
            <div id="clock" class="time-box"></div>
            <div class="time-box"><strong>Punch In:</strong> <?= $punch_in_time ?? "Not Punched In Yet" ?></div>
            <div class="time-box"><strong>Working Time:</strong> <span id="working_time">00:00:00</span></div>
            <div class="time-box"><strong>Punch Out:</strong> <?= $punch_out_time ?? "Not Punched Out Yet" ?></div>
            <?= $message ?>
            <form method="POST">
                <button type="submit" name="punch_in" class="btn btn-primary" <?= ($attendance) ? 'disabled' : '' ?>>Punch In</button>
                <button type="submit" name="punch_out" class="btn btn-danger" <?= (!$attendance || $punch_out_time) ? 'disabled' : '' ?>>Punch Out</button>
            </form>
        </div>
    </div>

    <script>
        function updateClock() {
            let now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString();
        }

        function updateWorkingTime() {
            let punchIn = <?= $punch_in_time ? '"' . $punch_in_time . '"' : 'null' ?>;
            let punchOut = <?= $punch_out_time ? '"' . $punch_out_time . '"' : 'null' ?>;

            if (punchIn && !punchOut) {
                let startTime = new Date(punchIn).getTime();
                let now = new Date().getTime();
                let diff = now - startTime;
                let hours = Math.floor(diff / (1000 * 60 * 60));
                let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                let seconds = Math.floor((diff % (1000 * 60)) / 1000);
                document.getElementById('working_time').innerText =
                    (hours < 10 ? "0" : "") + hours + ":" +
                    (minutes < 10 ? "0" : "") + minutes + ":" +
                    (seconds < 10 ? "0" : "") + seconds;
            }
        }

        setInterval(updateClock, 1000);
        setInterval(updateWorkingTime, 1000);
    </script>
</body>

</html>