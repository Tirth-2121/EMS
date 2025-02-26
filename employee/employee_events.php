<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in as an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit();
}

// Check if viewing past events
$show_past = isset($_GET['view_past']) ? true : false;

// Fetch events based on past or upcoming filter
$query = "SELECT * FROM events WHERE event_date " . ($show_past ? "<" : ">=") . " CURDATE() ORDER BY event_date ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        <div class="container mt-5">
            <h3><?= $show_past ? "Past Events" : "Upcoming Events" ?></h3>

            <div class="d-flex justify-content-between mb-3">
                <h5><?= $show_past ? "Past Events" : "Upcoming Events" ?></h5>
                <a href="employee_events.php?<?= $show_past ? "" : "view_past" ?>" class="btn btn-<?= $show_past ? "secondary" : "info" ?>">
                    <?= $show_past ? "Back to Upcoming Events" : "View Past Events" ?>
                </a>
            </div>

            <div class="row">
                <?php while ($event = $result->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($event['event_date']) ?></h6>
                                <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>

                <?php if ($result->num_rows === 0): ?>
                    <p class="text-center">No <?= $show_past ? "past" : "upcoming" ?> events found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
<?php $conn->close(); ?>