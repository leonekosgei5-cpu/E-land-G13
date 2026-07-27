<?php
session_start();
require_once("../../db.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

function safeCount($conn, $table, $condition = '') {
    $sql = "SELECT COUNT(*) AS total FROM `$table`";
    if ($condition !== '') {
        $sql .= " WHERE $condition";
    }

    $result = mysqli_query($conn, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return (int) $row['total'];
    }

    return 0;
}

$userCount = safeCount($conn, 'users');
$landCount = safeCount($conn, 'land_records');
$adminCount = safeCount($conn, 'administrators');
$caseCount = safeCount($conn, 'land_cases');
$activeAdminCount = safeCount($conn, 'administrators', "status = 'Active'");

$recentAdmins = mysqli_query($conn, "SELECT first_name, last_name, username, role, status, created_at FROM administrators ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard</title>
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
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background: linear-gradient(180deg, #1f3c5b 0%, #16324a 100%);
            color: white;
            padding: 24px 16px;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 28px;
            font-size: 24px;
        }
        .sidebar a {
            display: block;
            padding: 12px 14px;
            margin-bottom: 8px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.2s ease;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.14);
        }
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
        .topbar h1 {
            margin: 0;
            font-size: 28px;
        }
        .topbar .pill {
            background: #e8f5ef;
            color: #147d3b;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: bold;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }
        .card {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
        }
        .card i {
            font-size: 24px;
            margin-bottom: 12px;
            color: #2f80ed;
        }
        .card h2 {
            margin: 0 0 8px;
            font-size: 30px;
        }
        .card p {
            margin: 0;
            color: #64748b;
        }
        .panel {
            background: white;
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            margin-bottom: 24px;
        }
        .panel h3 { margin-top: 0; }
        .actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }
        .action-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            background: #fafcff;
        }
        .action-card h4 { margin: 0 0 8px; }
        .action-card p { color: #64748b; margin: 0 0 12px; }
        .action-card a {
            display: inline-block;
            text-decoration: none;
            background: #1f3c5b;
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        th { background: #f8fafc; }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active { background: #e8f5ef; color: #147d3b; }
        .status-suspended { background: #fdeceb; color: #c0392b; }
        @media (max-width: 980px) {
            .cards, .actions { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 680px) {
            .sidebar { position: static; width: 100%; height: auto; }
            .main { margin-left: 0; padding: 16px; }
            .cards, .actions { grid-template-columns: 1fr; }
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
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></h1>
                <p>Super admin control center for administrators and system oversight.</p>
            </div>
            <div class="pill">Role: Super Admin</div>
        </div>

        <div class="cards">
            <div class="card">
                <i class="fas fa-users"></i>
                <h2><?php echo $userCount; ?></h2>
                <p>Registered Users</p>
            </div>
            <div class="card">
                <i class="fas fa-map-marked-alt"></i>
                <h2><?php echo $landCount; ?></h2>
                <p>Land Records</p>
            </div>
            <div class="card">
                <i class="fas fa-user-shield"></i>
                <h2><?php echo $adminCount; ?></h2>
                <p>Administrators</p>
            </div>
            <div class="card">
                <i class="fas fa-gavel"></i>
                <h2><?php echo $caseCount; ?></h2>
                <p>Land Cases</p>
            </div>
        </div>

        <div class="panel">
            <h3>Quick Actions</h3>
            <div class="actions">
                <div class="action-card">
                    <h4>Create new admin</h4>
                    <p>Register a new administrator who can help manage users and records.</p>
                    <a href="create-admin.php">Open Form</a>
                </div>
                <div class="action-card">
                    <h4>Review administrators</h4>
                    <p>Monitor active and suspended administrators from a single screen.</p>
                    <a href="view-admin.php">View List</a>
                </div>
                <div class="action-card">
                    <h4>System overview</h4>
                    <p>Track platform activity with key totals and recent admin activity.</p>
                    <a href="#recent-admins">See Activity</a>
                </div>
            </div>
        </div>

        <div class="panel" id="recent-admins">
            <h3>Recent Administrators</h3>
            <p>Latest accounts created or updated by the system.</p>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($recentAdmins) > 0): ?>
                        <?php while ($admin = mysqli_fetch_assoc($recentAdmins)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                <td><?php echo htmlspecialchars($admin['role']); ?></td>
                                <td>
                                    <span class="status <?php echo ($admin['status'] === 'Active') ? 'status-active' : 'status-suspended'; ?>">
                                        <?php echo htmlspecialchars($admin['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($admin['created_at']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5">No administrator records found yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>