<?php
require_once __DIR__ . '/db.php';

$title = trim($_GET['title'] ?? '');
if ($title === '') {
    header('Location: view_land.php');
    exit;
}

$stmt = $mysqli->prepare('DELETE FROM land_records WHERE title_number = ? LIMIT 1');
$stmt->bind_param('s', $title);
$stmt->execute();

header('Location: view_land.php');
exit;
