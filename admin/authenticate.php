<?php
session_start();

if (!empty($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once "../db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    echo "<script>alert('Please enter both username and password.'); window.location='login.php';</script>";
    exit();
}

$sql = "SELECT id, first_name, username, password, role, status FROM administrators WHERE username = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($admin && $admin['status'] === 'Active' && password_verify($password, $admin['password'])) {
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['first_name'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['logged_in'] = true;

    mysqli_close($conn);
    header("Location: dashboard.php");
    exit();
}

mysqli_close($conn);

echo "<script>alert('Invalid username or password.'); window.location='login.php';</script>";
exit();
?>