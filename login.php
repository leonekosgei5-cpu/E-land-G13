<?php

session_start();
require_once __DIR__ . '/db.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo "<script>alert('Please enter both email and password.'); window.location='login.html';</script>";
    exit();
}

try {
    $user = mongo_find_one('users', ['email' => $email]);
} catch (MongoDB\Driver\Exception\MongoDBException $e) {
    $user = null;
}

if ($user && isset($user->password) && password_verify($password, $user->password)) {
    $_SESSION['user_id'] = (string) $user->_id;
    $_SESSION['user_name'] = $user->firstname ?? '';
    header("Location: my-land.php");
    exit();
}

echo "<script>alert('Incorrect credentials'); window.location='login.html';</script>";
?>