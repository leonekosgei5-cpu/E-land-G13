<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

function count_safe($sql) {
    global $mysqli;
    $res = mysqli_query($mysqli, $sql);
    if ($res) {
        $r = mysqli_fetch_array($res);
        return (int) ($r[0] ?? 0);
    }
    return 0;
}

$counts = [
    'Active Land Records' => count_safe("SELECT COUNT(*) FROM land_records WHERE status = 'Active'"),
    'Registered Owners' => count_safe('SELECT COUNT(*) FROM owners'),
    'Open Court Cases' => count_safe("SELECT COUNT(*) FROM land_cases WHERE status = 'Pending'"),
    'Active Restrictions' => count_safe("SELECT COUNT(*) FROM restrictions WHERE status = 'Active'"),
    'Survey Plans' => count_safe('SELECT COUNT(*) FROM survey_plans'),
    'Verification Records' => count_safe('SELECT COUNT(*) FROM verification_logs'),
];

$recentActivities = mysqli_query($mysqli, "SELECT title_number, owner, status, registration_date FROM land_records ORDER BY registration_date DESC, created_at DESC LIMIT 6");
$pendingCases = mysqli_query($mysqli, "SELECT title_number, case_number, court_name, status FROM land_cases WHERE status = 'Pending' ORDER BY created_at DESC LIMIT 4");
?>
<div class="py-4">
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #0f6b3c 0%, #1f8a52 100%); color: white;">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
      <div>
        <span class="badge rounded-pill bg-white text-success px-3 py-2 mb-3">Project Activity Hub</span>
        <h1 class="h3 mb-2">Track land administration work at a glance</h1>
        <p class="mb-0 opacity-75">Focus on the most important land project actions, recent records, and pending follow-ups.</p>
      </div>
      <a class="btn btn-light text-success fw-semibold" href="dashboard.php">Back to Dashboard</a>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <?php foreach ($counts as $label => $value): ?>
      <div class="col-6 col-lg-4 col-xl-2">
        <div class="card shadow-sm rounded-4 h-100 border-0">
          <div class="card-body">
            <h6 class="text-uppercase text-muted fs-sm mb-2"><?php echo esc($label); ?></h6>
            <div class="display-6 fw-bold text-success"><?php echo $value; ?></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4">
    <div class="col-lg-7">
      <div class="card shadow-sm rounded-4 border-0">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h5 class="card-title mb-1">Management actions</h5>
              <p class="text-muted mb-0">Carry out the most common land project workflows quickly.</p>
            </div>
          </div>
          <div class="row g-2">
            <div class="col-md-6"><a class="btn btn-primary w-100 text-start" href="view_land.php">Manage land records</a></div>
            <div class="col-md-6"><a class="btn btn-outline-secondary w-100 text-start" href="add_case.php">Review court cases</a></div>
            <div class="col-md-6"><a class="btn btn-warning text-white w-100 text-start" href="add_restriction.php">Set restrictions</a></div>
            <div class="col-md-6"><a class="btn btn-success w-100 text-start" href="ownership_transfer.php">Ownership transfer</a></div>
            <div class="col-md-6"><a class="btn btn-info text-white w-100 text-start" href="upload_survey.php">Upload survey plan</a></div>
            <div class="col-md-6"><a class="btn btn-outline-dark w-100 text-start" href="verification_logs.php">Verify records</a></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm rounded-4 border-0 h-100">
        <div class="card-body">
          <h5 class="card-title mb-3">Needs attention</h5>
          <?php if ($pendingCases && $pendingCases->num_rows): ?>
            <div class="d-grid gap-2">
              <?php while ($case = $pendingCases->fetch_assoc()): ?>
                <div class="border rounded-3 p-3">
                  <div class="fw-semibold"><?php echo esc($case['title_number']); ?></div>
                  <div class="small text-muted">Case: <?php echo esc($case['case_number'] ?? 'N/A'); ?> • <?php echo esc($case['court_name'] ?? 'Court pending'); ?></div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">No pending court cases at the moment.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm rounded-4 border-0 mt-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="card-title mb-1">Recent land activity</h5>
          <p class="text-muted mb-0">Latest land records added to the system.</p>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Title Number</th>
              <th>Owner</th>
              <th>Status</th>
              <th>Registration Date</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($recentActivities && $recentActivities->num_rows): ?>
              <?php while ($row = $recentActivities->fetch_assoc()): ?>
                <tr>
                  <td><?php echo esc($row['title_number']); ?></td>
                  <td><?php echo esc($row['owner']); ?></td>
                  <td><span class="badge rounded-pill bg-light text-dark"><?php echo esc($row['status'] ?? 'Active'); ?></span></td>
                  <td><?php echo esc($row['registration_date']); ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center py-3">No recent activity available.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
