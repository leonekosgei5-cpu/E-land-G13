<?php
session_start();
include "../db.php";

// Check if Super Admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] != "Super Admin") {
    header("Location: login.php");
    exit();
}

// Check form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = intval($_POST['id']);
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    // Check if username already exists
    $check = $conn->prepare("SELECT id FROM admins WHERE username=? AND id!=?");
    $check->bind_param("si", $username, $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {

        echo "
        <script>
        alert('Username already exists.');
        window.history.back();
        </script>";

        exit();

    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM admins WHERE email=? AND id!=?");
    $check->bind_param("si", $email, $id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {

        echo "
        <script>
        alert('Email already exists.');
        window.history.back();
        </script>";

        exit();

    }

    // If password is empty keep existing one
    if (empty($password)) {

        $stmt = $conn->prepare("
        UPDATE admins
        SET
        fullname=?,
        username=?,
        email=?,
        role=?
        WHERE id=?
        ");

        $stmt->bind_param(
            "ssssi",
            $fullname,
            $username,
            $email,
            $role,
            $id
        );

    } else {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
        UPDATE admins
        SET
        fullname=?,
        username=?,
        email=?,
        password=?,
        role=?
        WHERE id=?
        ");

        $stmt->bind_param(
            "sssssi",
            $fullname,
            $username,
            $email,
            $hashedPassword,
            $role,
            $id
        );

    }

    if ($stmt->execute()) {

        echo "
        <script>
        alert('Administrator updated successfully.');
        window.location='manage-admins.php';
        </script>";

    } else {

        echo "
        <script>
        alert('Update failed.');
        window.history.back();
        </script>";

    }

} else {

    header("Location: manage-admins.php");
    exit();

}
?>