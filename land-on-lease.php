
<?php
session_start();

require_once 'mongodb.php';

$records = [];
$recordError = '';

try {
    $cursor = mongo_find('land_records', ['record_type' => 'leasing'], ['sort' => ['created_at' => -1]]);
    foreach ($cursor as $doc) {
        $records[] = [
            'title_number' => $doc->title_number ?? '—',
            'parcel_number' => $doc->parcel_number ?? '—',
            'county' => $doc->county ?? '—',
            'physical_location' => $doc->physical_location ?? '—',
            'land_size' => $doc->land_size ?? '—',
            'status' => $doc->status ?? 'Available',
            'lease_term' => $doc->lease_term ?? 'N/A',
            'price' => $doc->price ?? 'KES 150,000/year',
            'description' => $doc->description ?? 'Verified land parcel listed through the E-Land system.'
        ];
    }
} catch (MongoDB\Driver\Exception\MongoDBException $e) {
    $recordError = $e->getMessage();
}

$sampleListings = [
    [
        'title_number' => 'NKR/LND/2024/001256',
        'parcel_number' => 'P-101',
        'county' => 'Nairobi',
        'physical_location' => 'Kibera, Nairobi',
        'land_size' => '0.75 acres',
        'status' => 'Available',
        'lease_term' => '6 months',
        'price' => 'KES 120,000/year',
        'description' => 'Ideal for small-scale farming, storage, or light commercial use.'
    ],
    [
        'title_number' => 'NKR/LND/2024/001789',
        'parcel_number' => 'P-205',
        'county' => 'Kiambu',
        'physical_location' => 'Ruiru, Kiambu',
        'land_size' => '1.2 acres',
        'status' => 'Available',
        'lease_term' => '12 months',
        'price' => 'KES 180,000/year',
        'description' => 'Spacious parcel close to major transport routes and schools.'
    ],
    [
        'title_number' => 'NKR/LND/2024/002314',
        'parcel_number' => 'P-340',
        'county' => 'Machakos',
        'physical_location' => 'Mlolongo, Machakos',
        'land_size' => '2.5 acres',
        'status' => 'Available',
        'lease_term' => '18 months',
        'price' => 'KES 240,000/year',
        'description' => 'Perfect for agro-business, warehousing, or community projects.'
    ]
];

$displayListings = !empty($records) ? $records : $sampleListings;
$showingSample = empty($records);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Land on Lease - E-Land</title>
    <link rel="stylesheet" href="style.css" />
    <style>
      body { background:#f6fbf8; color:#123524; }
      .page-header { background:linear-gradient(135deg, #0f5d32 0%, #1f7f49 100%); color:#fff; padding:3rem 1.5rem; text-align:center; }
      .page-content { padding:2rem 0 3rem; }
      .listing-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1.25rem; }
      .listing-card { background:#fff; border:1px solid #e6f1e8; border-radius:18px; padding:1.2rem; box-shadow:0 10px 24px rgba(32, 72, 49, 0.08); display:flex; flex-direction:column; gap:0.8rem; }
      .listing-card h3 { margin:0; font-size:1.05rem; color:#134d2b; }
      .listing-card p { margin:0; color:#5d6b61; line-height:1.5; }
      .meta-list { margin:0; padding:0; list-style:none; display:grid; gap:0.4rem; color:#355744; font-size:0.95rem; }
      .card-footer { margin-top:auto; display:flex; justify-content:space-between; align-items:center; gap:0.75rem; }
      .card-actions { display:flex; flex-wrap:wrap; gap:0.5rem; justify-content:flex-end; }
      .btn-secondary { background:#eef8f1; color:#17653c; border:none; padding:0.6rem 0.95rem; border-radius:999px; text-decoration:none; font-weight:600; }
      .btn-secondary:hover { background:#ddeedf; color:#17653c; }
      .status-pill { display:inline-block; padding:0.35rem 0.7rem; border-radius:999px; background:#eaf6ff; color:#1d4ed8; font-size:0.85rem; font-weight:600; }
      .status-pill.pending { background:#fff3cd; color:#8a6d3b; }
      .status-pill.available { background:#e8f7ee; color:#1f6f3f; }
      .btn-primary { background:#17653c; color:#fff; border:none; padding:0.6rem 0.95rem; border-radius:999px; text-decoration:none; font-weight:600; }
      .btn-primary:hover { background:#12572f; color:#fff; }
      .sample-badge { display:inline-block; background:#eef8f1; color:#17653c; padding:0.35rem 0.7rem; border-radius:999px; font-size:0.8rem; font-weight:600; }
      .lease-modal-overlay { position:fixed; inset:0; background:rgba(8, 28, 19, 0.65); display:none; align-items:center; justify-content:center; padding:1rem; z-index:1200; }
      .lease-modal-overlay.active { display:flex; }
      .lease-modal { background:#fff; width:min(620px, 100%); border-radius:20px; box-shadow:0 18px 45px rgba(8, 28, 19, 0.24); padding:1.4rem; }
      .lease-modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; }
      .lease-modal-close { border:none; background:#f1f6f2; width:38px; height:38px; border-radius:50%; font-size:1.1rem; cursor:pointer; }
      .lease-form-grid { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:0.85rem; }
      .lease-form-group { display:flex; flex-direction:column; gap:0.35rem; }
      .lease-form-group.full { grid-column:1 / -1; }
      .lease-form-group label { font-size:0.92rem; font-weight:600; color:#2f5b3d; }
      .lease-form-group input, .lease-form-group select, .lease-form-group textarea { border:1px solid #dfeee2; border-radius:10px; padding:0.7rem 0.8rem; font-size:0.95rem; }
      .lease-form-group textarea { min-height:100px; resize:vertical; }
      .lease-modal-actions { display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1rem; }
    </style>
  </head>
  <body>
    <nav class="navbar">
      <div class="nav-container">
        <div class="logo">E-Land</div>
        <ul class="nav-menu">
          <li><a href="homepage.php" class="nav-link">Home</a></li>
          <li><a href="my-land.php" class="nav-link">My Land</a></li>
          <li><a href="land-on-sale.php" class="nav-link">Land on Sale</a></li>
          <li>
            <a href="land-on-lease.php" class="nav-link active"
              >Land on Lease</a
            >
          </li>
          <li><a href="about.php" class="nav-link">About</a></li>
        </ul>
        <div class="auth-buttons">
<?php if (isset($_SESSION['user_id'])) { ?>

    <span class="user-greeting" style="color:white; margin-right:1rem;">
        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
    </span>

    <a href="logout.php" class="btn-login">Logout</a>

<?php } else { ?>

    <a href="login.html" class="btn-login">Login</a>
    <a href="register.html" class="btn-register">Register</a>

<?php } ?>
</div>
      </div>
    </nav>

    <header class="page-header">
      <h1>Land Properties for Lease</h1>
      <p>Find land that owners have listed for lease</p>
    </header>

    <main>
      <div class="page-content">
        <div class="container">
          <div style="margin-bottom: 1.5rem">
            <h2>Available Properties for Lease</h2>
            <p>Browse ready-to-view lease opportunities and request access to lands that match your plans.</p>
            <?php if ($showingSample): ?>
              <span class="sample-badge">Showing sample lands for preview</span>
            <?php endif; ?>
          </div>

          <div class="listing-grid">
            <?php foreach ($displayListings as $listing): ?>
              <?php $status = $listing['status'] ?? 'Available'; $statusClass = strtolower(str_replace(' ', '-', $status)); ?>
              <div class="listing-card">
                <div class="d-flex justify-content-between align-items-start gap-2">
                  <h3><?php echo htmlspecialchars($listing['title_number'] ?? 'Sample Lease'); ?></h3>
                  <span class="status-pill <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></span>
                </div>
                <p><?php echo htmlspecialchars($listing['description'] ?? 'A well-positioned parcel for lease, ideal for agriculture or light development.'); ?></p>
                <ul class="meta-list">
                  <li><strong>Parcel:</strong> <?php echo htmlspecialchars($listing['parcel_number'] ?? '—'); ?></li>
                  <li><strong>County:</strong> <?php echo htmlspecialchars($listing['county'] ?? '—'); ?></li>
                  <li><strong>Location:</strong> <?php echo htmlspecialchars($listing['physical_location'] ?? '—'); ?></li>
                  <li><strong>Size:</strong> <?php echo htmlspecialchars($listing['land_size'] ?? '—'); ?></li>
                  <li><strong>Lease term:</strong> <?php echo htmlspecialchars($listing['lease_term'] ?? 'Flexible'); ?></li>
                  <li><strong>Price:</strong> <?php echo htmlspecialchars($listing['price'] ?? 'Contact owner'); ?></li>
                </ul>
                <div class="card-footer">
                  <small class="text-muted">Lease-ready listing</small>
                  <div class="card-actions">
                    <a href="verify-land.php?title_number=<?php echo urlencode($listing['title_number'] ?? ''); ?>&mode=lease" class="btn-secondary">Verify Lease</a>
                    <a href="#" class="btn-primary" onclick="requestToLease(this, '<?php echo htmlspecialchars($listing['title_number'] ?? 'sample'); ?>')">Request to Lease</a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div style="margin-top: 2rem">
            <h3>Lease Requirements</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem; background: #fff; border: 1px solid #e6f1e8; border-radius: 8px; overflow: hidden;">
              <thead>
                <tr style="background: linear-gradient(135deg, #0f5d32 0%, #1f7f49 100%); color: #fff;">
                  <th style="padding: 1rem; text-align: left; font-weight: 600; border-right: 1px solid #e6f1e8;">Requirement</th>
                  <th style="padding: 1rem; text-align: left; font-weight: 600;">Description</th>
                </tr>
              </thead>
              <tbody>
                <tr style="border-bottom: 1px solid #e6f1e8;">
                  <td style="padding: 0.9rem 1rem; font-weight: 600; color: #145132;">Valid National ID or Passport</td>
                  <td style="padding: 0.9rem 1rem; color: #5d6b61;">A clear copy of your national ID or passport for identity verification purposes.</td>
                </tr>
                <tr style="border-bottom: 1px solid #e6f1e8; background: #f9fcfa;">
                  <td style="padding: 0.9rem 1rem; font-weight: 600; color: #145132;">Proof of Income or Business Registration</td>
                  <td style="padding: 0.9rem 1rem; color: #5d6b61;">Recent payslips, tax returns, or business registration documents to verify financial stability.</td>
                </tr>
                <tr style="border-bottom: 1px solid #e6f1e8;">
                  <td style="padding: 0.9rem 1rem; font-weight: 600; color: #145132;">References from Previous Landlords</td>
                  <td style="padding: 0.9rem 1rem; color: #5d6b61;">Contact details and written references from at least 2 previous landlords confirming good tenant behavior.</td>
                </tr>
                <tr style="border-bottom: 1px solid #e6f1e8; background: #f9fcfa;">
                  <td style="padding: 0.9rem 1rem; font-weight: 600; color: #145132;">Security Deposit (3 Months Rent)</td>
                  <td style="padding: 0.9rem 1rem; color: #5d6b61;">A deposit equivalent to 3 months of rent, payable before lease commencement.</td>
                </tr>
                <tr>
                  <td style="padding: 0.9rem 1rem; font-weight: 600; color: #145132;">Signed Lease Agreement</td>
                  <td style="padding: 0.9rem 1rem; color: #5d6b61;">Agreement to sign the lease contract upon final approval and deposit confirmation.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>

    <div id="leaseModal" class="lease-modal-overlay" aria-hidden="true">
      <div class="lease-modal" role="dialog" aria-modal="true" aria-labelledby="leaseModalTitle">
        <div class="lease-modal-header">
          <div>
            <h3 id="leaseModalTitle" style="margin:0; color:#145132;">Lease Request Details</h3>
            <p style="margin:0.2rem 0 0; color:#5d6b61;">Complete the form below before your lease request is sent.</p>
          </div>
          <button type="button" class="lease-modal-close" onclick="closeLeaseModal()" aria-label="Close">×</button>
        </div>

        <form id="leaseRequestForm" onsubmit="submitLeaseRequest(event)">
          <input type="hidden" id="leasePropertyId" name="property_id" />
          <div class="lease-form-grid">
            <div class="lease-form-group">
              <label for="fullName">Full Name *</label>
              <input id="fullName" name="full_name" required />
            </div>
            <div class="lease-form-group">
              <label for="email">Email Address *</label>
              <input id="email" name="email" type="email" required />
            </div>
            <div class="lease-form-group">
              <label for="phone">Phone Number *</label>
              <input id="phone" name="phone" required />
            </div>
            <div class="lease-form-group">
              <label for="nationalId">National ID / Passport *</label>
              <input id="nationalId" name="national_id_passport" placeholder="Enter ID number or passport number" required />
            </div>
            <div class="lease-form-group full">
              <label for="proofOfIncome">Proof of Income / Business Registration *</label>
              <textarea id="proofOfIncome" name="proof_of_income" placeholder="Describe your proof of income (payslips, tax returns, business registration documents, etc.)" required></textarea>
            </div>
            <div class="lease-form-group full">
              <label for="landlordReferences">References from Previous Landlords *</label>
              <textarea id="landlordReferences" name="landlord_references" placeholder="Provide contact details and names of at least 2 previous landlords as references" required></textarea>
            </div>
            <div class="lease-form-group">
              <label for="securityDeposit">Security Deposit Amount (KES) *</label>
              <input id="securityDeposit" name="security_deposit_amount" placeholder="e.g. 150000" required />
            </div>
            <div class="lease-form-group">
              <label for="leaseDuration">Preferred Lease Duration *</label>
              <select id="leaseDuration" name="lease_duration" required>
                <option value="">-- Select Duration --</option>
                <option>6 months</option>
                <option selected>12 months</option>
                <option>18 months</option>
                <option>24 months</option>
              </select>
            </div>
            <div class="lease-form-group full">
              <label for="intendedUse">Intended Use *</label>
              <input id="intendedUse" name="intended_use" placeholder="Farming, storage, business..." required />
            </div>
            <div class="lease-form-group">
              <label for="budget">Expected Budget (KES) *</label>
              <input id="budget" name="budget" placeholder="e.g. 150000" required />
            </div>
          </div>

          <div class="lease-modal-actions">
            <button type="button" class="btn-secondary" onclick="closeLeaseModal()">Cancel</button>
            <button type="submit" class="btn-primary">Submit Request</button>
          </div>
        </form>
      </div>
    </div>
 
   <script>
let activeLeaseButton = null;

function openLeaseModal(button, propertyId) {
    activeLeaseButton = button;
    document.getElementById('leasePropertyId').value = propertyId;
    document.getElementById('leaseModal').classList.add('active');
    document.getElementById('leaseModal').setAttribute('aria-hidden', 'false');
}

function closeLeaseModal() {
    document.getElementById('leaseModal').classList.remove('active');
    document.getElementById('leaseModal').setAttribute('aria-hidden', 'true');
    document.getElementById('leaseRequestForm').reset();
}

function requestToLease(button, propertyId) {
    event.preventDefault();

    <?php if (!isset($_SESSION['user_id'])): ?>
        alert("Please login to request a lease.");
        window.location.href = "login.html";
        return;
    <?php endif; ?>

    openLeaseModal(button, propertyId);
}

function submitLeaseRequest(event) {
    event.preventDefault();

    const form = document.getElementById('leaseRequestForm');
    const formData = new FormData(form);

    fetch('submit-lease-request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const propertyId = document.getElementById('leasePropertyId').value;
            const card = activeLeaseButton.closest('.listing-card');
            const statusTag = card.querySelector('.status-pill');

            if (statusTag) {
                statusTag.className = 'status-pill pending';
                statusTag.textContent = 'Pending';
            }

            if (activeLeaseButton) {
                activeLeaseButton.textContent = 'Request Sent';
                activeLeaseButton.style.background = '#95a5a6';
                activeLeaseButton.style.cursor = 'default';
                activeLeaseButton.onclick = null;
            }

            closeLeaseModal();
            alert('Your request has been submitted');
            form.reset();
        } else {
            alert('Error: ' + (data.message || 'Failed to submit lease request'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting your request. Please try again.');
    });
}
</script>

    <footer>
      <div class="footer-container">
        <div class="footer-about">
          <h3>E-Land</h3>
          <p>Find lease-ready land opportunities in Kenya and verify each property before you commit.</p>
        </div>

        <div class="footer-links">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="about.php">About</a></li>
            <li><a href="my-land.php">My Land</a></li>
            <li><a href="land-on-sale.php">Land on Sale</a></li>
            <li><a href="register.php">Register</a></li>
          </ul>
        </div>

        <div class="footer-contact">
          <h4>Contact</h4>
          <p>Email: <a href="mailto:aolukwo@gmail.com">aolukwo@gmail.com</a></p>
          <p>Phone: <a href="tel:+254746151340">+254 746 151 340</a></p>
          <p>Address: Nairobi, Kenya</p>
        </div>

        <div class="footer-social">
          <h4>Follow Us</h4>
          <div class="footer-social-links">
            <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer">Facebook</a>
            <a href="https://x.com/" target="_blank" rel="noopener noreferrer">X</a>
            <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer">Instagram</a>
            <a href="https://www.linkedin.com/" target="_blank" rel="noopener noreferrer">LinkedIn</a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© 2026 E-Land. All rights reserved.</p>
      </div>
    </footer>
  </body>
</html>
