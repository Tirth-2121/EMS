<?php
// admin/export_db.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Set headers
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="ems_db_backup_' . date("Y-m-d_H-i-s") . '.sql"');

// Database credentials
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'ems_db';

// Create backup file
$backup_file = tempnam(sys_get_temp_dir(), 'mysql_backup');

// Windows path (adjust if needed)
$mysqldump = 'C:\xampp\mysql\bin\mysqldump.exe';

// Build command
$command = sprintf(
    '"%s" --host=%s --user=%s --password=%s %s > %s',
    $mysqldump,
    $host,
    $user,
    $password,
    $database,
    $backup_file
);

// Execute command
exec($command);

// Read and output file
if (file_exists($backup_file) && filesize($backup_file) > 0) {
    readfile($backup_file);
    unlink($backup_file); // Delete temporary file
} else {
    die("Error: Backup file is empty or not created");
}

exit;
