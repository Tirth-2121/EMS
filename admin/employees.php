<?php
include '../config.php'; // Database connection

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5; // Employees per page
$offset = ($page - 1) * $limit;

// Search query
$whereClause = "";
if (!empty($search)) {
    $whereClause = "WHERE e.full_name LIKE ? OR e.position LIKE ? OR u.username LIKE ?";
}

// Count total employees
$countQuery = "SELECT COUNT(*) as total FROM employees e JOIN users u ON e.user_id = u.id $whereClause";
$stmt = $conn->prepare($countQuery);
if (!empty($search)) {
    $searchParam = "%$search%";
    $stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
}
$stmt->execute();
$countResult = $stmt->get_result()->fetch_assoc();
$totalEmployees = $countResult['total'];
$totalPages = ceil($totalEmployees / $limit);

// Fetch employees with pagination
$query = "SELECT e.id, e.full_name, e.position, e.department, e.salary, e.status, u.username, u.email 
          FROM employees e 
          JOIN users u ON e.user_id = u.id 
          $whereClause 
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
if (!empty($search)) {
    $stmt->bind_param("sssii", $searchParam, $searchParam, $searchParam, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        #main-content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>

<body id='container'>
    <?php include('../includes/admin_sidebar.php'); ?>
    <div id="main-content">
        <div class="container mt-4">
            <h2 class="mb-3">Employee List</h2>

            <!-- Add Employee Button -->
            <div class="d-flex justify-content-between mb-3">
                <a href="add_employee.php" class="btn btn-success">+ Add Employee</a>
                <form class="d-flex" method="GET">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search Employee..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>

            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['position']) ?></td>
                            <td><?= htmlspecialchars($row['department']) ?></td>
                            <td>$<?= number_format($row['salary'], 2) ?></td>
                            <td><?= $row['status'] ?></td>
                            <td>
                                <a href="edit_employee.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="delete_employee.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <nav>
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?search=<?= $search ?>&page=<?= $page - 1 ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?search=<?= $search ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item"><a class="page-link" href="?search=<?= $search ?>&page=<?= $page + 1 ?>">Next</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
</body>

</html>

<?php $conn->close(); ?>