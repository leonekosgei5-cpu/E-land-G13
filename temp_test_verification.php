<?php
require 'db.php';

$conn->query('DELETE FROM verification_logs');
$conn->query('DELETE FROM ownership_history');
$conn->query('DELETE FROM restrictions');
$conn->query('DELETE FROM land_cases');
$conn->query('DELETE FROM survey_plans');
$conn->query('DELETE FROM land_images');
$conn->query('DELETE FROM land_records');
$conn->query('DELETE FROM owners');

$stmt = $conn->prepare('INSERT INTO owners (firstname, lastname, national_id, phone, email, address) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssss', $fullName, $lastName, $nationalId, $phone, $email, $address);
$fullName = 'John';
$lastName = 'Maina';
$nationalId = '12345678';
$phone = '0712345678';
$email = 'john@example.com';
$address = 'Nakuru';
$stmt->execute();
$ownerId = $stmt->insert_id;
$stmt->close();

$stmt = $conn->prepare('INSERT INTO land_records (title_number, owner, owner_id, parcel_number, county, physical_location, latitude, longitude, land_size, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssisssssss', $title, $owner, $ownerId, $parcel, $county, $location, $lat, $lon, $size, $status);
$title = 'NKR/LND/2024/001256';
$owner = 'John Maina';
$parcel = 'P-001';
$county = 'Nakuru';
$location = 'Kahawa West';
$lat = '-0.3072';
$lon = '36.0822';
$size = '2.5 acres';
$status = 'Active';
$stmt->execute();
$stmt->close();

$conn->query("INSERT INTO ownership_history (title_number, owner_name, transfer_date, transfer_type, transfer_reason) VALUES ('NKR/LND/2024/001256', 'Jane Doe', '2024-01-10', 'Sale', 'Family transfer')");
$conn->query("INSERT INTO restrictions (title_number, restriction_type, description, status) VALUES ('NKR/LND/2024/001256', 'Court Order', 'Pending litigation', 'Active')");
$conn->query("INSERT INTO land_cases (title_number, case_number, court_name, case_type, status, hearing_date) VALUES ('NKR/LND/2024/001256', 'LC-1001', 'High Court', 'Boundary', 'Pending', '2024-06-15')");
$conn->query("INSERT INTO survey_plans (title_number, plan_file) VALUES ('NKR/LND/2024/001256', 'survey.pdf')");
$conn->query("INSERT INTO land_images (title_number, image) VALUES ('NKR/LND/2024/001256', 'land.jpg')");

$res = $conn->query("SELECT title_number FROM land_records WHERE title_number='NKR/LND/2024/001256'");
echo 'records=' . $res->num_rows . PHP_EOL;

$verify = $conn->prepare('SELECT * FROM land_records WHERE title_number = ? LIMIT 1');
$verify->bind_param('s', $title);
$verify->execute();
$land = $verify->get_result()->fetch_assoc();
echo 'owner=' . ($land['owner'] ?? 'none') . PHP_EOL;
