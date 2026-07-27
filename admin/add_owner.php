<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstname'] ?? '');
    $lastName = trim($_POST['lastname'] ?? '');
    $nationalId = trim($_POST['national_id'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($firstName === '' || $lastName === '') {
        echo '<div class="alert alert-danger">First and last name are required.</div>';
    } else {
        $stmt = $mysqli->prepare('INSERT INTO owners (firstname, lastname, national_id, phone, email, address) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssss', $firstName, $lastName, $nationalId, $phone, $email, $address);
        if ($stmt->execute()) {
            echo '<div class="alert alert-success">Owner registered successfully.</div>';
        } else {
            echo '<div class="alert alert-danger">Unable to register owner.</div>';
        }
    }
}
?>
<h3>Register Owner</h3>
<form method="post">
  <div class="mb-3"><label>First Name</label><input class="form-control" name="firstname" required></div>
  <div class="mb-3"><label>Last Name</label><input class="form-control" name="lastname" required></div>
  <div class="mb-3"><label>National ID</label><input class="form-control" name="national_id"></div>
  <div class="mb-3"><label>Phone</label><input class="form-control" name="phone"></div>
  <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="email"></div>
  <div class="mb-3"><label>Address</label><input class="form-control" name="address"></div>
  <button class="btn btn-primary">Save Owner</button>
</form>

<?php include __DIR__ . '/footer.php'; ?>
