<?php
session_start();
include '../db.php';
mysqli_set_charset($conn, 'utf8mb4');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch active and rejected lease requests
$active_query = "SELECT lr.*, lr.id as request_id FROM lease_requests lr WHERE lr.status IN ('Pending', 'Approved') ORDER BY lr.created_at DESC";
$active_result = mysqli_query($conn, $active_query);
$lease_requests = $active_result ? mysqli_fetch_all($active_result, MYSQLI_ASSOC) : [];

$rejected_query = "SELECT * FROM lease_rejected_requests ORDER BY rejected_at DESC";
$rejected_result = mysqli_query($conn, $rejected_query);
$rejected_requests = $rejected_result ? mysqli_fetch_all($rejected_result, MYSQLI_ASSOC) : [];

$message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $request_id = intval($_POST['request_id']);
    $note = trim($_POST['admin_note'] ?? '');

    if ($action === 'approve') {
        $status = 'Approved';
        $message = 'Lease request approved successfully.';

        $stmt = mysqli_prepare($conn, 'UPDATE lease_requests SET status = ?, admin_notified = 1, updated_at = NOW() WHERE id = ?');
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'si', $status, $request_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        if (!empty($note)) {
            $note_stmt = mysqli_prepare($conn, 'UPDATE lease_requests SET admin_note = ? WHERE id = ?');
            if ($note_stmt) {
                mysqli_stmt_bind_param($note_stmt, 'si', $note, $request_id);
                mysqli_stmt_execute($note_stmt);
                mysqli_stmt_close($note_stmt);
            }
        }
    } elseif ($action === 'reject') {
        $message = 'Lease request moved to rejected requests.';

        $fetch_stmt = mysqli_prepare($conn, 'SELECT id, user_id, title_number, full_name, email, phone, national_id_passport, proof_of_income, landlord_references, security_deposit_amount, lease_duration, intended_use, budget, admin_note FROM lease_requests WHERE id = ?');
        if ($fetch_stmt) {
            mysqli_stmt_bind_param($fetch_stmt, 'i', $request_id);
            mysqli_stmt_execute($fetch_stmt);
            $request_row = mysqli_stmt_get_result($fetch_stmt)->fetch_assoc();
            mysqli_stmt_close($fetch_stmt);
        }

        if ($request_row) {
            $insert_stmt = mysqli_prepare($conn, 'INSERT INTO lease_rejected_requests (request_id, user_id, title_number, full_name, email, phone, national_id_passport, proof_of_income, landlord_references, security_deposit_amount, lease_duration, intended_use, budget, rejection_reason) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            if ($insert_stmt) {
                mysqli_stmt_bind_param(
                    $insert_stmt,
                    'iissssssssssss',
                    $request_row['id'],
                    $request_row['user_id'],
                    $request_row['title_number'],
                    $request_row['full_name'],
                    $request_row['email'],
                    $request_row['phone'],
                    $request_row['national_id_passport'],
                    $request_row['proof_of_income'],
                    $request_row['landlord_references'],
                    $request_row['security_deposit_amount'],
                    $request_row['lease_duration'],
                    $request_row['intended_use'],
                    $request_row['budget'],
                    $note
                );
                mysqli_stmt_execute($insert_stmt);
                mysqli_stmt_close($insert_stmt);
            }

            $delete_stmt = mysqli_prepare($conn, 'DELETE FROM lease_requests WHERE id = ?');
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, 'i', $request_id);
                mysqli_stmt_execute($delete_stmt);
                mysqli_stmt_close($delete_stmt);
            }
        }
    } else {
        $status = 'Pending';
        $message = 'Lease request status set to pending.';
    }

    // Refresh the lists after the update
    $active_result = mysqli_query($conn, $active_query);
    $lease_requests = $active_result ? mysqli_fetch_all($active_result, MYSQLI_ASSOC) : [];

    $rejected_result = mysqli_query($conn, $rejected_query);
    $rejected_requests = $rejected_result ? mysqli_fetch_all($rejected_result, MYSQLI_ASSOC) : [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lease Requests - Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        :root {
            --bg: #f4f8f5;
            --surface: #ffffff;
            --primary: #17653c;
            --primary-dark: #0f4a2c;
            --accent: #2f9e5b;
            --text: #123524;
            --muted: #64706a;
            --border: #e4eee7;
            --shadow: 0 14px 34px rgba(17, 67, 40, 0.08);
        }

        * { box-sizing: border-box; }
        body { margin:0; background: linear-gradient(135deg, #f8fcf9 0%, var(--bg) 100%); color: var(--text); font-family: "Segoe UI", Arial, sans-serif; }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 270px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary-dark) 0%, #113d2d 100%);
            color: white;
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
            box-shadow: 12px 0 35px rgba(0, 0, 0, 0.12);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.16);
            margin-bottom: 18px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.16);
            font-size: 22px;
        }

        .brand h2 { margin: 0; font-size: 1.15rem; }
        .brand p { margin: 3px 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.78); }

        .sidebar ul { list-style: none; padding: 0; margin: 0; display: grid; gap: 6px; }
        .sidebar li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            color: white;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .sidebar li a:hover, .sidebar li a.active {
            background: rgba(255,255,255,0.16);
            transform: translateX(2px);
        }

        .main { margin-left: 270px; padding: 28px; }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface);
            padding: 22px 24px;
            border-radius: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
        .topbar h1 { margin: 0 0 6px; font-size: 1.5rem; color: var(--primary-dark); }
        .topbar p { margin: 0; color: var(--muted); }
        .topbar .pill {
            background: #ecfdf3;
            color: var(--primary);
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .admin-container { max-width: none; padding: 0; }
        .page-header { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--accent) 100%); color: #fff; padding: 1.5rem 1.4rem; margin-bottom: 1.4rem; border-radius: 18px; box-shadow: var(--shadow); }
        .page-header h1 { margin: 0; font-size: 1.7rem; }
        .page-header p { margin: 0.45rem 0 0; opacity: 0.95; }

        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; margin-bottom: 1.4rem; }
        .stat-card { background: var(--surface); padding: 1rem 1.1rem; border-radius: 16px; border: 1px solid var(--border); box-shadow: var(--shadow); }
        .stat-card h3 { margin: 0; color: var(--muted); font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.04em; }
        .stat-card .value { font-size: 1.75rem; font-weight: 700; color: var(--primary-dark); margin-top: 0.4rem; }

        .requests-table { width: 100%; border-collapse: separate; border-spacing: 0; background: var(--surface); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow); }
        .requests-table thead tr { background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%); color: #fff; }
        .requests-table thead th { padding: 0.95rem 1rem; text-align: left; font-weight: 600; font-size: 0.9rem; }
        .requests-table tbody tr { border-bottom: 1px solid var(--border); }
        .requests-table tbody tr:hover { background: #f9fdfb; }
        .requests-table tbody td { padding: 1rem; color: var(--muted); vertical-align: top; }

        .status-badge { display: inline-block; padding: 0.42rem 0.7rem; border-radius: 999px; font-size: 0.8rem; font-weight: 700; }
        .status-pending { background: #fff8e1; color: #9a6a00; }
        .status-approved { background: #eaf8ef; color: #1f7a3f; }
        .status-rejected { background: #fdeaea; color: #b42318; }

        .action-buttons { display: flex; flex-wrap: wrap; gap: 0.55rem; align-items: flex-start; }
        .btn-small { padding: 0.55rem 0.8rem; border: none; border-radius: 8px; cursor: pointer; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-block; }
        .btn-view { background: var(--primary); color: #fff; }
        .btn-view:hover { background: var(--primary-dark); }
        .btn-approve { background: #24a148; color: #fff; }
        .btn-approve:hover { background: #1c833a; }
        .btn-reject { background: #d64545; color: #fff; }
        .btn-reject:hover { background: #b83535; }

        .note-input { display:block; width:100%; margin-bottom:0.45rem; border:1px solid #d8e6dc; border-radius:8px; padding:0.6rem 0.7rem; min-width: 180px; }
        .note-input:focus { outline: 2px solid rgba(23,101,60,0.16); border-color: var(--primary); }

        .modal { display: none; position: fixed; inset: 0; background: rgba(8, 28, 19, 0.65); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
        .modal.active { display: flex; }
        .modal-content { background: #fff; border-radius: 16px; padding: 1.3rem 1.5rem; width: min(760px, 100%); max-height: 90vh; overflow-y: auto; box-shadow: 0 18px 42px rgba(0,0,0,0.2); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .modal-header h2 { margin: 0; color: var(--primary-dark); font-size: 1.2rem; }
        .close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; }

        .info-section { margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border); }
        .info-section:last-child { border-bottom: none; }
        .info-label { font-weight: 700; color: var(--primary-dark); display: inline-block; min-width: 180px; margin-top: 0.25rem; }
        .info-value { color: var(--muted); margin-left: 0.35rem; }

        .empty-state { text-align: center; padding: 2.5rem 1rem; color: var(--muted); background: var(--surface); border: 1px solid var(--border); border-radius: 16px; box-shadow: var(--shadow); }
        .empty-state p { margin: 0; font-size: 1rem; }

        @media (max-width: 980px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main { margin-left: 0; padding: 16px; }
            .topbar { flex-direction: column; align-items: flex-start; gap: 12px; }
        }

        @media (max-width: 860px) {
            .requests-table { display: block; overflow-x: auto; }
        }
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
            <li><a href="project_activities.php">🧭 Project Activities</a></li>
            <li><a class="active" href="lease-requests.php">🏠 Lease Requests</a></li>
            <li><a href="view_land.php">🗺️ Land Records</a></li>
            <li><a href="add_land.php">➕ Register Land</a></li>
            <li><a href="ownership_transfer.php">🔁 Ownership Transfer</a></li>
            <li><a href="add_case.php">⚖️ Court Cases</a></li>
            <li><a href="add_restriction.php">🚫 Restrictions</a></li>
            <li><a href="upload_survey.php">📝 Survey Plans</a></li>
            <li><a href="upload_image.php">🖼️ Land Images</a></li>
            <li><a href="verification_logs.php">✅ Verification Logs</a></li>
            <li><a href="logout.php">↩️ Logout</a></li>
        </ul>
    </div>

    <div class="main">
        <header class="topbar">
            <div>
                <h1>Lease Requests</h1>
                <p>Review, approve, or reject leasing requests from users</p>
            </div>
            <div class="pill">Admin workspace</div>
        </header>

        <div class="admin-container">
            <div class="page-header">
                <h1>Lease Requests</h1>
                <p>Manage and review all lease requests from users</p>
            </div>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <h3>Active Requests</h3>
                <div class="value"><?php echo count($lease_requests); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending</h3>
                <div class="value"><?php echo count(array_filter($lease_requests, fn($r) => $r['status'] === 'Pending')); ?></div>
            </div>
            <div class="stat-card">
                <h3>Accepted</h3>
                <div class="value"><?php echo count(array_filter($lease_requests, fn($r) => $r['status'] === 'Approved')); ?></div>
            </div>
            <div class="stat-card">
                <h3>Rejected</h3>
                <div class="value"><?php echo count($rejected_requests); ?></div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div style="background: #eaf8ef; color: #145132; border: 1px solid #ccebd6; padding: 0.9rem 1rem; border-radius: 10px; margin-bottom: 1.2rem; box-shadow: 0 6px 16px rgba(17, 67, 40, 0.06);">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div style="margin-bottom: 1.5rem;">
            <h2 style="margin-bottom: 0.6rem; color: var(--primary-dark);">Active and Accepted Requests</h2>
            <?php if (count($lease_requests) > 0): ?>
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Applicant Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Land Title</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lease_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['email']); ?></td>
                                <td><?php echo htmlspecialchars($request['phone']); ?></td>
                                <td><?php echo htmlspecialchars($request['title_number']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                                        <?php echo htmlspecialchars($request['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($request['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-small btn-view" onclick="viewDetails(<?php echo htmlspecialchars(json_encode($request, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)); ?>)">View</button>
                                        <?php if ($request['status'] === 'Pending'): ?>
                                            <form method="POST" style="display: inline-block; min-width: 220px;">
                                                <input type="hidden" name="action" value="approve">
                                                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                                <textarea class="note-input" name="admin_note" rows="2" placeholder="Optional approval note"></textarea>
                                                <button type="submit" class="btn-small btn-approve" onclick="return confirm('Approve this request?');">Approve</button>
                                            </form>
                                            <form method="POST" style="display: inline-block; min-width: 220px;">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                                <textarea class="note-input" name="admin_note" rows="2" placeholder="Optional rejection reason"></textarea>
                                                <button type="submit" class="btn-small btn-reject" onclick="return confirm('Reject this request?');">Reject</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No active or accepted lease requests found.</p>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <h2 style="margin-bottom: 0.6rem; color: var(--primary-dark);">Rejected Requests</h2>
            <?php if (count($rejected_requests) > 0): ?>
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Applicant Name</th>
                            <th>Email</th>
                            <th>Land Title</th>
                            <th>Rejected On</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rejected_requests as $rejected): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($rejected['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($rejected['email']); ?></td>
                                <td><?php echo htmlspecialchars($rejected['title_number']); ?></td>
                                <td><?php echo date('d M Y', strtotime($rejected['rejected_at'])); ?></td>
                                <td><?php echo htmlspecialchars($rejected['rejection_reason'] ?: 'No reason provided'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No rejected lease requests found.</p>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Lease Request Details</h2>
                <button type="button" class="close-btn" onclick="closeModal()">×</button>
            </div>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        function viewDetails(request) {
            const body = `
                <div class="info-section">
                    <h3 style="margin-top: 0; color: #145132;">Applicant Information</h3>
                    <p><span class="info-label">Full Name:</span><span class="info-value">${escapeHtml(request.full_name)}</span></p>
                    <p><span class="info-label">Email:</span><span class="info-value">${escapeHtml(request.email)}</span></p>
                    <p><span class="info-label">Phone:</span><span class="info-value">${escapeHtml(request.phone)}</span></p>
                    <p><span class="info-label">National ID/Passport:</span><span class="info-value">${escapeHtml(request.national_id_passport)}</span></p>
                </div>

                <div class="info-section">
                    <h3 style="margin-top: 0; color: #145132;">Land Information</h3>
                    <p><span class="info-label">Title Number:</span><span class="info-value">${escapeHtml(request.title_number)}</span></p>
                </div>

                <div class="info-section">
                    <h3 style="margin-top: 0; color: #145132;">Lease Terms</h3>
                    <p><span class="info-label">Preferred Duration:</span><span class="info-value">${escapeHtml(request.lease_duration)}</span></p>
                    <p><span class="info-label">Intended Use:</span><span class="info-value">${escapeHtml(request.intended_use)}</span></p>
                    <p><span class="info-label">Budget (KES):</span><span class="info-value">${parseFloat(request.budget).toLocaleString()}</span></p>
                    <p><span class="info-label">Security Deposit (KES):</span><span class="info-value">${parseFloat(request.security_deposit_amount).toLocaleString()}</span></p>
                </div>

                <div class="info-section">
                    <h3 style="margin-top: 0; color: #145132;">Requirements</h3>
                    <p><span class="info-label">Proof of Income/Business Registration:</span></p>
                    <p style="margin: 0.5rem 0; padding: 0.5rem; background: #f9fcfa; border-radius: 5px; color: #5d6b61;">${escapeHtml(request.proof_of_income)}</p>
                    
                    <p style="margin-top: 1rem;"><span class="info-label">Previous Landlord References:</span></p>
                    <p style="margin: 0.5rem 0; padding: 0.5rem; background: #f9fcfa; border-radius: 5px; color: #5d6b61;">${escapeHtml(request.landlord_references)}</p>
                </div>

                <div class="info-section">
                    <h3 style="margin-top: 0; color: #145132;">Request Status</h3>
                    <p><span class="info-label">Status:</span><span class="info-value">${escapeHtml(request.status)}</span></p>
                    <p><span class="info-label">Submitted:</span><span class="info-value">${new Date(request.created_at).toLocaleString()}</span></p>
                </div>
            `;
            
            document.getElementById('modalBody').innerHTML = body;
            document.getElementById('detailsModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('detailsModal').classList.remove('active');
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
