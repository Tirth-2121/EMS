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

// Handle Event Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $event_date = $_POST['event_date'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO events (title, event_date, description) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $event_date, $description);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Delete Event
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $conn->query("DELETE FROM events WHERE id=$event_id");
    header("Location: admin_events.php");
}

// Fetch all events
$result = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events</title>
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
            <h3>Manage Events</h3>

            <!-- Add Event Form -->
            <form method="POST" class="mb-4">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Event Date</label>
                    <input type="date" name="event_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
            </form>

            <!-- Events Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Event Date</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($event = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= htmlspecialchars($event['event_date']) ?></td>
                            <td><?= htmlspecialchars($event['description']) ?></td>
                            <td>
                                <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="admin_events.php?delete=<?= $event['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this event?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
<?php $conn->close(); ?>