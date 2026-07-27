<?php
session_start();
include "../db.php";

// Check if Super Admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != "Super Admin") {
    header("Location: login.php");
    exit();
}

// Check if ID was provided
if (!isset($_GET['id'])) {
    header("Location: manage-admins.php");
    exit();
}

$id = intval($_GET['id']);

// Prevent Super Admin from deleting themselves
if ($id == $_SESSION['admin_id']) {
    echo "
    <script>
        alert('You cannot delete your own account.');
        window.location='manage-admins.php';
    </script>";
    exit();
}

// Check whether administrator exists
$stmt = $conn->prepare("SELECT fullname FROM admins WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "
    <script>
        alert('Administrator not found.');
        window.location='manage-admins.php';
    </script>";
    exit();
}

$admin = $result->fetch_assoc();

// Delete administrator
$stmt = $conn->prepare("DELETE FROM admins WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    echo "
    <script>
        alert('Administrator deleted successfully.');
        window.location='manage-admins.php';
    </script>";

} else {

    echo "
    <script>
        alert('Unable to delete administrator.');
        window.location='manage-admins.php';
    </script>";

}
?>