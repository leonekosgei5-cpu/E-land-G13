<?php
session_start();
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$title_number = trim($_POST['property_id'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$national_id_passport = trim($_POST['national_id_passport'] ?? '');
$proof_of_income = trim($_POST['proof_of_income'] ?? '');
$landlord_references = trim($_POST['landlord_references'] ?? '');
$security_deposit_amount = trim($_POST['security_deposit_amount'] ?? '');
$lease_duration = trim($_POST['lease_duration'] ?? '');
$intended_use = trim($_POST['intended_use'] ?? '');
$budget = trim($_POST['budget'] ?? '');

$required_fields = [
    'full_name' => $full_name,
    'email' => $email,
    'phone' => $phone,
    'national_id_passport' => $national_id_passport,
    'proof_of_income' => $proof_of_income,
    'landlord_references' => $landlord_references,
    'security_deposit_amount' => $security_deposit_amount,
    'lease_duration' => $lease_duration,
    'intended_use' => $intended_use,
    'budget' => $budget,
];

foreach ($required_fields as $field => $value) {
    if ($value === '') {
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit;
    }
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

if (!is_numeric($budget) || !is_numeric($security_deposit_amount)) {
    echo json_encode(['success' => false, 'message' => 'Budget and security deposit must be numeric values']);
    exit;
}

$land_record = mongo_find_one('land_records', ['title_number' => $title_number]);
$land_record_data = [];
if ($land_record) {
    $land_record_data = json_decode(json_encode($land_record), true);
}

$lease_request_id = new MongoDB\BSON\ObjectId();
$requestDocument = [
    '_id' => $lease_request_id,
    'user_id' => new MongoDB\BSON\ObjectId($user_id),
    'title_number' => $title_number,
    'full_name' => $full_name,
    'email' => $email,
    'phone' => $phone,
    'national_id_passport' => $national_id_passport,
    'proof_of_income' => $proof_of_income,
    'landlord_references' => $landlord_references,
    'security_deposit_amount' => $security_deposit_amount,
    'lease_duration' => $lease_duration,
    'intended_use' => $intended_use,
    'budget' => $budget,
    'status' => 'Pending',
    'admin_notified' => false,
    'created_at' => new MongoDB\BSON\UTCDateTime(),
];

try {
    mongo_insert_one('lease_requests', $requestDocument);
} catch (MongoDB\Driver\Exception\MongoDBException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to save lease request: ' . $e->getMessage()]);
    exit;
}

$admin = mongo_find_one('administrators', ['status' => 'Active']);
if ($admin) {
    $admin_email = $admin->email ?? '';
    $admin_name = $admin->first_name ?? 'Administrator';
    $subject = "New Lease Request - {$title_number}";
    $email_body = "<html><head><style>body { font-family: Arial, sans-serif; background-color: #f6fbf8; } .container { background-color: #fff; max-width: 600px; margin: 20px auto; padding: 20px; border-radius: 10px; border: 1px solid #e6f1e8; } .header { background: linear-gradient(135deg, #0f5d32 0%, #1f7f49 100%); color: #fff; padding: 15px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; } h2 { color: #145132; margin-top: 0; } .section { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e6f1e8; } .label { font-weight: 600; color: #145132; } .value { color: #5d6b61; margin-left: 10px; } .button { background: #17653c; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 15px; } .footer { color: #999; font-size: 12px; margin-top: 20px; padding-top: 15px; border-top: 1px solid #e6f1e8; }</style></head><body><div class='container'><div class='header'><h2 style='margin: 0;'>New Lease Request Received</h2></div><p>Hello {$admin_name},</p><p>A new lease request has been submitted for your review. Below are the details:</p><div class='section'><h3 style='margin-top: 0; color: #145132;'>Land Information</h3><p><span class='label'>Title Number:</span><span class='value'>{$title_number}</span></p><p><span class='label'>Parcel Number:</span><span class='value'>" . htmlspecialchars($land_record_data['parcel_number'] ?? 'N/A') . "</span></p><p><span class='label'>County:</span><span class='value'>" . htmlspecialchars($land_record_data['county'] ?? 'N/A') . "</span></p><p><span class='label'>Location:</span><span class='value'>" . htmlspecialchars($land_record_data['physical_location'] ?? 'N/A') . "</span></p></div><div class='section'><h3 style='margin-top: 0; color: #145132;'>Applicant Information</h3><p><span class='label'>Full Name:</span><span class='value'>{$full_name}</span></p><p><span class='label'>Email:</span><span class='value'>{$email}</span></p><p><span class='label'>Phone:</span><span class='value'>{$phone}</span></p><p><span class='label'>National ID/Passport:</span><span class='value'>{$national_id_passport}</span></p></div><div class='section'><h3 style='margin-top: 0; color: #145132;'>Lease Terms</h3><p><span class='label'>Preferred Duration:</span><span class='value'>{$lease_duration}</span></p><p><span class='label'>Intended Use:</span><span class='value'>{$intended_use}</span></p><p><span class='label'>Budget (KES):</span><span class='value'>" . number_format((float)$budget) . "</span></p><p><span class='label'>Security Deposit (KES):</span><span class='value'>" . number_format((float)$security_deposit_amount) . "</span></p></div><div class='section'><h3 style='margin-top: 0; color: #145132;'>Requirements Provided</h3><p><span class='label'>Proof of Income/Business Registration:</span></p><p style='margin-left: 10px; color: #5d6b61;'>{$proof_of_income}</p><p><span class='label'>Previous Landlord References:</span></p><p style='margin-left: 10px; color: #5d6b61;'>{$landlord_references}</p></div><a href='http://localhost/E_Land/admin/lease-requests.php' class='button'>Review Request</a><div class='footer'><p>This is an automated message from E-Land. Please do not reply to this email.</p></div></div></body></html>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@e-land.com\r\n";
    if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $mail_sent = mail($admin_email, $subject, $email_body, $headers);
        if ($mail_sent) {
            mongo_update_one('lease_requests', ['_id' => $lease_request_id], ['admin_notified' => true]);
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Lease request submitted successfully',
    'request_id' => (string) $lease_request_id,
]);
?>
