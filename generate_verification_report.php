<?php
session_start();
require_once __DIR__ . '/db.php';

$title = trim($_GET['title_number'] ?? '');
if ($title === '') {
    exit('Missing title number.');
}

$land = $conn->prepare('SELECT * FROM land_records WHERE title_number = ? LIMIT 1');
$land->bind_param('s', $title);
$land->execute();
$landRow = $land->get_result()->fetch_assoc();
$land->close();

$owner = null;
if ($landRow && !empty($landRow['owner_id'])) {
    $ownerStmt = $conn->prepare('SELECT * FROM owners WHERE id = ? LIMIT 1');
    $ownerStmt->bind_param('i', $landRow['owner_id']);
    $ownerStmt->execute();
    $owner = $ownerStmt->get_result()->fetch_assoc();
    $ownerStmt->close();
}

$historyColumns = [];
$historySchemaStmt = $conn->query('SHOW COLUMNS FROM ownership_history');
while ($column = $historySchemaStmt->fetch_assoc()) {
    $historyColumns[] = $column['Field'];
}
$historySchemaStmt->close();

$historyQuery = "SELECT title_number, owner_name AS previous_owner, '' AS new_owner, transfer_date, transfer_type, transfer_reason AS reason FROM ownership_history WHERE title_number = ? ORDER BY transfer_date DESC, id DESC";
if (in_array('previous_owner', $historyColumns, true)) {
    $historyQuery = "SELECT title_number, previous_owner, new_owner, transfer_date, transfer_type, reason FROM ownership_history WHERE title_number = ? ORDER BY transfer_date DESC, id DESC";
}

$historyStmt = $conn->prepare($historyQuery);
$historyStmt->bind_param('s', $title);
$historyStmt->execute();
$history = $historyStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$historyStmt->close();

$restrictionStmt = $conn->prepare('SELECT * FROM restrictions WHERE title_number = ? ORDER BY created_at DESC');
$restrictionStmt->bind_param('s', $title);
$restrictionStmt->execute();
$restrictions = $restrictionStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$restrictionStmt->close();

$caseStmt = $conn->prepare('SELECT * FROM land_cases WHERE title_number = ? ORDER BY hearing_date DESC');
$caseStmt->bind_param('s', $title);
$caseStmt->execute();
$cases = $caseStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$caseStmt->close();

$lines = [];
$lines[] = 'LAND VERIFICATION REPORT';
$lines[] = 'Generated: ' . date('Y-m-d H:i:s');
$lines[] = '';
$lines[] = '=== USER INFORMATION ===';
if (isset($_SESSION['user_id'])) {
    $lines[] = 'User ID: ' . htmlspecialchars($_SESSION['user_id']);
    $lines[] = 'User Name: ' . htmlspecialchars($_SESSION['user_name'] ?? '-');
    $lines[] = 'Email: ' . htmlspecialchars($_SESSION['user_email'] ?? '-');
} else {
    $lines[] = 'User: Not logged in (Anonymous)';
}
$lines[] = '';
$lines[] = '=== LAND INFORMATION ===';
$lines[] = 'Title Number: ' . ($landRow['title_number'] ?? $title);
$lines[] = 'Owner: ' . ($owner['full_name'] ?? $landRow['owner'] ?? '-');
$lines[] = 'Parcel Number: ' . ($landRow['parcel_number'] ?? '-');
$lines[] = 'County: ' . ($landRow['county'] ?? '-');
$lines[] = 'Location: ' . ($landRow['physical_location'] ?? '-');
$lines[] = 'Status: ' . ($landRow['status'] ?? '-');
$lines[] = 'Land Size: ' . ($landRow['land_size'] ?? '-');
$lines[] = '';
$lines[] = '=== OWNER CONTACT INFORMATION ===';
if ($owner) {
    $lines[] = 'Name: ' . ($owner['full_name'] ?? '-');
    $lines[] = 'National ID: ' . ($owner['national_id'] ?? '-');
    $lines[] = 'Phone: ' . ($owner['phone'] ?? '-');
    $lines[] = 'Email: ' . ($owner['email'] ?? '-');
    $lines[] = 'Address: ' . ($owner['address'] ?? '-');
} else {
    $lines[] = 'No owner information available.';
}
$lines[] = '';
$lines[] = '=== OWNERSHIP HISTORY ===';
foreach ($history as $h) {
    $lines[] = '- ' . ($h['transfer_date'] ?? '-') . ' : ' . ($h['previous_owner'] ?? '-') . ' -> ' . ($h['new_owner'] ?? '-');
}
$lines[] = '';
$lines[] = '=== RESTRICTIONS ===';
foreach ($restrictions as $r) {
    $lines[] = '- ' . ($r['restriction_type'] ?? '-') . ': ' . ($r['description'] ?? '-');
}
$lines[] = '';
$lines[] = '=== COURT CASES ===';
foreach ($cases as $c) {
    $lines[] = '- ' . ($c['case_number'] ?? '-') . ' | ' . ($c['court_name'] ?? '-') . ' | ' . ($c['status'] ?? '-');
}

$pdfContent = implode("\n", $lines);
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="verification-report-' . preg_replace('/[^A-Za-z0-9_-]/', '-', $title) . '.pdf"');

$escaped = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $pdfContent);
$stream = "%PDF-1.4\n1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj\n4 0 obj<< /Length 0 >>stream\n" . str_replace("\n", "\n", $escaped) . "\nendstream\nendobj\n5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\nxref\n0 6\n0000000000 65535 f \n0000000010 00000 n \n0000000062 00000 n \n0000000119 00000 n \n0000000205 00000 n \n0000000304 00000 n \ntrailer<< /Size 6 /Root 1 0 R >>\nstartxref\n0\n%%EOF";
echo $stream;
