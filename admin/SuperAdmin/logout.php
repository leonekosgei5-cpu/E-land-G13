<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Destroy the session
session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f4f7fb, #e8eef7);
            color: #233142;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            background: white;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
            text-align: center;
            max-width: 440px;
            width: 90%;
        }
        .icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: #e8f5ef;
            color: #147d3b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }
        h2 { margin: 0 0 10px; }
        p { color: #64748b; margin-bottom: 20px; }
        a {
            display: inline-block;
            text-decoration: none;
            background: #1f3c5b;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            font-weight: bold;
        }
        a:hover { background: #16324a; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><i class="fas fa-sign-out-alt"></i></div>
        <h2>You have been logged out</h2>
        <p>Thank you for using the E-Land super admin panel. You can sign in again anytime.</p>
        <a href="login.php">Go to Login</a>
    </div>
</body>
</html>
