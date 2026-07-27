<?php
session_start();
include '../db.php';
mysqli_set_charset($conn, 'utf8mb4');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$active_query = "SELECT * FROM purchase_requests WHERE status IN ('Pending', 'Approved') ORDER BY created_at DESC";
$active_result = mysqli_query($conn, $active_query);
$purchase_requests = $active_result ? mysqli_fetch_all($active_result, MYSQLI_ASSOC) : [];

$rejected_query = "SELECT * FROM purchase_requests WHERE status = 'Rejected' ORDER BY updated_at DESC";
$rejected_result = mysqli_query($conn, $rejected_query);
$rejected_requests = $rejected_result ? mysqli_fetch_all($rejected_result, MYSQLI_ASSOC) : [];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $request_id = intval($_POST['request_id']);
    $note = trim($_POST['admin_note'] ?? '');

    if ($action === 'approve') {
        $status = 'Approved';
        $message = 'Purchase request approved successfully.';
        $stmt = mysqli_prepare($conn, 'UPDATE purchase_requests SET status = ?, admin_notified = 1, admin_note = ? WHERE id = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssi', $status, $note, $request_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    } elseif ($action === 'reject') {
        $status = 'Rejected';
        $message = 'Purchase request rejected.';
        $stmt = mysqli_prepare($conn, 'UPDATE purchase_requests SET status = ?, admin_note = ? WHERE id = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ssi', $status, $note, $request_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }

    $active_result = mysqli_query($conn, $active_query);
    $purchase_requests = $active_result ? mysqli_fetch_all($active_result, MYSQLI_ASSOC) : [];
    $rejected_result = mysqli_query($conn, $rejected_query);
    $rejected_requests = $rejected_result ? mysqli_fetch_all($rejected_result, MYSQLI_ASSOC) : [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Purchase Requests - Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css" />
    <style>
        :root { --bg:#f4f8f5; --surface:#fff; --primary:#17653c; --primary-dark:#0f4a2c; --muted:#64706a; --border:#e4eee7; --shadow:0 14px 34px rgba(17, 67, 40, 0.08); }
        * { box-sizing: border-box; }
        body { margin:0; background:linear-gradient(135deg, #f8fcf9 0%, var(--bg) 100%); color:#123524; font-family:'Segoe UI', Arial, sans-serif; }
        .sidebar { position:fixed; inset:0 auto 0 0; width:270px; background:linear-gradient(180deg, var(--primary-dark) 0%, #113d2d 100%); color:#fff; padding:24px 18px; }
        .brand { display:flex; align-items:center; gap:12px; margin-bottom:24px; }
        .brand-icon { width:44px; height:44px; border-radius:12px; background:rgba(255,255,255,0.16); display:grid; place-items:center; font-size:1.3rem; }
        .sidebar ul { list-style:none; padding:0; margin:0; display:grid; gap:8px; }
        .sidebar a { color:#fff; text-decoration:none; padding:10px 12px; display:block; border-radius:10px; }
        .sidebar a.active, .sidebar a:hover { background:rgba(255,255,255,0.16); }
        .main { margin-left:270px; padding:24px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; }
        .eyebrow { text-transform:uppercase; letter-spacing:0.08em; color:#5f7b66; font-size:0.78rem; font-weight:700; }
        .panel { background:var(--surface); border:1px solid var(--border); border-radius:18px; padding:20px; box-shadow:var(--shadow); margin-bottom:20px; }
        .alert { background:#f2fdf5; color:#17653c; padding:12px 14px; border-radius:12px; border:1px solid #caefd4; margin-bottom:16px; }
        .requests-table { width:100%; border-collapse:collapse; background:var(--surface); }
        .requests-table th, .requests-table td { padding:12px 14px; border-bottom:1px solid var(--border); text-align:left; vertical-align:top; }
        .requests-table th { background:#f7fcf8; color:var(--primary-dark); font-size:0.82rem; text-transform:uppercase; letter-spacing:0.04em; }
        .status-badge { display:inline-block; padding:0.35rem 0.7rem; border-radius:999px; font-size:0.85rem; font-weight:600; }
        .status-pending { background:#fff3cd; color:#8a6d3b; }
        .status-approved { background:#e8f7ee; color:#1f6f3f; }
        .status-rejected { background:#fde8e8; color:#b91c1c; }
        .btn-small { border:none; border-radius:999px; padding:0.5rem 0.8rem; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; }
        .btn-approve { background:#17653c; color:#fff; }
        .btn-reject { background:#b91c1c; color:#fff; }
        .btn-view { background:#eef8f1; color:#17653c; }
        textarea { width:100%; border:1px solid var(--border); border-radius:10px; padding:0.7rem; min-height:84px; }
        @media (max-width: 980px) { .sidebar { position:relative; width:100%; height:auto; } .main { margin-left:0; padding:16px; } }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="brand">
        <div class="brand-icon">🏞️</div>
        <div>
            <h2>E-Land</h2>
            <p>Admin console</p>
        </div>
    </div>
    <ul>
        <li><a href="dashboard.php">📊 Dashboard</a></li>
        <li><a href="lease-requests.php">🏠 Lease Requests</a></li>
        <li><a class="active" href="purchase-requests.php">🏷️ Purchase Requests</a></li>
        <li><a href="view_land.php">🗺️ Land Records</a></li>
        <li><a href="logout.php">↩️ Logout</a></li>
    </ul>
</div>
<div class="main">
    <header class="topbar">
        <div>
            <div class="eyebrow">Land purchase control center</div>
            <h1>Purchase Requests</h1>
            <p>Approve or reject buyer purchase requests for listed properties.</p>
        </div>
    </header>

    <?php if (!empty($message)): ?>
        <div class="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="panel">
        <h2 style="margin:0 0 12px; color:var(--primary-dark);">Pending and Approved Requests</h2>
        <?php if (count($purchase_requests) > 0): ?>
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Buyer</th>
                        <th>Property</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($purchase_requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['full_name'] ?? '—'); ?><br><small><?php echo htmlspecialchars($request['email'] ?? '—'); ?></small></td>
                            <td><?php echo htmlspecialchars($request['title_number'] ?? '—'); ?><br><small><?php echo htmlspecialchars($request['preferred_payment_plan'] ?? '—'); ?></small></td>
                            <td><?php echo htmlspecialchars($request['phone'] ?? '—'); ?><br><small><?php echo htmlspecialchars($request['national_id_passport'] ?? '—'); ?></small></td>
                            <td><span class="status-badge status-<?php echo strtolower($request['status'] ?? 'pending'); ?>"><?php echo htmlspecialchars($request['status'] ?? 'Pending'); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($request['created_at'])); ?></td>
                            <td>
                                <form method="post" style="display:grid; gap:8px;">
                                    <input type="hidden" name="request_id" value="<?php echo (int) $request['id']; ?>" />
                                    <textarea name="admin_note" placeholder="Add approval or rejection note"><?php echo htmlspecialchars($request['admin_note'] ?? ''); ?></textarea>
                                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                        <button type="submit" name="action" value="approve" class="btn-small btn-approve" onclick="return confirm('Approve this purchase request?');">Approve</button>
                                        <button type="submit" name="action" value="reject" class="btn-small btn-reject" onclick="return confirm('Reject this purchase request?');">Reject</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No purchase requests found.</p>
        <?php endif; ?>
    </div>

    <div class="panel">
        <h2 style="margin:0 0 12px; color:var(--primary-dark);">Rejected Requests</h2>
        <?php if (count($rejected_requests) > 0): ?>
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Buyer</th>
                        <th>Property</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rejected_requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['full_name'] ?? '—'); ?><br><small><?php echo htmlspecialchars($request['email'] ?? '—'); ?></small></td>
                            <td><?php echo htmlspecialchars($request['title_number'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($request['phone'] ?? '—'); ?></td>
                            <td><span class="status-badge status-rejected">Rejected</span></td>
                            <td><?php echo date('d M Y', strtotime($request['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No rejected requests.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
