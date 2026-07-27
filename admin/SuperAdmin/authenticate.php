<?php
session_start();

/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
*/

require_once("../../db.php");

/*
|--------------------------------------------------------------------------
| Check Database Connection
|--------------------------------------------------------------------------
*/

if (!isset($conn) || mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

/*
|--------------------------------------------------------------------------
| Allow POST Requests Only
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {

    echo "<script>
            alert('Please enter both username and password.');
            window.location='login.php';
          </script>";
    exit();
}

/*
|--------------------------------------------------------------------------
| Find Administrator
|--------------------------------------------------------------------------
*/

$sql = "SELECT *
        FROM administrators
        WHERE username = ?
        AND status = 'Active'
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "s", $username);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

/*
|--------------------------------------------------------------------------
| Verify Login
|--------------------------------------------------------------------------
*/

if ($result && mysqli_num_rows($result) == 1) {

    $admin = mysqli_fetch_assoc($result);

    if (password_verify($password, $admin['password'])) {

        session_regenerate_id(true);

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['first_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['logged_in'] = true;

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        header("Location: dashboard.php");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| Invalid Login
|--------------------------------------------------------------------------
*/

mysqli_stmt_close($stmt);
mysqli_close($conn);

echo "<script>
        alert('Invalid username or password.');
        window.location='login.php';
      </script>";
exit();
?>