<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/toast.css">
    <script src="../assets/toast.js"></script>
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        #container {
            display: flex;
            height: 100vh;
        }

        .sidebar-header {
            padding: 0 15px;
            /* Reduced padding */
            margin-bottom: 0;
            /* Removed bottom margin */
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .ems-title {
            margin: 0;
            /* Remove any margins from title */
            padding: 0;
            /* Remove any padding */
            font-size: 24px;
            font-weight: bold;
        }

        #sidebar {
            padding-top: 10px;
            /* Reduced top padding */
        }

        #sidebar {
            width: 250px;
            /* Changed from 60px to be expanded by default */
            height: 100vh;
            background: #343a40;
            color: white;
            padding-top: 20px;
            overflow: hidden;
            transition: width 0.3s ease;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        #sidebar.collapsed {
            /* Changed from .expanded to .collapsed */
            width: 60px;
        }

        #sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            white-space: nowrap;
            gap: 10px;
        }

        #sidebar a:hover {
            background: #495057;
        }

        #pin-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            padding: 5px;
            display: block;
            /* Show by default */
        }

        #sidebar.collapsed #pin-btn {
            display: none;
            /* Hide when sidebar is collapsed */
        }

        #pin-btn:hover {
            color: #ffc107;
        }

        .menu-text {
            opacity: 1;
            /* Visible by default */
            transition: opacity 0.3s ease;
        }

        #sidebar.collapsed .menu-text {
            opacity: 0;
            display: none;
        }

        #logout-btn {
            width: 90%;
            margin: 10px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 8px;
        }

        #main-content {
            flex-grow: 1;
            padding: 20px;
            overflow-x: auto;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body>
    <div id="container">
        <!-- Sidebar -->
        <div id="sidebar">
            <div>
                <div class="sidebar-header">
                    <h3 class="ems-title" style="margin-left:10px;margin-bottom:10px;">EMS</h3>
                    <button id="pin-btn">
                        <i class="bi bi-pin-angle-fill"></i>
                    </button>
                </div>
                <div>
                    <a href="/ems/admin/dashboard.php">
                        <i class="bi bi-speedometer2"></i>
                        <span class="menu-text">Dashboard</span>
                    </a>
                    <a href="/ems/admin/employees.php">
                        <i class="bi bi-people-fill"></i>
                        <span class="menu-text">Manage Employees</span>
                    </a>
                    <a href="/ems/admin/leave_admin.php">
                        <i class="bi bi-calendar-check"></i>
                        <span class="menu-text">Leave Management</span>
                    </a>
                    <a href="/ems/todo.php">
                        <i class="bi bi-list-check"></i>
                        <span class="menu-text">To-Dos</span>
                    </a>
                    <a href="/ems/admin/reports.php">
                        <i class="bi bi-file-earmark-text"></i>
                        <span class="menu-text">Reports</span>
                    </a>
                    <a href="/ems/admin/salary.php">
                        <i class="bi bi-currency-dollar"></i>
                        <span class="menu-text">Salary Management</span>
                    </a>
                    <a href="/ems/admin/query_admin.php">
                        <i class="bi bi-question-circle"></i>
                        <span class="menu-text">Employee Queries</span>
                    </a>
                    <a href="/ems/admin/tasks.php">
                        <i class="bi bi-list-task"></i>
                        <span class="menu-text">Assign Task</span>
                    </a>
                    <a href="/ems/admin/admin_holidays.php">
                        <i class="bi bi-calendar-event"></i>
                        <span class="menu-text">Holidays</span>
                    </a>
                    <a href="/ems/admin/admin_events.php">
                        <i class="bi bi-calendar2-event"></i>
                        <span class="menu-text">Upcomming Events</span>
                    </a>
                    <a href="/ems/admin/export_db.php">
                        <i class="bi bi-download"></i>
                        <span class="menu-text">Export Database</span>
                    </a>
                    <a href="/ems/admin/admin_profile.php">
                        <i class="bi bi-person"></i>
                        <span class="menu-text">My Profile</span>
                    </a>
                </div>
            </div>

            <!-- Logout Button -->
            <a href="/ems/auth/logout.php" class="btn btn-danger" id="logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span class="menu-text">Logout</span>
            </a>
        </div>

        <!-- Main Content -->
        <!-- <div id="main-content">
            <h2>Admin Dashboard</h2>
            <p>Welcome to your dashboard!</p>
        </div> -->
    </div>

    <script>
        const sidebar = document.getElementById("sidebar");
        const pinButton = document.getElementById("pin-btn");

        let isPinned = true; // Start with pinned state

        pinButton.addEventListener("click", () => {
            isPinned = !isPinned;
            sidebar.classList.toggle("collapsed", !isPinned); // Toggle collapsed instead of expanded
            pinButton.innerHTML = isPinned ?
                '<i class="bi bi-pin-angle-fill"></i>' :
                '<i class="bi bi-pin-angle"></i>';
        });

        sidebar.addEventListener("mouseenter", () => {
            if (!isPinned) sidebar.classList.remove("collapsed");
        });

        sidebar.addEventListener("mouseleave", () => {
            if (!isPinned) sidebar.classList.add("collapsed");
        });
    </script>
</body>

</html>