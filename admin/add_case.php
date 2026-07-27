<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'case_number' => trim($_POST['case_number'] ?? ''),
        'title_number' => trim($_POST['title_number'] ?? ''),
        'court_name' => trim($_POST['court_name'] ?? ''),
        'case_type' => trim($_POST['case_type'] ?? ''),
        'status' => trim($_POST['status'] ?? 'Pending'),
        'hearing_date' => trim($_POST['hearing_date'] ?? ''),
        'decision' => trim($_POST['decision'] ?? '')
    ];

    if ($data['case_number'] === '') {
        $alert = '<div class="alert alert-warning">Please enter a case number.</div>';
    } elseif (safe_prepare_insert('land_cases', $data)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $alert = '<div class="alert alert-danger">Unable to save the court case. Please try again.</div>';
    }
}
?>
<div class="container-fluid px-0">
  <div class="card shadow-sm rounded-4">
    <div class="card-body p-4">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
          <h1 class="h3 mb-1">Register Legal Case</h1>
          <p class="text-muted mb-0">Add a new land dispute or court case record to the registry.</p>
        </div>
        <a class="btn btn-outline-secondary" href="view_land.php">Back to Land List</a>
      </div>

      <?php echo $alert; ?>

      <form method="post" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Case Number</label>
          <input class="form-control form-control-lg" name="case_number" required placeholder="ELC 34/2026">
        </div>
        <div class="col-md-6">
          <label class="form-label">Title Number</label>
          <input class="form-control form-control-lg" name="title_number" placeholder="NKR/LND/2024/001256">
        </div>
        <div class="col-md-6">
          <label class="form-label">Court Name</label>
          <input class="form-control form-control-lg" name="court_name" placeholder="Environment and Land Court">
        </div>
        <div class="col-md-6">
          <label class="form-label">Case Type</label>
          <input class="form-control form-control-lg" name="case_type" placeholder="Boundary Dispute">
        </div>
        <div class="col-md-6">
          <label class="form-label">Hearing Date</label>
          <input type="date" class="form-control form-control-lg" name="hearing_date">
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <select class="form-select form-select-lg" name="status">
            <option value="Pending">Pending</option>
            <option value="Active">Active</option>
            <option value="Closed">Closed</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Decision</label>
          <textarea class="form-control form-control-lg" name="decision" rows="4" placeholder="Enter decision details or remarks."></textarea>
        </div>
        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary btn-lg">Save Court Case</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
