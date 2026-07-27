<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != "Super Admin") {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Create Administrator</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial,Helvetica,sans-serif;
}

body{
background:#f4f6f9;
}

.sidebar{
position:fixed;
left:0;
top:0;
width:250px;
height:100%;
background:#1e3a5f;
padding-top:20px;
}

.sidebar h2{
color:white;
text-align:center;
margin-bottom:30px;
}

.sidebar a{
display:block;
padding:15px 25px;
color:white;
text-decoration:none;
transition:.3s;
}

.sidebar a:hover{
background:#2d5b91;
}

.main{
margin-left:250px;
padding:30px;
}

.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:30px;
}

.form-box{

max-width:700px;

background:white;

padding:30px;

border-radius:10px;

box-shadow:0 5px 15px rgba(0,0,0,.1);

}

.form-group{

margin-bottom:20px;

}

label{

display:block;

margin-bottom:8px;

font-weight:bold;

}

input,
select{

width:100%;

padding:12px;

border:1px solid #ccc;

border-radius:6px;

font-size:15px;

}

button{

background:#28a745;

color:white;

border:none;

padding:14px 30px;

font-size:16px;

border-radius:6px;

cursor:pointer;

}

button:hover{

background:#218838;

}

.row{

display:grid;

grid-template-columns:1fr 1fr;

gap:20px;

}

.success{

background:#d4edda;

color:#155724;

padding:15px;

margin-bottom:20px;

border-radius:6px;

}

.error{

background:#f8d7da;

color:#721c24;

padding:15px;

margin-bottom:20px;

border-radius:6px;

}

</style>

</head>

<body>

<div class="sidebar">

<h2>E-Land</h2>

<a href="dashboard.php">
<i class="fas fa-home"></i>
 Dashboard
</a>

<a href="view-admins.php">
<i class="fas fa-users"></i>
 Administrators
</a>

<a href="create-admin.php">
<i class="fas fa-user-plus"></i>
 Create Admin
</a>

<a href="profile.php">
<i class="fas fa-user-circle"></i>
 Profile
</a>

<a href="logout.php">
<i class="fas fa-sign-out-alt"></i>
 Logout
</a>

</div>

<div class="main">

<div class="header">

<h1>Create New Administrator</h1>

</div>

<?php

if(isset($_GET['success'])){

echo "<div class='success'>
Administrator created successfully.
</div>";

}

if(isset($_GET['error'])){

echo "<div class='error'>"
.htmlspecialchars($_GET['error']).
"</div>";

}

?>

<div class="form-box">

<form action="save-admin.php" method="POST">

<div class="row">

<div class="form-group">

<label>First Name</label>

<input
type="text"
name="first_name"
required>

</div>

<div class="form-group">

<label>Last Name</label>

<input
type="text"
name="last_name"
required>

</div>

</div>

<div class="row">

<div class="form-group">

<label>Email</label>

<input
type="email"
name="email"
required>

</div>

<div class="form-group">

<label>Phone</label>

<input
type="text"
name="phone"
required>

</div>

</div>

<div class="row">

<div class="form-group">

<label>Username</label>

<input
type="text"
name="username"
required>

</div>

<div class="form-group">

<label>Role</label>

<select name="role">

<option value="Admin">Administrator</option>

<option value="Super Admin">Super Administrator</option>

</select>

</div>

</div>

<div class="row">

<div class="form-group">

<label>Password</label>

<input
type="password"
name="password"
required>

</div>

<div class="form-group">

<label>Confirm Password</label>

<input
type="password"
name="confirm_password"
required>

</div>

</div>

<div class="form-group">

<label>Status</label>

<select name="status">

<option value="Active">Active</option>

<option value="Suspended">Suspended</option>

</select>

</div>

<button type="submit">

<i class="fas fa-save"></i>

Create Administrator

</button>

</form>

</div>

</div>

</body>

</html>