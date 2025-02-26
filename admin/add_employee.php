<?php
include '../config.php'; // Include database connection

$message = ""; // Store success or error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hashed_password = MD5($password);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    $salary = floatval($_POST['salary']); // Convert salary to decimal
    $date_of_joining = date("Y-m-d", strtotime($_POST['date_of_joining'])); // Format date properly

    // Check if username or email already exists
    $check_query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "<div class='alert alert-danger'>Username or Email already exists!</div>";
    } else {
        // Insert into users table
        $user_query = "INSERT INTO users (username, password, role, email) VALUES (?, ?, 'employee', ?)";
        $stmt = $conn->prepare($user_query);
        if (!$stmt) {
            die("User Query Failed: " . $conn->error);
        }
        $stmt->bind_param("sss", $username, $hashed_password, $email);

        if ($stmt->execute()) {
            $user_id = $conn->insert_id;

            // Debugging Output
            echo "DEBUG: Inserted User ID = " . $user_id . "<br>";

            // REMOVE Email Check in Employees Table 
            // employees table does not have an `email` column!

            // Insert into employees table
            $emp_query = "INSERT INTO employees (user_id, full_name, position, department, salary, join_date,status ) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";

            // echo "DEBUG: SQL Query = " . $emp_query . "<br>"; // Debugging

            $stmt = $conn->prepare($emp_query);

            if (!$stmt) {
                die("Employee Query Failed: " . $conn->error . " | SQL: " . $emp_query);
            }

            $status = "Active"; // Default value

            $stmt->bind_param("isssdss", $user_id, $full_name, $position, $department, $salary, $date_of_joining, $status);

            if (!$stmt->execute()) {
                die("Error adding employee: " . $stmt->error);
            }

            echo "<div class='alert alert-success'>Employee added successfully!</div>";
            header("Location: /ems/admin/employees.php");
            exit();
        } else {
            $message = "<div class='alert alert-danger'>Error creating user!</div>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee</title>
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
        <div class="container mt-4">
            <h2 class="text-center mb-4">Add New Employee</h2>

            <?= $message; ?> <!-- Display success/error messages -->

            <div class="card shadow-lg">
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Username:</label>
                                <input type="text" class="form-control" name="username" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password:</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Full Name:</label>
                                <input type="text" class="form-control" name="full_name" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email:</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone:</label>
                                <input type="text" class="form-control" name="phone">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Department:</label>
                                <select class="form-select" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="Development">Development</option>
                                    <option value="QA">QA</option>
                                    <option value="Designer">Designer</option>
                                    <option value="Sales">Sales</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Position:</label>
                                <input type="text" class="form-control" name="position">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Salary:</label>
                                <input type="number" class="form-control" step="0.01" name="salary">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date of Joining:</label>
                                <input type="date" class="form-control" name="date_of_joining">
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Add Employee</button>
                            <a href="employees.php" class="btn btn-secondary">Back to List</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>