<?php
session_start();
include "../db.php";

// Check if Super Admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != "Super Admin") {
    header("Location: login.php");
    exit();
}

// Check if ID exists
if (!isset($_GET['id'])) {
    header("Location: manage-admins.php");
    exit();
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM admins WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Administrator not found.");
}

$admin = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Administrator</title>

<link rel="stylesheet" href="../style.css">

<style>

body{
    background:#f4f4f4;
    font-family:Arial, Helvetica, sans-serif;
}

.container{
    width:600px;
    margin:40px auto;
    background:#fff;
    padding:30px;
    border-radius:8px;
    box-shadow:0 0 10px rgba(0,0,0,.1);
}

h2{
    text-align:center;
    margin-bottom:20px;
}

.form-group{
    margin-bottom:15px;
}

label{
    display:block;
    margin-bottom:6px;
    font-weight:bold;
}

input,
select{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:5px;
}

button{
    width:100%;
    padding:12px;
    background:#0d6efd;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
    font-size:16px;
}

button:hover{
    background:#0b5ed7;
}

.back{
    text-align:center;
    margin-top:15px;
}

.back a{
    text-decoration:none;
    color:#0d6efd;
}

</style>

</head>

<body>

<div class="container">

<h2>Edit Administrator</h2>

<form action="update-admin.php" method="POST">

<input
type="hidden"
name="id"
value="<?php echo $admin['id']; ?>">

<div class="form-group">

<label>Full Name</label>

<input
type="text"
name="fullname"
value="<?php echo htmlspecialchars($admin['fullname']); ?>"
required>

</div>

<div class="form-group">

<label>Username</label>

<input
type="text"
name="username"
value="<?php echo htmlspecialchars($admin['username']); ?>"
required>

</div>

<div class="form-group">

<label>Email</label>

<input
type="email"
name="email"
value="<?php echo htmlspecialchars($admin['email']); ?>"
required>

</div>

<div class="form-group">

<label>New Password</label>

<input
type="password"
name="password"
placeholder="Leave blank to keep the current password">

</div>

<div class="form-group">

<label>Role</label>

<select name="role" required>

<option value="Super Admin" <?php if($admin['role']=="Super Admin") echo "selected"; ?>>
Super Admin
</option>

<option value="Admin" <?php if($admin['role']=="Admin") echo "selected"; ?>>
Admin
</option>

<option value="Land Officer" <?php if($admin['role']=="Land Officer") echo "selected"; ?>>
Land Officer
</option>

<option value="Registrar" <?php if($admin['role']=="Registrar") echo "selected"; ?>>
Registrar
</option>

</select>

</div>

<button type="submit">
Update Administrator
</button>

</form>

<div class="back">

<a href="manage-admins.php">
← Back to Manage Administrators
</a>

</div>

</div>

</body>
</html>