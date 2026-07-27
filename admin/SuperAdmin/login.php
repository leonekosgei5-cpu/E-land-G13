<?php
session_start();

if(isset($_SESSION['admin_id'])){
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">

<title>Super Admin Login</title>

<link rel="stylesheet" href="../style.css">

<style>

body{

background:#f4f6f9;

font-family:Arial;

display:flex;

justify-content:center;

align-items:center;

height:100vh;

}

.login-box{

width:400px;

background:white;

padding:40px;

border-radius:10px;

box-shadow:0 0 20px rgba(0,0,0,.1);

}

.login-box h2{

text-align:center;

margin-bottom:30px;

color:#2c3e50;

}

input{

width:100%;

padding:12px;

margin-bottom:20px;

border:1px solid #ddd;

border-radius:5px;

}

button{

width:100%;

padding:12px;

background:#27ae60;

color:white;

border:none;

border-radius:5px;

font-size:16px;

cursor:pointer;

}

button:hover{

background:#219150;

}

</style>

</head>

<body>

<div class="login-box">

<h2>Super Administrator</h2>

<form action="authenticate.php" method="POST">

<input
type="text"
name="username"
placeholder="Username"
required>

<input
type="password"
name="password"
placeholder="Password"
required>

<button type="submit">

Login

</button>

</form>

</div>

</body>

</html>