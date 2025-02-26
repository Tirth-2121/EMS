<?php
session_start();
include 'config.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// **Handle Add, Edit, Delete Actions**
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['task'])) {
        // 📌 Add a new To-Do
        $task = $_POST['task'];
        $stmt = $conn->prepare("INSERT INTO todos (user_id, task) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task);
        $stmt->execute();
    } elseif (isset($_POST['edit_id'], $_POST['edit_task'])) {
        // 📌 Edit a To-Do
        $id = $_POST['edit_id'];
        $task = $_POST['edit_task'];
        $stmt = $conn->prepare("UPDATE todos SET task = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $task, $id, $user_id);
        $stmt->execute();
    } elseif (isset($_POST['delete_id'])) {
        // 📌 Delete a To-Do
        $id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM todos WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// **Fetch To-Do List**
$stmt = $conn->prepare("SELECT * FROM todos WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$todos = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .todo-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #ffffff;
            border-radius: 5px;
            margin-bottom: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .modal-content {
            border-radius: 10px;
        }

        #main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>

<body id="container">
    <?php $role === 'admin' ? include('./includes/admin_sidebar.php') : include('./includes/employee_sidebar.php'); ?>
    <div id="main-content">
        <div class="container">
            <h3 class="text-center mb-4">📌 To-Do List</h3>
            <form method="POST" class="d-flex gap-2">
                <input type="text" name="task" class="form-control" placeholder="Enter a task" required>
                <button type="submit" class="btn btn-primary">Add</button>
            </form>

            <div class="mt-4">
                <?php foreach ($todos as $todo) : ?>
                    <div class="todo-item">
                        <span><?= htmlspecialchars($todo['task']) ?></span>
                        <div>
                            <button class="btn btn-sm btn-warning edit-btn" data-id="<?= $todo['id'] ?>" data-task="<?= htmlspecialchars($todo['task']) ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $todo['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <!-- Edit Modal -->
    <div id="editModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content p-3">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <input type="text" name="edit_task" id="edit_task" class="form-control" required>
                        <button type="submit" class="btn btn-primary mt-3">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll(".edit-btn").forEach(btn => {
            btn.addEventListener("click", function() {
                document.getElementById("edit_id").value = this.getAttribute("data-id");
                document.getElementById("edit_task").value = this.getAttribute("data-task");
                new bootstrap.Modal(document.getElementById("editModal")).show();
            });
        });
    </script>
</body>

</html>