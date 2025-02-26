<nav class="navbar navbar-dark bg-dark">
    <a class="navbar-brand px-3" href="dashboard.php">EMS Admin</a>
    <span class="text-white px-3"><?= $_SESSION['username'] ?></span>
    <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
</nav>

<div class="d-flex">
    <div class="bg-light p-3" style="width: 250px; min-height: 100vh;">
        <h4>Menu</h4>
        <ul class="list-unstyled">
            <li><a href="dashboard.php" class="btn btn-link">Dashboard</a></li>
            <li><a href="manage_employees.php" class="btn btn-link">Manage Employees</a></li>
            <li><a href="leaves.php" class="btn btn-link">Leave Requests</a></li>
            <li><a href="attendance.php" class="btn btn-link">Attendance</a></li>
            <li><a href="salary.php" class="btn btn-link">Salary Management</a></li>
            <li><a href="reports.php" class="btn btn-link">Reports</a></li>
        </ul>
    </div>
</div>
