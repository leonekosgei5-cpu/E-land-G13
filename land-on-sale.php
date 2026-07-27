<?php
session_start();

require_once 'mongodb.php';

$records = [];
$recordError = '';

try {
    $cursor = mongo_find('land_records', ['record_type' => 'selling'], ['sort' => ['created_at' => -1]]);
    $records = $cursor->toArray();
} catch (MongoDB\Driver\Exception\MongoDBException $e) {
    $recordError = $e->getMessage();
}

$sampleListings = [
    [
        'title_number' => 'NKR/LND/2024/003411',
        'parcel_number' => 'P-401',
        'county' => 'Nairobi',
        'physical_location' => 'Kitengela, Nairobi',
        'land_size' => '0.5 acres',
        'status' => 'Available',
        'price' => 'KES 1,250,000',
        'description' => 'A compact parcel ideal for a family home, small rental units, or future development.',
        'is_sample' => true
    ],
    [
        'title_number' => 'NKR/LND/2024/004125',
        'parcel_number' => 'P-512',
        'county' => 'Kiambu',
        'physical_location' => 'Ruiru, Kiambu',
        'land_size' => '1.8 acres',
        'status' => 'Available',
        'price' => 'KES 3,600,000',
        'description' => 'A spacious parcel near schools, roads, and growing residential estates.',
        'is_sample' => true
    ],
    [
        'title_number' => 'NKR/LND/2024/004918',
        'parcel_number' => 'P-630',
        'county' => 'Machakos',
        'physical_location' => 'Mlolongo, Machakos',
        'land_size' => '2.4 acres',
        'status' => 'Available',
        'price' => 'KES 4,900,000',
        'description' => 'Strategic agricultural land with excellent access to the Nairobi corridor.',
        'is_sample' => true
    ]
];

$displayListings = $sampleListings;
if (!empty($records)) {
    foreach ($records as $record) {
        $displayListings[] = [
            'title_number' => $record->title_number ?? '—',
            'parcel_number' => $record->parcel_number ?? '—',
            'county' => $record->county ?? '—',
            'physical_location' => $record->physical_location ?? '—',
            'land_size' => $record->land_size ?? '—',
            'status' => $record->status ?? 'Available',
            'price' => $record->price ?? 'KES 2,000,000',
            'description' => $record->description ?? 'Verified land parcel listed through the E-Land record system.',
            'is_sample' => false
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land on Sale - E-Land</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: #f6fbf8; color: #123524; }
        .page-header { background: linear-gradient(135deg, #0f5d32 0%, #1f7f49 100%); color: #fff; padding: 3rem 1.5rem; text-align: center; }
        .page-content { padding: 2rem 0 3rem; }
        .listing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.2rem; }
        .listing-card { background: #fff; border: 1px solid #e6f1e8; border-radius: 18px; padding: 1.2rem; box-shadow: 0 10px 24px rgba(32, 72, 49, 0.08); display: flex; flex-direction: column; gap: 0.8rem; }
        .listing-card h3 { margin: 0; font-size: 1.05rem; color: #134d2b; }
        .listing-card p { margin: 0; color: #5d6b61; line-height: 1.5; }
        .meta-list { margin: 0; padding: 0; list-style: none; display: grid; gap: 0.4rem; color: #355744; font-size: 0.95rem; }
        .card-footer { margin-top: auto; display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; }
        .status-pill { display: inline-block; padding: 0.35rem 0.7rem; border-radius: 999px; background: #eaf6ff; color: #1d4ed8; font-size: 0.85rem; font-weight: 600; }
        .status-pill.pending { background: #fff3cd; color: #8a6d3b; }
        .status-pill.available { background: #e8f7ee; color: #1f6f3f; }
        .btn-primary { background: #17653c; color: #fff; border: none; padding: 0.6rem 0.95rem; border-radius: 999px; text-decoration: none; font-weight: 600; cursor: pointer; }
        .btn-primary:hover { background: #12572f; color: #fff; }
        .btn-secondary { background: #eef8f1; color: #17653c; border: none; padding: 0.6rem 0.95rem; border-radius: 999px; text-decoration: none; font-weight: 600; }
        .btn-secondary:hover { background: #ddeedf; color: #17653c; }
        .sample-badge { display: inline-block; background: #eef8f1; color: #17653c; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.8rem; font-weight: 600; }
        .modal-overlay { position: fixed; inset: 0; background: rgba(8, 28, 19, 0.65); display: none; align-items: center; justify-content: center; padding: 1rem; z-index: 1200; }
        .modal-overlay.active { display: flex; }
        .modal { background: #fff; width: min(620px, 100%); border-radius: 20px; box-shadow: 0 18px 45px rgba(8, 28, 19, 0.24); padding: 1.4rem; }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
        .modal-close { border: none; background: #f1f6f2; width: 38px; height: 38px; border-radius: 50%; font-size: 1.1rem; cursor: pointer; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.85rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.35rem; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: 0.92rem; font-weight: 600; color: #2f5b3d; }
        .form-group input, .form-group select, .form-group textarea { border: 1px solid #dfeee2; border-radius: 10px; padding: 0.7rem 0.8rem; font-size: 0.95rem; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1rem; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">E-Land</div>
            <ul class="nav-menu">
                <li><a href="homepage.php" class="nav-link">Home</a></li>
                <li><a href="my-land.php" class="nav-link">My Land</a></li>
                <li><a href="land-on-sale.php" class="nav-link active">Land on Sale</a></li>
                <li><a href="land-on-lease.php" class="nav-link">Land on Lease</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
            </ul>
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="user-greeting" style="color:white; margin-right:1rem;">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</span>
                    <a href="logout.php" class="btn-login">Logout</a>
                <?php else: ?>
                    <a href="login.html" class="btn-login">Login</a>
                    <a href="register.php" class="btn-register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="page-header">
        <h1>Land Properties for Sale</h1>
        <p>Review sample parcels and submit a purchase request for admin approval.</p>
    </header>

    <main>
        <div class="page-content">
            <div class="container">
                <div style="margin-bottom: 1.5rem;">
                    <h2>Available Properties for Sale</h2>
                    <p>Choose a parcel below to start your purchase request. Each request is routed to the administrator for approval.</p>
                    <span class="sample-badge">Sample properties included for preview</span>
                </div>

                <div class="listing-grid">
                    <?php foreach ($displayListings as $listing): ?>
                        <?php $status = $listing['status'] ?? 'Available'; $statusClass = strtolower(str_replace(' ', '-', $status)); ?>
                        <div class="listing-card">
                            <div style="display:flex; justify-content:space-between; align-items:start; gap:0.5rem;">
                                <h3><?php echo htmlspecialchars($listing['title_number'] ?? 'Sample Parcel'); ?></h3>
                                <span class="status-pill <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($status); ?></span>
                            </div>
                            <p><?php echo htmlspecialchars($listing['description'] ?? 'A well-positioned parcel suitable for residential or commercial use.'); ?></p>
                            <ul class="meta-list">
                                <li><strong>Parcel:</strong> <?php echo htmlspecialchars($listing['parcel_number'] ?? '—'); ?></li>
                                <li><strong>County:</strong> <?php echo htmlspecialchars($listing['county'] ?? '—'); ?></li>
                                <li><strong>Location:</strong> <?php echo htmlspecialchars($listing['physical_location'] ?? '—'); ?></li>
                                <li><strong>Size:</strong> <?php echo htmlspecialchars($listing['land_size'] ?? '—'); ?></li>
                                <li><strong>Price:</strong> <?php echo htmlspecialchars($listing['price'] ?? 'Contact owner'); ?></li>
                            </ul>
                            <div class="card-footer">
                                <small style="color:#6f8177;"><?php echo !empty($listing['is_sample']) ? 'Sample listing' : 'Verified listing'; ?></small>
                                <a href="#" class="btn-primary" onclick="requestToBuy(this, '<?php echo htmlspecialchars($listing['title_number'] ?? 'sample'); ?>')">Request to Buy</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <div id="purchaseModal" class="modal-overlay" aria-hidden="true">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="purchaseModalTitle">
            <div class="modal-header">
                <div>
                    <h3 id="purchaseModalTitle" style="margin:0; color:#145132;">Purchase Request Details</h3>
                    <p style="margin:0.2rem 0 0; color:#5d6b61;">Share your details so the admin can review and approve the purchase.</p>
                </div>
                <button type="button" class="modal-close" onclick="closePurchaseModal()" aria-label="Close">×</button>
            </div>

            <form id="purchaseRequestForm" onsubmit="submitPurchaseRequest(event)">
                <input type="hidden" id="purchasePropertyId" name="property_id" />
                <div class="form-grid">
                    <div class="form-group">
                        <label for="buyerName">Full Name *</label>
                        <input id="buyerName" name="full_name" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>" required />
                    </div>
                    <div class="form-group">
                        <label for="buyerEmail">Email Address *</label>
                        <input id="buyerEmail" name="email" type="email" required />
                    </div>
                    <div class="form-group">
                        <label for="buyerPhone">Phone Number *</label>
                        <input id="buyerPhone" name="phone" required />
                    </div>
                    <div class="form-group">
                        <label for="buyerId">National ID / Passport *</label>
                        <input id="buyerId" name="national_id_passport" required />
                    </div>
                    <div class="form-group full">
                        <label for="buyerIncome">Proof of Income / Business Registration *</label>
                        <textarea id="buyerIncome" name="proof_of_income" placeholder="Provide your recent payslips, tax returns, or registration documents" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="paymentPlan">Preferred Payment Plan</label>
                        <select id="paymentPlan" name="preferred_payment_plan">
                            <option value="Cash">Cash</option>
                            <option value="Installment">Installment</option>
                            <option value="Mortgage">Mortgage</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="budget">Budget (KES)</label>
                        <input id="budget" name="budget" placeholder="e.g. 3500000" />
                    </div>
                    <div class="form-group full">
                        <label for="notes">Additional Notes</label>
                        <textarea id="notes" name="additional_notes" placeholder="Share any information you want the admin to review"></textarea>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closePurchaseModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
let activePurchaseButton = null;

function openPurchaseModal(button, propertyId) {
    activePurchaseButton = button;
    document.getElementById('purchasePropertyId').value = propertyId;
    document.getElementById('purchaseModal').classList.add('active');
    document.getElementById('purchaseModal').setAttribute('aria-hidden', 'false');
}

function closePurchaseModal() {
    document.getElementById('purchaseModal').classList.remove('active');
    document.getElementById('purchaseModal').setAttribute('aria-hidden', 'true');
    document.getElementById('purchaseRequestForm').reset();
}

function requestToBuy(button, propertyId) {
    event.preventDefault();

    <?php if (!isset($_SESSION['user_id'])): ?>
        alert('Please login to request a property');
        window.location.href = 'login.html';
        return;
    <?php endif; ?>

    openPurchaseModal(button, propertyId);
}

function submitPurchaseRequest(event) {
    event.preventDefault();

    const form = document.getElementById('purchaseRequestForm');
    const formData = new FormData(form);

    fetch('submit-purchase-request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const card = activePurchaseButton.closest('.listing-card');
            const statusTag = card.querySelector('.status-pill');
            if (statusTag) {
                statusTag.className = 'status-pill pending';
                statusTag.textContent = 'Pending';
            }

            if (activePurchaseButton) {
                activePurchaseButton.textContent = 'Request Sent';
                activePurchaseButton.style.background = '#95a5a6';
                activePurchaseButton.style.cursor = 'default';
                activePurchaseButton.onclick = null;
            }

            closePurchaseModal();
            alert('Your purchase request has been submitted for admin review.');
            form.reset();
        } else {
            alert('Error: ' + (data.message || 'Unable to submit request'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending your request.');
    });
}
    </script>

    <footer>
        <div class="footer-container">
            <div class="footer-about">
                <h3>E-Land</h3>
                <p>Helping property seekers discover verified land opportunities in Kenya with confidence.</p>
            </div>

            <div class="footer-links">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="about.php">About</a></li>
                    <li><a href="my-land.php">My Land</a></li>
                    <li><a href="land-on-lease.php">Land on Lease</a></li>
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