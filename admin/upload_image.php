<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title_number'] ?? '';
    if (!isset($_FILES['image']) || empty($title)) { echo '<div class="alert alert-danger">Missing data</div>'; }
    else {
        $uploaddir = __DIR__ . '/../uploads/land/';
        if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
        $fname = basename($_FILES['image']['name']);
        $target = $uploaddir . $fname;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            safe_prepare_insert('land_images', ['title_number'=>$title, 'filename'=>$fname, 'uploaded_at'=>date('Y-m-d H:i:s')]);
            echo '<div class="alert alert-success">Uploaded successfully.</div>';
        } else { echo '<div class="alert alert-danger">Upload failed.</div>'; }
    }
}

?>
<h3>Upload Land Image</h3>
<form method="post" enctype="multipart/form-data">
  <div class="mb-3"><label>Title Number</label><input class="form-control" name="title_number" required></div>
  <div class="mb-3"><label>Choose Image</label><input type="file" class="form-control" name="image" accept="image/*" required></div>
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
    <title>Upload Image</title>
</head>
<body>
    <h1>Upload Image</h1>
    <p>Upload land-related images here.</p>
</body>
</html>
