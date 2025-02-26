<?php
include '../config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle adding, updating, and deleting holidays
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_holiday'])) {
        $holiday_name = $_POST['holiday_name'];
        $holiday_date = $_POST['holiday_date'];
        $description = $_POST['description'];

        $query = "INSERT INTO holidays (holiday_name, holiday_date, description) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $holiday_name, $holiday_date, $description);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['edit_holiday'])) {
        $holiday_id = $_POST['holiday_id'];
        $holiday_name = $_POST['holiday_name'];
        $holiday_date = $_POST['holiday_date'];
        $description = $_POST['description'];

        $query = "UPDATE holidays SET holiday_name = ?, holiday_date = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $holiday_name, $holiday_date, $description, $holiday_id);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['delete_holiday'])) {
        $holiday_id = $_POST['holiday_id'];

        $query = "DELETE FROM holidays WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $holiday_id);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
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

<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Holidays</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
    <script>
        function openEditModal(id, name, date, desc) {
            document.getElementById("edit_holiday_id").value = id;
            document.getElementById("edit_holiday_name").value = name;
            document.getElementById("edit_holiday_date").value = date;
            document.getElementById("edit_description").value = desc;
            new bootstrap.Modal(document.getElementById("editModal")).show();
        }
    </script>
</head>

<body class="bg-light" id="container">
    <?php include('../includes/admin_sidebar.php'); ?>
    <div id="main-content">
        <div class="container py-4">
            <h2 class="mb-4 text-center">Holiday Calendar - <?= $current_year ?></h2>

            <!-- Add Holiday Form -->
            <div class="card p-4 mb-4">
                <h4 class="mb-3">Add New Holiday</h4>
                <form method="POST" class="row g-3">
                    <div class="col-md-7">
                        <input type="text" name="holiday_name" class="form-control" placeholder="Holiday Name" required>
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="holiday_date" class="form-control" required min="<?= date('Y-01-01') ?>" max="<?= date('Y-12-31') ?>">
                    </div>
                    <div class="col-md-12">
                        <textarea name="description" class="form-control" placeholder="Holiday Description"></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="add_holiday" class="btn btn-primary">Add Holiday</button>
                    </div>
                </form>
            </div>

            <!-- Holiday List -->
            <table class="table table-bordered bg-white">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Holiday Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($holidays as $holiday): ?>
                        <tr>
                            <td><?= $holiday['holiday_date'] ?></td>
                            <td><?= htmlspecialchars($holiday['holiday_name']) ?></td>
                            <td><?= htmlspecialchars($holiday['description']) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="openEditModal('<?= $holiday['id'] ?>', '<?= htmlspecialchars($holiday['holiday_name']) ?>', '<?= $holiday['holiday_date'] ?>', '<?= htmlspecialchars($holiday['description']) ?>')">Edit</button>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="holiday_id" value="<?= $holiday['id'] ?>">
                                    <button type="submit" name="delete_holiday" class="btn btn-danger btn-sm" onclick="return confirm('Delete this holiday?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Edit Holiday Modal -->
    <div id="editModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Holiday</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="holiday_id" id="edit_holiday_id">
                        <div class="mb-3">
                            <input type="text" name="holiday_name" id="edit_holiday_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <input type="date" name="holiday_date" id="edit_holiday_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <textarea name="description" id="edit_description" class="form-control"></textarea>
                        </div>
                        <button type="submit" name="edit_holiday" class="btn btn-success">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>