<?php
session_start();
include("../../db.php");

// Only Super Admin can access
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != "Super Admin") {
    header("Location: login.php");
    exit();
}

// Only accept POST requests
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: create-admin.php");
    exit();
}

// Collect form data
$first_name = trim($_POST['first_name']);
$last_name = trim($_POST['last_name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$username = trim($_POST['username']);
$role = trim($_POST['role']);
$status = trim($_POST['status']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate required fields
if (
    empty($first_name) ||
    empty($last_name) ||
    empty($email) ||
    empty($phone) ||
    empty($username) ||
    empty($password)
) {
    header("Location: create-admin.php?error=Please fill all required fields");
    exit();
}

// Password confirmation
if ($password !== $confirm_password) {
    header("Location: create-admin.php?error=Passwords do not match");
    exit();
}

// Password length
if (strlen($password) < 8) {
    header("Location: create-admin.php?error=Password must be at least 8 characters");
    exit();
}

// Check duplicate username or email
$sql = "SELECT id FROM administrators WHERE username=? OR email=?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $username, $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    header("Location: create-admin.php?error=Username or Email already exists");
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Save administrator
$sql = "INSERT INTO administrators
(
first_name,
last_name,
email,
phone,
username,
password,
role,
status
)
VALUES
(
?,
?,
?,
?,
?,
?,
?,
?
)";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssssssss",
    $first_name,
    $last_name,
    $email,
    $phone,
    $username,
    $hashed_password,
    $role,
    $status
);

if (mysqli_stmt_execute($stmt)) {

    header("Location: create-admin.php?success=1");
    exit();

} else {

    header("Location: create-admin.php?error=Unable to save administrator");
    exit();

}
?>