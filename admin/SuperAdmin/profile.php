<?php
session_start();
require_once("../../db.php");

if (!isset($_SESSION['admin_id']) || ($_SESSION['admin_role'] ?? '') !== 'Super Admin') {
    header("Location: login.php");
    exit();
}

$adminId = (int) $_SESSION['admin_id'];
$message = '';
$messageType = '';

// Load current profile
$stmt = mysqli_prepare($conn, "SELECT first_name, last_name, email, phone, username, role, status FROM administrators WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    header("Location: logout.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($username)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } else {
        $duplicateCheck = mysqli_prepare($conn, "SELECT id FROM administrators WHERE (username = ? OR email = ?) AND id != ? LIMIT 1");
        mysqli_stmt_bind_param($duplicateCheck, "ssi", $username, $email, $adminId);
        mysqli_stmt_execute($duplicateCheck);
        $duplicateResult = mysqli_stmt_get_result($duplicateCheck);

        if (mysqli_num_rows($duplicateResult) > 0) {
            $message = 'That username or email is already in use.';
            $messageType = 'error';
        } else {
            if ($password !== '') {
                if (strlen($password) < 8) {
                    $message = 'Password must be at least 8 characters long.';
                    $messageType = 'error';
                } elseif ($password !== $confirm_password) {
                    $message = 'Passwords do not match.';
                    $messageType = 'error';
                }
            }

            if ($message === '') {
                if ($password !== '') {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateSql = "UPDATE administrators SET first_name = ?, last_name = ?, email = ?, phone = ?, username = ?, password = ? WHERE id = ?";
                    $updateStmt = mysqli_prepare($conn, $updateSql);
                    mysqli_stmt_bind_param($updateStmt, "ssssssi", $first_name, $last_name, $email, $phone, $username, $hashedPassword, $adminId);
                } else {
                    $updateSql = "UPDATE administrators SET first_name = ?, last_name = ?, email = ?, phone = ?, username = ? WHERE id = ?";
                    $updateStmt = mysqli_prepare($conn, $updateSql);
                    mysqli_stmt_bind_param($updateStmt, "sssssi", $first_name, $last_name, $email, $phone, $username, $adminId);
                }

                if (mysqli_stmt_execute($updateStmt)) {
                    $_SESSION['admin_name'] = $first_name;
                    $message = 'Your profile was updated successfully.';
                    $messageType = 'success';
                    $admin['first_name'] = $first_name;
                    $admin['last_name'] = $last_name;
                    $admin['email'] = $email;
                    $admin['phone'] = $phone;
                    $admin['username'] = $username;
                } else {
                    $message = 'Unable to update your profile right now.';
                    $messageType = 'error';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #233142;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100%;
            background: linear-gradient(180deg, #1f3c5b 0%, #16324a 100%);
            color: white;
            padding: 24px 16px;
        }
        .sidebar h2 { text-align: center; margin-bottom: 28px; }
        .sidebar a {
            display: block;
            padding: 12px 14px;
            margin-bottom: 8px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .sidebar a:hover { background: rgba(255,255,255,0.14); }
        .main {
            margin-left: 250px;
            padding: 30px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .card {
            background: white;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            padding: 24px;
            max-width: 900px;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: bold;
        }
        .alert-success { background: #e8f5ef; color: #147d3b; }
        .alert-error { background: #fdeceb; color: #c0392b; }
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 12px;
        }
        .btn {
            background: #1f3c5b;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn:hover { background: #16324a; }
        .muted { color: #64748b; margin-top: 6px; }
        @media (max-width: 800px) {
            .sidebar { position: static; width: 100%; height: auto; }
            .main { margin-left: 0; padding: 16px; }
            .profile-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2><i class="fas fa-landmark"></i> E-Land</h2>
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="create-admin.php"><i class="fas fa-user-plus"></i> Add Administrator</a>
        <a href="view-admin.php"><i class="fas fa-users-cog"></i> Manage Administrators</a>
        <a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main">
        <div class="topbar">
            <div>
                <h1>My Profile</h1>
                <p>Update your personal information and account security.</p>
            </div>
        </div>

        <div class="card">
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="profile-grid">
                    <div>
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($admin['first_name']); ?>" required>

                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($admin['last_name']); ?>" required>

                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    <div>
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>

                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>

                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">

                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Leave blank to keep current password">
                    </div>
                </div>

                <div class="muted">
                    Role: <strong><?php echo htmlspecialchars($admin['role']); ?></strong> &nbsp;|&nbsp; Status: <strong><?php echo htmlspecialchars($admin['status']); ?></strong>
                </div>
                <br>
                <button type="submit" class="btn"><i class="fas fa-save"></i> Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>
