<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if event ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_events.php");
    exit();
}

$event_id = intval($_GET['id']);

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

// If event not found, redirect to event list
if (!$event) {
    header("Location: admin_events.php");
    exit();
}

// Handle Event Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_event'])) {
    $title = $_POST['title'];
    $event_date = $_POST['event_date'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE events SET title = ?, event_date = ?, description = ? WHERE id = ?");
    $stmt->bind_param("sssi", $title, $event_date, $description, $event_id);

    if ($stmt->execute()) {
        header("Location: admin_events.php");
        exit();
    } else {
        $error = "Error updating event.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
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
            <h3>Edit Event</h3>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Event Date</label>
                    <input type="date" name="event_date" class="form-control" value="<?= htmlspecialchars($event['event_date']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" required><?= htmlspecialchars($event['description']) ?></textarea>
                </div>
                <button type="submit" name="update_event" class="btn btn-success">Update Event</button>
                <a href="admin_events.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</body>

</html>