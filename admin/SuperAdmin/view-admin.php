<?php
session_start();
include("../../db.php");

if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] != "Super Admin") {
    header("Location: login.php");
    exit();
}

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

if ($search != "") {

    $sql = "SELECT * FROM administrators
            WHERE first_name LIKE ?
            OR last_name LIKE ?
            OR username LIKE ?
            OR email LIKE ?
            ORDER BY id DESC";

    $stmt = mysqli_prepare($conn, $sql);

    $keyword = "%".$search."%";

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $keyword,
        $keyword,
        $keyword,
        $keyword
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $result = mysqli_query(
        $conn,
        "SELECT * FROM administrators ORDER BY id DESC"
    );

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>View Administrators</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:Arial,sans-serif;
}

body{
background:#f4f6f9;
}

.sidebar{

position:fixed;

width:250px;

height:100%;

background:#1f3c5b;

}

.sidebar h2{

color:white;

padding:20px;

text-align:center;

}

.sidebar a{

display:block;

padding:15px 25px;

color:white;

text-decoration:none;

}

.sidebar a:hover{

background:#2f5f8b;

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

.search-box{

display:flex;

gap:10px;

margin-bottom:20px;

}

.search-box input{

padding:10px;

width:300px;

border:1px solid #ccc;

border-radius:5px;

}

.search-box button{

padding:10px 20px;

background:#28a745;

border:none;

color:white;

border-radius:5px;

cursor:pointer;

}

table{

width:100%;

border-collapse:collapse;

background:white;

box-shadow:0 0 10px rgba(0,0,0,.1);

}

th{

background:#1f3c5b;

color:white;

padding:15px;

}

td{

padding:12px;

border-bottom:1px solid #ddd;

text-align:center;

}

.badge-active{

background:#28a745;

color:white;

padding:5px 10px;

border-radius:20px;

}

.badge-suspended{

background:#dc3545;

color:white;

padding:5px 10px;

border-radius:20px;

}

.edit{

background:#007bff;

padding:8px 12px;

color:white;

text-decoration:none;

border-radius:5px;

}

.delete{

background:#dc3545;

padding:8px 12px;

color:white;

text-decoration:none;

border-radius:5px;

}

.add-btn{

background:#28a745;

color:white;

padding:12px 18px;

text-decoration:none;

border-radius:6px;

}

</style>

</head>

<body>

<div class="sidebar">

<h2>E-Land</h2>

<a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>

<a href="create-admin.php"><i class="fas fa-user-plus"></i> Add Administrator</a>

<a href="view-admin.php"><i class="fas fa-users"></i> View Administrators</a>

<a href="profile.php"><i class="fas fa-user"></i> Profile</a>

<a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>

</div>

<div class="main">

<div class="header">

<h1>Administrators</h1>

<a href="create-admin.php" class="add-btn">
<i class="fas fa-plus"></i>
New Administrator
</a>

</div>

<form method="GET">

<div class="search-box">

<input
type="text"
name="search"
placeholder="Search administrator..."
value="<?php echo htmlspecialchars($search); ?>">

<button type="submit">

<i class="fas fa-search"></i>

Search

</button>

</div>

</form>

<table>

<tr>

<th>ID</th>

<th>Name</th>

<th>Username</th>

<th>Email</th>

<th>Phone</th>

<th>Role</th>

<th>Status</th>

<th>Created</th>

<th>Actions</th>

</tr>

<?php

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo htmlspecialchars($row['first_name']." ".$row['last_name']); ?></td>

<td><?php echo htmlspecialchars($row['username']); ?></td>

<td><?php echo htmlspecialchars($row['email']); ?></td>

<td><?php echo htmlspecialchars($row['phone']); ?></td>

<td><?php echo htmlspecialchars($row['role']); ?></td>

<td>

<?php

if($row['status']=="Active"){

echo "<span class='badge-active'>Active</span>";

}else{

echo "<span class='badge-suspended'>Suspended</span>";

}

?>

</td>

<td><?php echo $row['created_at']; ?></td>

<td>

<a
class="edit"
href="edit-admin.php?id=<?php echo $row['id']; ?>">

<i class="fas fa-edit"></i>

Edit

</a>

<a
class="delete"
href="delete-admin.php?id=<?php echo $row['id']; ?>"
onclick="return confirm('Delete this administrator?')">

<i class="fas fa-trash"></i>

Delete

</a>

</td>

</tr>

<?php

}

?>

</table>

</div>

</body>

</html>