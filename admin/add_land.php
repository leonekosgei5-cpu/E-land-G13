<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

$owners = $mysqli->query('SELECT id, firstname, lastname FROM owners ORDER BY firstname, lastname');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title_number' => trim($_POST['title_number'] ?? ''),
        'owner' => trim($_POST['owner'] ?? ''),
        'owner_id' => intval($_POST['owner_id'] ?? 0),
        'parcel_number' => trim($_POST['parcel_number'] ?? ''),
        'block_number' => trim($_POST['block_number'] ?? ''),
        'county' => trim($_POST['county'] ?? ''),
        'sub_county' => trim($_POST['sub_county'] ?? ''),
        'ward' => trim($_POST['ward'] ?? ''),
        'physical_location' => trim($_POST['physical_location'] ?? ''),
        'latitude' => trim($_POST['latitude'] ?? ''),
        'longitude' => trim($_POST['longitude'] ?? ''),
        'land_size' => trim($_POST['land_size'] ?? ''),
        'land_category' => trim($_POST['land_category'] ?? ''),
        'land_use' => trim($_POST['land_use'] ?? ''),
        'registration_date' => trim($_POST['registration_date'] ?? date('Y-m-d')),
        'status' => trim($_POST['status'] ?? 'Active')
    ];

    if ($data['title_number'] === '') {
        echo '<div class="alert alert-danger">Title number is required.</div>';
    } elseif ($data['owner_id'] <= 0) {
        echo '<div class="alert alert-danger">Please select a registered owner.</div>';
    } else {
        $stmt = $mysqli->prepare('INSERT INTO land_records (title_number, owner, owner_id, parcel_number, block_number, county, sub_county, ward, physical_location, latitude, longitude, land_size, land_category, land_use, registration_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param(
            'ssisssssssssssss',
            $data['title_number'],
            $data['owner'],
            $data['owner_id'],
            $data['parcel_number'],
            $data['block_number'],
            $data['county'],
            $data['sub_county'],
            $data['ward'],
            $data['physical_location'],
            $data['latitude'],
            $data['longitude'],
            $data['land_size'],
            $data['land_category'],
            $data['land_use'],
            $data['registration_date'],
            $data['status']
        );
        if ($stmt->execute()) {
            header('Location: dashboard.php'); exit;
        }
        echo '<div class="alert alert-danger">Failed to insert record.</div>';
    }
}

?>
<div class="card shadow-sm rounded-4 border-0 mb-4">
  <div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="mb-1">Register New Land</h3>
        <p class="text-muted mb-0">Capture a new land parcel and link it to its owner.</p>
      </div>
      <a class="btn btn-outline-secondary" href="view_land.php">View Records</a>
    </div>
    <form method="post">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Title Number</label><input class="form-control" name="title_number" required></div>
        <div class="col-md-4"><label class="form-label">Owner Name</label><input class="form-control" name="owner" placeholder="Enter owner name"></div>
        <div class="col-md-4"><label class="form-label">Select Registered Owner</label><select class="form-select" name="owner_id" required><option value="" disabled selected>-- Select owner --</option><?php while ($row = $owners->fetch_assoc()): ?><option value="<?php echo (int)$row['id']; ?>"><?php echo esc($row['firstname'] . ' ' . $row['lastname']); ?></option><?php endwhile; ?></select></div>
        <div class="col-md-4"><label class="form-label">Parcel Number</label><input class="form-control" name="parcel_number"></div>
        <div class="col-md-4"><label class="form-label">Block Number</label><input class="form-control" name="block_number"></div>
        <div class="col-md-4"><label class="form-label">County</label><input class="form-control" name="county"></div>
        <div class="col-md-4"><label class="form-label">Sub County</label><input class="form-control" name="sub_county"></div>
        <div class="col-md-4"><label class="form-label">Ward</label><input class="form-control" name="ward"></div>
        <div class="col-md-4"><label class="form-label">Physical Location</label><input class="form-control" name="physical_location"></div>
        <div class="col-md-2"><label class="form-label">Latitude</label><input class="form-control" name="latitude"></div>
        <div class="col-md-2"><label class="form-label">Longitude</label><input class="form-control" name="longitude"></div>
        <div class="col-md-3"><label class="form-label">Land Size</label><input class="form-control" name="land_size"></div>
        <div class="col-md-3"><label class="form-label">Land Category</label><input class="form-control" name="land_category"></div>
        <div class="col-md-3"><label class="form-label">Land Use</label><input class="form-control" name="land_use"></div>
        <div class="col-md-3"><label class="form-label">Registration Date</label><input type="date" class="form-control" name="registration_date"></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option>Active</option><option>Inactive</option></select></div>
      </div>
      <button class="btn btn-primary mt-3">Register Land</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
