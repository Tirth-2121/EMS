<?php
session_start();
include '../config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // Using MD5 (consider upgrading to bcrypt for security)

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role'];

        // Fetch employee_id for the logged-in user
        $user_id = $row['id'];
        $empQuery = "SELECT id FROM employees WHERE user_id='$user_id'";
        $empResult = $conn->query($empQuery);

        if ($empResult->num_rows == 1) {
            $empRow = $empResult->fetch_assoc();
            $_SESSION['employee_id'] = $empRow['id'];
        } else {
            $_SESSION['employee_id'] = null; // If no employee record is found
        }

        // Redirect based on role
        if ($row['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../employee/dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login | Employee Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: url('https://images.pexels.com/photos/3184291/pexels-photo-3184291.jpeg?auto=compress&cs=tinysrgb&h=1080') no-repeat center center/cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 380px;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            text-align: center;
        }

        .login-container h3 {
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .form-control {
            border-radius: 5px;
        }

        .btn-primary {
            background: #007bff;
            border: none;
            border-radius: 5px;
            padding: 10px;
            font-size: 16px;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .text-danger {
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h3>Employee Management System</h3>
        <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" class="rounded-circle mb-3" width="80" alt="EMS Logo">

        <form method="POST">
            <?php if (isset($error)) {
                echo "<p class='text-danger'>$error</p>";
            } ?>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

</body>

</html>