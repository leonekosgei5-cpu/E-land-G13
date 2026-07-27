<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/header.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title_number'] ?? '');
    $newOwner = trim($_POST['new_owner'] ?? '');
    $transferDate = trim($_POST['transfer_date'] ?? date('Y-m-d'));
    $transferType = trim($_POST['transfer_type'] ?? '');
    $reason = trim($_POST['transfer_reason'] ?? '');

    if ($title === '' || $newOwner === '') {
        $message = 'Please enter both the title number and the new owner.';
        $messageType = 'danger';
    } else {
        $res = $mysqli->query("SELECT owner FROM land_records WHERE title_number='" . $mysqli->real_escape_string($title) . "' LIMIT 1");
        $row = $res ? $res->fetch_assoc() : null;
        $oldOwner = $row['owner'] ?? '';

        $stmt = $mysqli->prepare("UPDATE land_records SET owner=? WHERE title_number=?");
        if ($stmt) {
            $stmt->bind_param('ss', $newOwner, $title);
            $stmt->execute();
            $stmt->close();

            safe_prepare_insert('ownership_history', [
                'title_number' => $title,
                'previous_owner' => $oldOwner,
                'new_owner' => $newOwner,
                'transfer_date' => $transferDate,
                'transfer_type' => $transferType,
                'reason' => $reason
            ]);

            $message = 'Ownership transfer recorded successfully.';
            $messageType = 'success';
        } else {
            $message = 'Unable to process the transfer. Please try again.';
            $messageType = 'danger';
        }
    }
}

?>
<div class="card shadow-sm rounded-4 border-0 mb-4">
  <div class="card-body p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
      <div>
        <h3 class="mb-1">Ownership Transfer</h3>
        <p class="text-muted mb-0">Record a verified change of ownership for a land title.</p>
      </div>
      <a class="btn btn-outline-secondary" href="view_land.php">Back to Records</a>
    </div>

    <?php if ($message !== ''): ?>
      <div class="alert alert-<?php echo esc($messageType); ?> rounded-3"><?php echo esc($message); ?></div>
    <?php endif; ?>

    <form method="post" class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Title Number</label>
        <input class="form-control" name="title_number" placeholder="e.g. NKR/LND/2024/001256" required>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">New Owner</label>
        <input class="form-control" name="new_owner" placeholder="Enter new owner name" required>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Transfer Date</label>
        <input type="date" class="form-control" name="transfer_date" value="<?php echo date('Y-m-d'); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Transfer Type</label>
        <select class="form-select" name="transfer_type">
          <option value="">Select type</option>
          <option value="Sale">Sale</option>
          <option value="Gift">Gift</option>
          <option value="Inheritance">Inheritance</option>
          <option value="Court Order">Court Order</option>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold">Transfer Reason</label>
        <textarea class="form-control" name="transfer_reason" rows="4" placeholder="Add a short note about the transfer reason"></textarea>
      </div>
      <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary">Record Transfer</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
