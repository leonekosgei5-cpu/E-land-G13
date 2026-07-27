<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

$title = trim($_GET['title_number'] ?? '');
$owner = trim($_GET['owner'] ?? '');
$status = trim($_GET['status'] ?? '');

$where = [];
if ($title !== '') {
    $where[] = "title_number LIKE '%" . $mysqli->real_escape_string($title) . "%'";
}
if ($owner !== '') {
    $where[] = "owner LIKE '%" . $mysqli->real_escape_string($owner) . "%'";
}
if ($status !== '') {
    $where[] = "status = '" . $mysqli->real_escape_string($status) . "'";
}
$sql = 'SELECT lr.*, (SELECT COUNT(*) FROM land_cases lc WHERE lc.title_number = lr.title_number) AS case_count FROM land_records lr' . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY lr.registration_date DESC LIMIT 200';
$result = $mysqli->query($sql);

?>
<div class="card shadow-sm rounded-4 border-0 mb-4">
  <div class="card-body p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Land Registry</h1>
        <p class="text-muted mb-0">Search registered land records and manage them from one place.</p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="view_land_cases.php">View Land Cases</a>
        <a class="btn btn-primary" href="add_land.php">Register New Land</a>
      </div>
        <input class="form-control" name="title_number" value="<?php echo esc($title); ?>" placeholder="Search by title number">
      </div>
      <div class="col-md-4">
        <label class="form-label">Owner</label>
        <input class="form-control" name="owner" value="<?php echo esc($owner); ?>" placeholder="Search by owner">
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="">Any</option>
          <option value="Active" <?php if ($status === 'Active') echo 'selected'; ?>>Active</option>
          <option value="Inactive" <?php if ($status === 'Inactive') echo 'selected'; ?>>Inactive</option>
        </select>
      </div>
      <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-primary w-100">Search</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Title Number</th>
            <th>Owner</th>
            <th>Location</th>
            <th>Size</th>
            <th>Use</th>
            <th>Status</th>
            <th>Cases</th>
            <th>Registered</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo esc($row['title_number']); ?></td>
                <td><?php echo esc($row['owner']); ?></td>
                <td><?php echo esc($row['physical_location']); ?></td>
                <td><?php echo esc($row['land_size']); ?></td>
                <td><?php echo esc($row['land_use']); ?></td>
                <td><?php echo esc($row['status']); ?></td>
                <td><?php echo esc($row['case_count']); ?></td>
                <td><?php echo esc($row['registration_date']); ?></td>
                <td>
                  <a class="btn btn-sm btn-outline-secondary" href="edit_land.php?title=<?php echo urlencode($row['title_number']); ?>">Edit</a>
                  <a class="btn btn-sm btn-outline-info" href="view_land_cases.php?title_number=<?php echo urlencode($row['title_number']); ?>">Cases</a>
                  <a class="btn btn-sm btn-danger" href="delete_land.php?title=<?php echo urlencode($row['title_number']); ?>" onclick="return confirm('Delete this land record?');">Delete</a>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center py-4">No records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
