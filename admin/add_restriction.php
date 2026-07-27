<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

$types = ['Bank Charge','Court Order','Lease Expired','Boundary Dispute','Government Restriction','Succession','Caveat'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title_number'=>$_POST['title_number']??'',
        'restriction_type'=>$_POST['restriction_type']??'',
        'description'=>$_POST['description']??'',
        'status'=>$_POST['status']??'Active'
    ];
    if (safe_prepare_insert('restrictions', $data)) { header('Location: dashboard.php'); exit; }
    else echo '<div class="alert alert-danger">Insert failed.</div>';
}

?>
<h3>Register Restriction</h3>
<form method="post">
  <div class="mb-3"><label>Title Number</label><input class="form-control" name="title_number"></div>
  <div class="mb-3"><label>Restriction Type</label><select class="form-control" name="restriction_type"><?php foreach($types as $t) echo "<option>".esc($t)."</option>"; ?></select></div>
  <div class="mb-3"><label>Description</label><textarea class="form-control" name="description"></textarea></div>
  <div class="mb-3"><label>Status</label><input class="form-control" name="status" value="Active"></div>
  <button class="btn btn-primary">Register Restriction</button>
</form>

<?php include __DIR__ . '/footer.php'; ?>
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Restriction</title>
</head>
<body>
    <h1>Add Restriction</h1>
    <p>Form for adding land restrictions.</p>
</body>
</html>
