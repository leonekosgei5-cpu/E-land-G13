<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

$where = [];
if (!empty($_GET['title_number'])) {
    $where[] = "title_number = '" . $mysqli->real_escape_string($_GET['title_number']) . "'";
}
if (!empty($_GET['user'])) {
    $where[] = "user_name = '" . $mysqli->real_escape_string($_GET['user']) . "'";
}
if (!empty($_GET['date'])) {
    $where[] = "DATE(verification_date) = '" . $mysqli->real_escape_string($_GET['date']) . "'";
}

$sql = 'SELECT * FROM verification_logs' . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY verification_date DESC LIMIT 200';
$res = $mysqli->query($sql);

?>
<div class="card shadow-sm rounded-4 border-0 mb-4">
  <div class="card-body p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
      <div>
        <h3 class="mb-1">Verification Logs</h3>
        <p class="text-muted mb-0">Review the latest verification activity for land records.</p>
      </div>
    </div>

    <form method="get" class="row g-3 mb-4">
      <div class="col-md-4">
        <label class="form-label">Title Number</label>
        <input class="form-control" name="title_number" placeholder="Title Number" value="<?php echo esc($_GET['title_number'] ?? ''); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">User</label>
        <input class="form-control" name="user" placeholder="User" value="<?php echo esc($_GET['user'] ?? ''); ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Date</label>
        <input type="date" class="form-control" name="date" value="<?php echo esc($_GET['date'] ?? ''); ?>">
      </div>
      <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-primary w-100">Search</button>
      </div>
    </form>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Title</th>
            <th>Recommendation</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($res && $res->num_rows): ?>
            <?php while ($r = $res->fetch_assoc()): ?>
              <tr>
                <td><?php echo esc($r['id'] ?? ''); ?></td>
                <td><?php echo esc($r['user_name'] ?? ''); ?></td>
                <td><?php echo esc($r['title_number'] ?? ''); ?></td>
                <td><?php echo esc($r['recommendation'] ?? ''); ?></td>
                <td><?php echo esc($r['verification_date'] ?? ''); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center py-4">No verification logs found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
