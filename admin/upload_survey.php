<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title_number'] ?? '';
    if (!isset($_FILES['survey']) || empty($title)) { echo '<div class="alert alert-danger">Missing data</div>'; }
    else {
        $uploaddir = __DIR__ . '/../uploads/survey/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
        $fname = basename($_FILES['survey']['name']);
        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf','jpg','jpeg','png'])) { echo '<div class="alert alert-danger">Invalid file type.</div>'; }
        else {
            $target = $uploaddir . $fname;
            if (move_uploaded_file($_FILES['survey']['tmp_name'], $target)) {
                safe_prepare_insert('survey_plans', ['title_number'=>$title, 'filename'=>$fname, 'uploaded_at'=>date('Y-m-d H:i:s')]);
                echo '<div class="alert alert-success">Uploaded successfully.</div>';
            } else { echo '<div class="alert alert-danger">Upload failed.</div>'; }
        }
    }
}

?>
<h3>Upload Survey Plan</h3>
<form method="post" enctype="multipart/form-data">
  <div class="mb-3"><label>Title Number</label><input class="form-control" name="title_number" required></div>
  <div class="mb-3"><label>Choose File (PDF / JPG / PNG)</label><input type="file" class="form-control" name="survey" accept="application/pdf,image/*" required></div>
  <button class="btn btn-primary">Upload</button>
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
    <title>Upload Survey</title>
</head>
<body>
    <h1>Upload Survey</h1>
    <p>Upload survey documents here.</p>
</body>
</html>
