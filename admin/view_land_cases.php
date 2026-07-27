<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

$title = trim($_GET['title_number'] ?? '');
$caseNumber = trim($_GET['case_number'] ?? '');
$status = trim($_GET['status'] ?? '');

$where = [];
if ($title !== '') {
    $where[] = "lc.title_number LIKE '%" . $mysqli->real_escape_string($title) . "%'";
}
if ($caseNumber !== '') {
    $where[] = "lc.case_number LIKE '%" . $mysqli->real_escape_string($caseNumber) . "%'";
}
if ($status !== '') {
    $where[] = "lc.status = '" . $mysqli->real_escape_string($status) . "'";
}

$sql = 'SELECT lc.*, lr.owner, lr.physical_location FROM land_cases lc LEFT JOIN land_records lr ON lr.title_number = lc.title_number' .
       (count($where) ? ' WHERE ' . implode(' AND ', $where) : '') .
       ' ORDER BY lc.hearing_date DESC, lc.title_number ASC LIMIT 200';
$result = $mysqli->query($sql);
?>
<div class="card shadow-sm rounded-4 border-0 mb-4">
  <div class="card-body p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">Land Case Records</h1>
        <p class="text-muted mb-0">Browse court cases linked to registered land titles.</p>
      </div>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary" href="view_land.php">Back to Land List</a>
        <a class="btn btn-primary" href="add_case.php">Add New Case</a>
      </div>
    </div>

    <form method="get" class="row g-3 mb-3">
      <div class="col-md-4">
        <label class="form-label">Title Number</label>
        <input class="form-control" name="title_number" value="<?php echo esc($title); ?>" placeholder="Search by title number">
      </div>
      <div class="col-md-4">
        <label class="form-label">Case Number</label>
        <input class="form-control" name="case_number" value="<?php echo esc($caseNumber); ?>" placeholder="Search by case number">
      </div>
      <div class="col-md-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status">
          <option value="">Any</option>
          <option value="Pending" <?php if ($status === 'Pending') echo 'selected'; ?>>Pending</option>
          <option value="Active" <?php if ($status === 'Active') echo 'selected'; ?>>Active</option>
          <option value="Closed" <?php if ($status === 'Closed') echo 'selected'; ?>>Closed</option>
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
            <th>Case Number</th>
            <th>Title Number</th>
            <th>Owner</th>
            <th>Court</th>
            <th>Type</th>
            <th>Hearing Date</th>
            <th>Status</th>
            <th>Decision</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo esc($row['case_number']); ?></td>
                <td><a href="view_land.php?title_number=<?php echo urlencode($row['title_number']); ?>"><?php echo esc($row['title_number']); ?></a></td>
                <td><?php echo esc($row['owner']); ?></td>
                <td><?php echo esc($row['court_name']); ?></td>
                <td><?php echo esc($row['case_type']); ?></td>
                <td><?php echo esc($row['hearing_date']); ?></td>
                <td><?php echo esc($row['status']); ?></td>
                <td><?php echo esc($row['decision']); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center py-4">No case records found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>