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
$preferred_payment_plan = trim($_POST['preferred_payment_plan'] ?? '');
$budget = trim($_POST['budget'] ?? '');
$additional_notes = trim($_POST['additional_notes'] ?? '');

$required_fields = [
    'full_name' => $full_name,
    'email' => $email,
    'phone' => $phone,
    'national_id_passport' => $national_id_passport,
    'proof_of_income' => $proof_of_income,
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

$purchase_request_id = new MongoDB\BSON\ObjectId();
$requestDocument = [
    '_id' => $purchase_request_id,
    'user_id' => new MongoDB\BSON\ObjectId($user_id),
    'title_number' => $title_number,
    'full_name' => $full_name,
    'email' => $email,
    'phone' => $phone,
    'national_id_passport' => $national_id_passport,
    'proof_of_income' => $proof_of_income,
    'preferred_payment_plan' => $preferred_payment_plan,
    'budget' => $budget,
    'additional_notes' => $additional_notes,
    'status' => 'Pending',
    'admin_notified' => false,
    'created_at' => new MongoDB\BSON\UTCDateTime(),
];

try {
    mongo_insert_one('purchase_requests', $requestDocument);
} catch (MongoDB\Driver\Exception\MongoDBException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to save purchase request: ' . $e->getMessage()]);
    exit;
}

$admin = mongo_find_one('administrators', ['status' => 'Active']);
if ($admin) {
    $admin_email = $admin->email ?? '';
    $admin_name = $admin->first_name ?? 'Administrator';
    $subject = "New Purchase Request - {$title_number}";
    $email_body = "<html><body style='font-family: Arial, sans-serif; background-color: #f6fbf8;'><div style='max-width: 620px; margin: 20px auto; background: #fff; border-radius: 10px; padding: 24px; border: 1px solid #e6f1e8;'><h2 style='margin: 0 0 12px; color: #145132;'>New Land Purchase Request Received</h2><p>Hello {$admin_name},</p><p>A new purchase request has been submitted and is awaiting your approval.</p><p><strong>Property:</strong> {$title_number}</p><p><strong>Buyer:</strong> {$full_name}</p><p><strong>Email:</strong> {$email}</p><p><strong>Phone:</strong> {$phone}</p><p><strong>Preferred Payment Plan:</strong> {$preferred_payment_plan}</p><p><strong>Budget:</strong> {$budget}</p><p><strong>Notes:</strong> {$additional_notes}</p><a href='http://localhost/E_Land/admin/purchase-requests.php' style='display:inline-block; margin-top:15px; padding:10px 18px; background:#17653c; color:#fff; text-decoration:none; border-radius:6px;'>Review Request</a></div></body></html>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@e-land.com\r\n";
    if (filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        mail($admin_email, $subject, $email_body, $headers);
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Purchase request submitted successfully',
    'request_id' => (string) $purchase_request_id,
]);
?>
