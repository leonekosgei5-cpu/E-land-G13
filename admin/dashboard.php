<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$ownersCount = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM owners"));
$landCount = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM land_records"));
$caseCount = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM land_cases"));
$restrictionCount = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM restrictions"));
$verificationCount = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM verification_logs"));
$pendingCases = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM land_cases WHERE status = 'Pending'"));
$leaseRequestCount = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM lease_requests"));
$pendingLeaseRequests = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM lease_requests WHERE status = 'Pending'"));
$purchaseRequestCount = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM purchase_requests"));
$pendingPurchaseRequests = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM purchase_requests WHERE status = 'Pending'"));
$approvedLeaseRequests = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM lease_requests WHERE status = 'Approved'"));
$approvedPurchaseRequests = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM purchase_requests WHERE status = 'Approved'"));

$recentLands = mysqli_query($conn, "SELECT title_number, owner, status, created_at FROM land_records ORDER BY created_at DESC LIMIT 5");
$recentLogs = mysqli_query($conn, "SELECT title_number, user_id AS user_name, verification_result AS recommendation, verification_date FROM verification_logs ORDER BY verification_date DESC LIMIT 5");
$recentLeaseRequests = mysqli_query($conn, "SELECT id, title_number, full_name, status, created_at FROM lease_requests ORDER BY created_at DESC LIMIT 5");
$recentPurchaseRequests = mysqli_query($conn, "SELECT id, title_number, full_name, status, created_at FROM purchase_requests ORDER BY created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard | E-Land</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --surface: #ffffff;
            --text: #172033;
            --muted: #6b7280;
            --primary: #176b4c;
            --primary-2: #2c9c6b;
            --accent: #f59e0b;
            --danger: #dc2626;
            --border: #e5e7eb;
            --shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #f8fbff 0%, var(--bg) 100%);
            color: var(--text);
        }

        a { text-decoration: none; color: inherit; }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 270px;
            height: 100%;
            background: linear-gradient(180deg, var(--primary) 0%, #113d2d 100%);
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

        .brand h2 {
            margin: 0;
            font-size: 1.15rem;
        }

        .brand p {
            margin: 3px 0 0;
            font-size: 0.85rem;
            color: rgba(255,255,255,0.78);
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 6px;
        }

        .sidebar li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            color: white;
            transition: all 0.2s ease;
        }

        .sidebar li a:hover,
        .sidebar li a.active {
            background: rgba(255,255,255,0.16);
            transform: translateX(2px);
        }

        .main {
            margin-left: 270px;
            padding: 28px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface);
            padding: 24px 26px;
            border-radius: 24px;
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }

        .eyebrow {
            display: inline-block;
            background: #ecfdf3;
            color: var(--primary);
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .topbar h1 {
            margin: 0 0 8px;
            font-size: 1.6rem;
        }

        .subtext {
            margin: 0;
            color: var(--muted);
            max-width: 780px;
        }

        .topbar-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .today-pill {
            background: #f4f7fb;
            padding: 10px 14px;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 600;
            color: #374151;
        }

        .logout-btn {
            background: var(--danger);
            color: white;
            padding: 10px 16px;
            border-radius: 999px;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 18px;
            margin-bottom: 22px;
        }

        .stat-card {
            background: var(--surface);
            padding: 18px 20px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: "";
            position: absolute;
            right: -12px;
            top: -12px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(23, 107, 76, 0.08);
        }

        .stat-card h3 {
            margin: 0 0 8px;
            font-size: 0.95rem;
            color: var(--muted);
        }

        .stat-card .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text);
        }

        .stat-card.primary { border-color: #dcfce7; }
        .stat-card.secondary { border-color: #dbeafe; }
        .stat-card.warning { border-color: #fef3c7; }
        .stat-card.success { border-color: #e0f2fe; }

        .content-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .panel {
            background: var(--surface);
            border-radius: 22px;
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .panel-header h3 {
            margin: 0;
            font-size: 1.05rem;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .action-card {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 14px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid var(--border);
            transition: 0.2s ease;
        }

        .action-card:hover {
            transform: translateY(-2px);
            border-color: var(--primary);
        }

        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: white;
            font-size: 18px;
        }

        .action-card h4 {
            margin: 0 0 4px;
            font-size: 0.95rem;
        }

        .action-card p {
            margin: 0;
            color: var(--muted);
            font-size: 0.82rem;
        }

        .health-list {
            display: grid;
            gap: 12px;
        }

        .health-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eef2f7;
        }

        .health-item:last-child { border-bottom: 0; }

        .health-item strong { color: var(--primary); }

        .report-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .report-card {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px;
        }

        .report-card h4 {
            margin: 0 0 4px;
            font-size: 0.95rem;
        }

        .report-card p {
            margin: 0;
            color: var(--primary);
            font-size: 1.25rem;
            font-weight: 700;
        }

        .report-card span {
            display: inline-block;
            margin-top: 6px;
            color: var(--muted);
            font-size: 0.82rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .status-pill.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-pill.approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-pill.rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.92rem;
        }

        th, td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #eef2f7;
        }

        th {
            color: var(--muted);
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .empty-state {
            padding: 18px 0;
            color: var(--muted);
            text-align: center;
        }

        @media (max-width: 980px) {
            .sidebar { width: 100%; height: auto; position: relative; }
            .main { margin-left: 0; padding: 16px; }
            .content-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .topbar { flex-direction: column; align-items: flex-start; gap: 14px; }
            .action-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
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
            <li><a class="active" href="dashboard.php">� Reports</a></li>
            <li><a href="project_activities.php">🧭 Project Activities</a></li>
            <li><a href="lease-requests.php">🏠 Lease Requests</a></li>
            <li><a href="purchase-requests.php">🏷️ Purchase Requests</a></li>
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
                <span class="eyebrow">Land project control center</span>
                <h1>Reports dashboard</h1>
                <p class="subtext">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator'); ?></strong>. Review land activity, request volume, and verification progress from one operational overview.</p>
            </div>
            <div class="topbar-actions">
                <div class="today-pill">Today: <?php echo date("d M Y"); ?></div>
                <a class="logout-btn" href="logout.php">Logout</a>
            </div>
        </header>

        <section class="stats-grid">
            <article class="stat-card primary">
                <h3>Owners</h3>
                <div class="value"><?php echo $ownersCount; ?></div>
            </article>
            <article class="stat-card secondary">
                <h3>Land Records</h3>
                <div class="value"><?php echo $landCount; ?></div>
            </article>
            <article class="stat-card warning">
                <h3>Open Cases</h3>
                <div class="value"><?php echo $caseCount; ?></div>
            </article>
            <article class="stat-card success">
                <h3>Restrictions</h3>
                <div class="value"><?php echo $restrictionCount; ?></div>
            </article>
            <article class="stat-card primary">
                <h3>Lease Requests</h3>
                <div class="value"><?php echo $leaseRequestCount; ?></div>
            </article>
            <article class="stat-card primary">
                <h3>Purchase Requests</h3>
                <div class="value"><?php echo $purchaseRequestCount; ?></div>
            </article>
            <article class="stat-card primary">
                <h3>Verifications</h3>
                <div class="value"><?php echo $verificationCount; ?></div>
            </article>
        </section>

        <section class="content-grid">
            <div class="panel">
                <div class="panel-header">
                    <h3>Operational snapshot</h3>
                </div>
                <div class="report-grid">
                    <div class="report-card">
                        <h4>Pending lease requests</h4>
                        <p><?php echo $pendingLeaseRequests; ?></p>
                        <span>Awaiting review</span>
                    </div>
                    <div class="report-card">
                        <h4>Pending purchase requests</h4>
                        <p><?php echo $pendingPurchaseRequests; ?></p>
                        <span>Needs admin decision</span>
                    </div>
                    <div class="report-card">
                        <h4>Approved leases</h4>
                        <p><?php echo $approvedLeaseRequests; ?></p>
                        <span>Accepted requests</span>
                    </div>
                    <div class="report-card">
                        <h4>Approved purchases</h4>
                        <p><?php echo $approvedPurchaseRequests; ?></p>
                        <span>Completed transactions</span>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h3>Project health</h3>
                </div>
                <div class="health-list">
                    <div class="health-item">
                        <span>Active land records</span>
                        <strong><?php echo $landCount; ?></strong>
                    </div>
                    <div class="health-item">
                        <span>Pending court cases</span>
                        <strong><?php echo $pendingCases; ?></strong>
                    </div>
                    <div class="health-item">
                        <span>Current restrictions</span>
                        <strong><?php echo $restrictionCount; ?></strong>
                    </div>
                    <div class="health-item">
                        <span>Verification activity</span>
                        <strong><?php echo $verificationCount; ?></strong>
                    </div>
                    <div class="health-item">
                        <span>Pending lease requests</span>
                        <strong><?php echo $pendingLeaseRequests; ?></strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-grid">
            <div class="panel">
                <div class="panel-header">
                    <h3>Quick actions</h3>
                </div>
                <div class="action-grid">
                    <a class="action-card" href="add_land.php">
                        <div class="action-icon">➕</div>
                        <div>
                            <h4>Add land record</h4>
                            <p>Create a new parcel entry</p>
                        </div>
                    </a>
                    <a class="action-card" href="ownership_transfer.php">
                        <div class="action-icon">🔁</div>
                        <div>
                            <h4>Transfer ownership</h4>
                            <p>Record land transfer</p>
                        </div>
                    </a>
                    <a class="action-card" href="upload_survey.php">
                        <div class="action-icon">📝</div>
                        <div>
                            <h4>Upload survey plan</h4>
                            <p>Attach survey documents</p>
                        </div>
                    </a>
                    <a class="action-card" href="verification_logs.php">
                        <div class="action-icon">✅</div>
                        <div>
                            <h4>Review verifications</h4>
                            <p>Inspect recent checks</p>
                        </div>
                    </a>
                    <a class="action-card" href="lease-requests.php">
                        <div class="action-icon">🏠</div>
                        <div>
                            <h4>Review lease requests</h4>
                            <p>Approve or reject user leases</p>
                        </div>
                    </a>
                    <a class="action-card" href="purchase-requests.php">
                        <div class="action-icon">🏷️</div>
                        <div>
                            <h4>Review purchase requests</h4>
                            <p>Approve or reject land purchases</p>
                        </div>
                    </a>
                </div>
            </div>

        </section>

        <section class="content-grid">
            <div class="panel">
                <div class="panel-header">
                    <h3>Recent land records</h3>
                </div>
                <?php if (mysqli_num_rows($recentLands) > 0) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title number</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($recentLands)) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['owner']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['created_at']))); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="empty-state">No land records have been added yet.</div>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h3>Latest verification logs</h3>
                </div>
                <?php if (mysqli_num_rows($recentLogs) > 0) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>By</th>
                                <th>Recommendation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($recentLogs)) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_name'] ?? 'System'); ?></td>
                                    <td><?php echo htmlspecialchars($row['recommendation'] ?? 'Pending'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="empty-state">No verification activity yet.</div>
                <?php endif; ?>
            </div>
        </section>

        <section class="content-grid">
            <div class="panel">
                <div class="panel-header">
                    <h3>Recent lease requests</h3>
                </div>
                <?php if (mysqli_num_rows($recentLeaseRequests) > 0) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Applicant</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($recentLeaseRequests)) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><span class="status-pill <?php echo strtolower($row['status'] ?? 'pending'); ?>"><?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?></span></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['created_at']))); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="empty-state">No recent lease requests.</div>
                <?php endif; ?>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h3>Recent purchase requests</h3>
                </div>
                <?php if (mysqli_num_rows($recentPurchaseRequests) > 0) : ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Applicant</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($recentPurchaseRequests)) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title_number']); ?></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td><span class="status-pill <?php echo strtolower($row['status'] ?? 'pending'); ?>"><?php echo htmlspecialchars($row['status'] ?? 'Pending'); ?></span></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($row['created_at']))); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="empty-state">No recent purchase requests.</div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>