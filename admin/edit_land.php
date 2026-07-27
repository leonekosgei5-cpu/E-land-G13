<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

$title = $_GET['title'] ?? '';
if (!$title) { echo '<div class="alert alert-warning">Missing title number.</div>'; include __DIR__.'/footer.php'; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $mysqli->prepare("UPDATE land_records SET owner=?, physical_location=?, latitude=?, longitude=?, land_size=?, land_use=?, registration_date=?, status=? WHERE title_number=?");
    $stmt->bind_param('sssssssss', $_POST['owner'], $_POST['physical_location'], $_POST['latitude'], $_POST['longitude'], $_POST['land_size'], $_POST['land_use'], $_POST['registration_date'], $_POST['status'], $title);
    $stmt->execute();
    header('Location: dashboard.php'); exit;
}

$res = $mysqli->query($mysqli->real_escape_string("SELECT * FROM land_records WHERE title_number='".$title."' LIMIT 1"));
$row = $res ? $res->fetch_assoc() : null;

?>
<h3>Edit Land: <?php echo esc($title); ?></h3>
<?php if (!$row): ?><div class="alert alert-danger">Record not found.</div><?php else: ?>
<form method="post">
  <div class="row">
    <div class="mb-3 col-md-6"><label>Owner</label><input class="form-control" name="owner" value="<?php echo esc($row['owner']); ?>"></div>
    <div class="mb-3 col-md-6"><label>Status</label><select class="form-control" name="status"><option <?php if($row['status']=='Active') echo 'selected'; ?>>Active</option><option <?php if($row['status']=='Inactive') echo 'selected'; ?>>Inactive</option></select></div>
    <div class="mb-3 col-md-12"><label>Physical Location</label><input class="form-control" name="physical_location" value="<?php echo esc($row['physical_location']); ?>"></div>
    <div class="mb-3 col-md-6"><label>Latitude</label><input class="form-control" name="latitude" value="<?php echo esc($row['latitude']); ?>"></div>
    <div class="mb-3 col-md-6"><label>Longitude</label><input class="form-control" name="longitude" value="<?php echo esc($row['longitude']); ?>"></div>
    <div class="mb-3 col-md-4"><label>Land Size</label><input class="form-control" name="land_size" value="<?php echo esc($row['land_size']); ?>"></div>
    <div class="mb-3 col-md-4"><label>Land Use</label><input class="form-control" name="land_use" value="<?php echo esc($row['land_use']); ?>"></div>
    <div class="mb-3 col-md-4"><label>Registration Date</label><input type="date" class="form-control" name="registration_date" value="<?php echo esc($row['registration_date']); ?>"></div>
  </div>
  <button class="btn btn-primary">Update Land</button>
</form>
<?php endif; include __DIR__ . '/footer.php'; ?>
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Land</title>
</head>
<body>
    <h1>Edit Land</h1>
    <p>Form for editing land records.</p>
</body>
</html>
