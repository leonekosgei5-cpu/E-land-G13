<?php
require_once __DIR__ . '/mongodb.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit();
}

function request_value(string $key): ?string
{
    return isset($_POST[$key]) ? trim((string) $_POST[$key]) : null;
}

$firstname = request_value('firstname');
$middlename = request_value('middlename');
$lastname = request_value('lastname');
$doctype = request_value('doctype');
$docnumber = request_value('docnumber');
$email = request_value('email');
$phone = request_value('phone');
$accounttype = request_value('accounttype');
$passwordPlain = request_value('password');
$confirmPassword = request_value('confirmpassword');

if ($passwordPlain === null || $confirmPassword === null || $passwordPlain !== $confirmPassword) {
    echo "<script>alert('Passwords do not match.'); window.location='register.html';</script>";
    exit();
}

if (empty($email) && empty($phone)) {
    echo "<script>alert('Please provide either an email address or a phone number.'); window.location='register.html';</script>";
    exit();
}

if (!empty($email)) {
    try {
        $existingUser = mongo_find_one('users', ['email' => $email]);
    } catch (MongoDB\Driver\Exception\MongoDBException $e) {
        $existingUser = null;
    }

    if ($existingUser) {
        echo "<script>alert('An account with this email already exists.'); window.location='register.html';</script>";
        exit();
    }
}

$frontName = '';
if (!empty(request_value('frontCloudinaryUrl'))) {
    $frontName = request_value('frontCloudinaryUrl');
} elseif (isset($_FILES['frontDocument']) && $_FILES['frontDocument']['error'] === UPLOAD_ERR_OK) {
    $frontName = time() . '_' . basename($_FILES['frontDocument']['name']);
    move_uploaded_file($_FILES['frontDocument']['tmp_name'], __DIR__ . '/uploads/' . $frontName);
}

$backName = '';
if (!empty(request_value('backCloudinaryUrl'))) {
    $backName = request_value('backCloudinaryUrl');
} elseif (isset($_FILES['backDocument']) && $_FILES['backDocument']['error'] === UPLOAD_ERR_OK) {
    $backName = time() . '_back_' . basename($_FILES['backDocument']['name']);
    move_uploaded_file($_FILES['backDocument']['tmp_name'], __DIR__ . '/uploads/' . $backName);
}

$faceName = '';
if (!empty(request_value('faceCloudinaryUrl'))) {
    $faceName = request_value('faceCloudinaryUrl');
} elseif (isset($_FILES['faceCapture']) && $_FILES['faceCapture']['error'] === UPLOAD_ERR_OK) {
    $faceName = time() . '_face_' . basename($_FILES['faceCapture']['name']);
    move_uploaded_file($_FILES['faceCapture']['tmp_name'], __DIR__ . '/uploads/' . $faceName);
}

$passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

$userDocument = [
    'firstname' => $firstname,
    'middlename' => $middlename,
    'lastname' => $lastname,
    'doctype' => $doctype,
    'docnumber' => $docnumber,
    'email' => $email,
    'phone' => $phone,
    'accounttype' => $accounttype,
    'password' => $passwordHash,
    'front_document' => $frontName,
    'back_document' => $backName,
    'face_photo' => $faceName,
    'created_at' => new MongoDB\BSON\UTCDateTime(),
];

try {
    mongo_insert_one('users', $userDocument);
    echo "<script>alert('Registration successful. Please login.'); window.location='login.html';</script>";
    exit();
} catch (MongoDB\Driver\Exception\MongoDBException $e) {
    echo "<script>alert('Registration failed. Please try again later.'); window.location='register.html';</script>";
    exit();
}
?>