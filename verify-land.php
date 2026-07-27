<?php
session_start();
require_once __DIR__ . '/mongodb.php';

$searchTerm = trim($_POST['title_number'] ?? $_GET['title_number'] ?? '');
$validationError = '';
$result = null;
$verificationMode = strtolower($_POST['mode'] ?? $_GET['mode'] ?? '') === 'lease' ? 'lease' : 'buy';
$recommendation = $verificationMode === 'lease' ? 'Safe to Lease' : 'Safe to Buy';
$fallbackLandCatalog = [
    'NKR/LND/2024/001256' => [
        'title_number' => 'NKR/LND/2024/001256',
        'parcel_number' => 'P-101',
        'county' => 'Nairobi',
        'physical_location' => 'Kibera, Nairobi',
        'land_size' => '0.75 acres',
        'status' => 'Available',
        'record_type' => 'leasing',
        'owner' => 'Sample Lease Owner',
    ],
    'NKR/LND/2024/001789' => [
        'title_number' => 'NKR/LND/2024/001789',
        'parcel_number' => 'P-205',
        'county' => 'Kiambu',
        'physical_location' => 'Ruiru, Kiambu',
        'land_size' => '1.2 acres',
        'status' => 'Available',
        'record_type' => 'leasing',
        'owner' => 'Sample Lease Owner',
    ],
    'NKR/LND/2024/002314' => [
        'title_number' => 'NKR/LND/2024/002314',
        'parcel_number' => 'P-340',
        'county' => 'Machakos',
        'physical_location' => 'Mlolongo, Machakos',
        'land_size' => '2.5 acres',
        'status' => 'Available',
        'record_type' => 'leasing',
        'owner' => 'Sample Lease Owner',
    ],
];

if ($searchTerm !== '') {
    if (!preg_match('/^[A-Za-z0-9]+\/[A-Za-z0-9]+\/\d{4}\/\d{6}$/', $searchTerm)) {
        $validationError = 'Please enter a valid title number in the format NKR/LND/2024/001256.';
    } else {
        try {
            $landDoc = mongo_find_one('land_records', ['title_number' => $searchTerm]);
        } catch (MongoDB\Driver\Exception\MongoDBException $e) {
            $landDoc = null;
            $validationError = 'Unable to query land records: ' . $e->getMessage();
        }

        $land = null;
        if ($landDoc) {
            $land = json_decode(json_encode($landDoc), true);
        }

        $fallbackLand = null;
        if (!$land && $verificationMode === 'lease') {
            $fallbackLand = $fallbackLandCatalog[$searchTerm] ?? null;
        }
        $land = $land ?: $fallbackLand;

        if ($land) {
            $owner = null;
            if (!empty($land['owner_id'])) {
                try {
                    $ownerDoc = mongo_find_one('owners', ['id' => (int) $land['owner_id']]);
                    $owner = $ownerDoc ? json_decode(json_encode($ownerDoc), true) : null;
                } catch (MongoDB\Driver\Exception\MongoDBException $e) {
                    $owner = null;
                }
            }

            try {
                $historyCursor = mongo_find('ownership_history', ['title_number' => $searchTerm], ['sort' => ['transfer_date' => -1]]);
                $history = [];
                foreach ($historyCursor as $doc) {
                    $row = json_decode(json_encode($doc), true);
                    $history[] = [
                        'title_number' => $row['title_number'] ?? '',
                        'previous_owner' => $row['previous_owner'] ?? $row['owner_name'] ?? '',
                        'new_owner' => $row['new_owner'] ?? '',
                        'transfer_date' => $row['transfer_date'] ?? '',
                        'transfer_type' => $row['transfer_type'] ?? '',
                        'reason' => $row['reason'] ?? $row['transfer_reason'] ?? '',
                    ];
                }
            } catch (MongoDB\Driver\Exception\MongoDBException $e) {
                $history = [];
            }

            try {
                $restrictionCursor = mongo_find('restrictions', ['title_number' => $searchTerm], ['sort' => ['created_at' => -1]]);
                $restrictions = [];
                foreach ($restrictionCursor as $doc) {
                    $restrictions[] = json_decode(json_encode($doc), true);
                }
            } catch (MongoDB\Driver\Exception\MongoDBException $e) {
                $restrictions = [];
            }

            try {
                $caseCursor = mongo_find('land_cases', ['title_number' => $searchTerm], ['sort' => ['hearing_date' => -1]]);
                $cases = [];
                foreach ($caseCursor as $doc) {
                    $cases[] = json_decode(json_encode($doc), true);
                }
            } catch (MongoDB\Driver\Exception\MongoDBException $e) {
                $cases = [];
            }

            try {
                $surveyCursor = mongo_find('survey_plans', ['title_number' => $searchTerm], ['sort' => ['uploaded_at' => -1], 'limit' => 5]);
                $surveys = [];
                foreach ($surveyCursor as $doc) {
                    $surveys[] = json_decode(json_encode($doc), true);
                }
            } catch (MongoDB\Driver\Exception\MongoDBException $e) {
                $surveys = [];
            }

            try {
                $imageCursor = mongo_find('land_images', ['title_number' => $searchTerm], ['sort' => ['uploaded_at' => -1], 'limit' => 5]);
                $images = [];
                foreach ($imageCursor as $doc) {
                    $images[] = json_decode(json_encode($doc), true);
                }
            } catch (MongoDB\Driver\Exception\MongoDBException $e) {
                $images = [];
            }

            $hasIssue = false;
            if (!empty($restrictions)) {
                $hasIssue = true;
            }
            if (!empty($cases)) {
                foreach ($cases as $c) {
                    if (strtolower($c['status'] ?? '') !== 'closed') {
                        $hasIssue = true;
                    }
                }
            }

            $recommendation = $hasIssue
                ? ($verificationMode === 'lease' ? 'Lease with Caution' : 'Buy with Caution')
                : ($verificationMode === 'lease' ? 'Safe to Lease' : 'Safe to Buy');
            if ($hasIssue && (!empty($restrictions) || !empty($cases))) {
                $recommendation = $verificationMode === 'lease' ? 'Do Not Lease' : 'Do Not Buy';
            }

            $result = [
                'land' => $land,
                'owner' => $owner,
                'history' => $history,
                'restrictions' => $restrictions,
                'cases' => $cases,
                'surveys' => $surveys,
                'images' => $images,
                'recommendation' => $recommendation,
            ];
            $landSummary = $result['land'] ?? [
                'title_number' => $searchTerm,
                'parcel_number' => '-',
                'county' => '-',
                'physical_location' => '-',
                'land_size' => '-',
                'status' => '-',
                'record_type' => $verificationMode === 'lease' ? 'Leasing' : 'Selling',
            ];

            $hasPersistedLandRecord = !empty($land['id']);
            if ($hasPersistedLandRecord) {
                $userId = 13;
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                try {
                    mongo_insert_one('verification_logs', [
                        'title_number' => $searchTerm,
                        'verification_result' => $recommendation,
                        'user_id' => $userId,
                        'ip_address' => $ipAddress,
                        'created_at' => new MongoDB\BSON\UTCDateTime()
                    ]);
                } catch (MongoDB\Driver\Exception\MongoDBException $e) {
                    // Logging failure should not block user verification page.
                }
            }
        } else {
            $validationError = 'No land record was found for that title number.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Land - E-Land</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f6fbf8;
            color: #123524;
        }

        .page-header {
            background: linear-gradient(135deg, #0f5d32 0%, #1f7f49 100%);
            color: white;
            padding: 3rem 1.5rem;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            font-size: 1.05rem;
            opacity: 0.9;
            margin: 0;
        }

        .page-content {
            padding: 2rem 0 3rem;
        }

        .search-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(15, 93, 50, 0.1);
            border: 1px solid #e6f1e8;
        }

        .search-form {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            color: #145132;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .form-group input {
            padding: 0.8rem 1rem;
            border: 2px solid #e6f1e8;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #0f5d32;
            box-shadow: 0 0 0 3px rgba(15, 93, 50, 0.1);
        }

        .btn-verify {
            background: linear-gradient(135deg, #0f5d32 0%, #1f7f49 100%);
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
        }

        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(15, 93, 50, 0.3);
        }

        .result-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(15, 93, 50, 0.1);
            border: 1px solid #e6f1e8;
            margin-bottom: 2rem;
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #e6f1e8;
        }

        .result-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #145132;
            margin: 0;
        }

        .status-badge {
            display: inline-block;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .status-badge.safe {
            background: #e8f8ee;
            color: #1c7a44;
        }

        .status-badge.caution {
            background: #fff6df;
            color: #9a6200;
        }

        .status-badge.danger {
            background: #ffe7e7;
            color: #b13232;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: linear-gradient(135deg, #f9fcfa 0%, #edf7f0 100%);
            border: 1px solid #dcefe2;
            border-radius: 12px;
            padding: 1.25rem 1.4rem;
            margin-bottom: 1.5rem;
        }

        .summary-card h3 {
            margin: 0 0 0.9rem;
            color: #145132;
            font-size: 1.1rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.9rem;
        }

        .summary-item {
            background: white;
            border-radius: 10px;
            padding: 0.8rem;
            border: 1px solid #e6f1e8;
        }

        .summary-label {
            display: block;
            font-size: 0.8rem;
            color: #6f8177;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.35rem;
        }

        .summary-value {
            font-weight: 700;
            color: #145132;
        }

        .info-section {
            background: #f9fcfa;
            border: 1px solid #e6f1e8;
            border-radius: 10px;
            padding: 1.5rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f5d32;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: #1f7f49;
            font-size: 1.2rem;
        }

        .info-item {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 1rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid #e6f1e8;
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            font-weight: 600;
            color: #145132;
        }

        .info-value {
            color: #5d6b61;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
        }

        .alert-error {
            background: #ffe7e7;
            color: #b13232;
            border-left-color: #b13232;
        }

        .history-item {
            background: white;
            padding: 1rem;
            margin-bottom: 0.8rem;
            border-radius: 8px;
            border-left: 4px solid #0f5d32;
        }

        .history-date {
            font-weight: 700;
            color: #0f5d32;
        }

        .history-detail {
            color: #5d6b61;
            font-size: 0.95rem;
            margin-top: 0.3rem;
        }

        .document-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .document-list li {
            padding: 0.5rem 0;
        }

        .document-list a {
            color: #0f5d32;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }

        .document-list a:hover {
            color: #1f7f49;
            text-decoration: underline;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e6f1e8;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: #0f5d32;
            color: white;
        }

        .btn-primary:hover {
            background: #1f7f49;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #e6f1e8;
            color: #0f5d32;
            border: 2px solid #0f5d32;
        }

        .btn-secondary:hover {
            background: #0f5d32;
            color: white;
        }

        .map-container {
            border-radius: 10px;
            overflow: hidden;
            margin-top: 1.5rem;
        }

        .map-container iframe {
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 1.8rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .result-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-item {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo"><a href="homepage.php">E-Land</a></div>
            <ul class="nav-menu">
                <li><a href="homepage.php" class="nav-link">Home</a></li>
                <li><a href="my-land.php" class="nav-link">My Land</a></li>
                <li><a href="land-on-sale.php" class="nav-link">Land on Sale</a></li>
                <li><a href="land-on-lease.php" class="nav-link">Land on Lease</a></li>
                <li><a href="properties.php" class="nav-link">Properties</a></li>
                <li><a href="verify-land.php" class="nav-link active">Verify Land</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
            </ul>
            <div class="auth-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="user-greeting" style="color:white; margin-right:1rem;">
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                    <a href="logout.php" class="btn-login">Logout</a>
                <?php else: ?>
                    <a href="login.html" class="btn-login">Login</a>
                    <a href="register.html" class="btn-register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="page-header">
        <h1><i class="fas fa-check-circle"></i> Verify Land</h1>
        <p><?php echo $verificationMode === 'lease' ? 'Check land ownership, restrictions, court cases, and documentation before leasing.' : 'Check land ownership, restrictions, court cases, and documentation before buying.'; ?></p>
    </header>

    <!-- Main Content -->
    <main class="page-content">
        <div class="container">
            <!-- Search Card -->
            <div class="search-card">
                <h2 style="margin-top: 0; color: #145132; margin-bottom: 1.5rem;">
                    <i class="fas fa-search"></i> <?php echo $verificationMode === 'lease' ? 'Verify Lease Eligibility' : 'Search by Title Number'; ?>
                </h2>
                <form method="post" class="search-form">
                    <input type="hidden" name="mode" value="<?php echo htmlspecialchars($verificationMode, ENT_QUOTES); ?>">
                    <div class="form-group">
                        <label for="title_number">Title Number <span style="color: #d32f2f;">*</span></label>
                        <input 
                            type="text" 
                            id="title_number"
                            name="title_number" 
                            value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES); ?>" 
                            placeholder="e.g. NKR/LND/2024/001256"
                            pattern="^[A-Za-z0-9]+\/[A-Za-z0-9]+\/\d{4}\/\d{6}$"
                            title="Format: NKR/LND/2024/001256"
                            required
                        >
                    </div>
                    <button type="submit" class="btn-verify">
                        <i class="fas fa-search"></i> Verify Land
                    </button>
                </form>
            </div>

            <?php if ($validationError): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($validationError); ?>
                </div>
            <?php endif; ?>

            <?php if ($result): ?>
                <div class="result-card">
                    <div class="result-header">
                        <h2 class="result-title">
                            <i class="fas fa-file-certificate"></i> Verification Result
                        </h2>
                        <span class="status-badge <?php echo 
                            in_array($result['recommendation'], ['Safe to Buy', 'Safe to Lease'], true) ? 'safe' : 
                            (in_array($result['recommendation'], ['Buy with Caution', 'Lease with Caution'], true) ? 'caution' : 'danger'); 
                        ?>">
                            <?php echo htmlspecialchars($result['recommendation']); ?>
                        </span>
                    </div>

                    <div class="summary-card">
                        <h3><i class="fas fa-home"></i> Land Details Summary</h3>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <span class="summary-label">Title Number</span>
                                <span class="summary-value"><?php echo htmlspecialchars($landSummary['title_number'] ?? '-'); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Parcel Number</span>
                                <span class="summary-value"><?php echo htmlspecialchars($landSummary['parcel_number'] ?? '-'); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">County</span>
                                <span class="summary-value"><?php echo htmlspecialchars($landSummary['county'] ?? '-'); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Location</span>
                                <span class="summary-value"><?php echo htmlspecialchars($landSummary['physical_location'] ?? '-'); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Land Size</span>
                                <span class="summary-value"><?php echo htmlspecialchars($landSummary['land_size'] ?? '-'); ?></span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Record Type</span>
                                <span class="summary-value"><?php echo htmlspecialchars(ucfirst($landSummary['record_type'] ?? ($verificationMode === 'lease' ? 'Leasing' : 'Selling'))); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Land Details Section -->
                    <div class="info-grid">
                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-map"></i> Land Details
                            </div>
                            <div class="info-item">
                                <span class="info-label">Title Number</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['land']['title_number'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Parcel Number</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['land']['parcel_number'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">County</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['land']['county'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Location</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['land']['physical_location'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Land Size</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['land']['land_size'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['land']['status'] ?? '-'); ?></span>
                            </div>
                        </div>

                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-user"></i> Owner Information
                            </div>
                            <div class="info-item">
                                <span class="info-label">Owner Name</span>
                                <span class="info-value"><?php echo htmlspecialchars(trim(($result['owner']['firstname'] ?? '') . ' ' . ($result['owner']['lastname'] ?? '') . ' ' . ($result['land']['owner'] ?? ''))); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">National ID</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['owner']['national_id'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Phone</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['owner']['phone'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['owner']['email'] ?? '-'); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Address</span>
                                <span class="info-value"><?php echo htmlspecialchars($result['owner']['address'] ?? '-'); ?></span>
                            </div>
                        </div>

                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-history"></i> Ownership History
                            </div>
                            <?php if (!empty($result['history'])): ?>
                                <?php foreach (array_slice($result['history'], 0, 3) as $item): ?>
                                    <div class="history-item">
                                        <div class="history-date"><?php echo htmlspecialchars($item['transfer_date'] ?? '-'); ?></div>
                                        <div class="history-detail">
                                            <?php echo htmlspecialchars($item['previous_owner'] ?? '-'); ?> → <?php echo htmlspecialchars($item['new_owner'] ?? '-'); ?>
                                        </div>
                                        <?php if (!empty($item['reason'])): ?>
                                            <small style="color: #999;"><?php echo htmlspecialchars($item['reason']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #999; margin: 0;">No ownership history found.</p>
                            <?php endif; ?>
                        </div>

                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-ban"></i> Restrictions
                            </div>
                            <?php if (!empty($result['restrictions'])): ?>
                                <?php foreach ($result['restrictions'] as $item): ?>
                                    <div style="padding: 0.8rem 0; border-bottom: 1px solid #e6f1e8;">
                                        <strong style="color: #0f5d32;"><?php echo htmlspecialchars($item['restriction_type'] ?? '-'); ?></strong>
                                        <p style="margin: 0.3rem 0 0; color: #5d6b61;"><?php echo htmlspecialchars($item['description'] ?? '-'); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #999; margin: 0;">No restrictions recorded.</p>
                            <?php endif; ?>
                        </div>

                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-gavel"></i> Court Cases
                            </div>
                            <?php if (!empty($result['cases'])): ?>
                                <?php foreach ($result['cases'] as $item): ?>
                                    <div style="padding: 0.8rem 0; border-bottom: 1px solid #e6f1e8;">
                                        <strong style="color: #0f5d32;"><?php echo htmlspecialchars($item['case_number'] ?? '-'); ?></strong>
                                        <p style="margin: 0.3rem 0 0; color: #5d6b61;">
                                            <strong><?php echo htmlspecialchars($item['court_name'] ?? '-'); ?></strong><br>
                                            Status: <?php echo htmlspecialchars($item['status'] ?? '-'); ?> | Hearing: <?php echo htmlspecialchars($item['hearing_date'] ?? '-'); ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p style="color: #999; margin: 0;">No court cases recorded.</p>
                            <?php endif; ?>
                        </div>

                        <div class="info-section">
                            <div class="section-title">
                                <i class="fas fa-file-alt"></i> Documents
                            </div>
                            <?php if (!empty($result['surveys']) || !empty($result['images'])): ?>
                                <?php if (!empty($result['surveys'])): ?>
                                    <p style="margin-bottom: 1rem;"><strong>Survey Plans</strong></p>
                                    <ul class="document-list">
                                        <?php foreach ($result['surveys'] as $item): ?>
                                            <li>
                                                <a href="uploads/survey/<?php echo urlencode($item['filename']); ?>" target="_blank">
                                                    <i class="fas fa-file-pdf"></i> <?php echo htmlspecialchars($item['filename']); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                                <?php if (!empty($result['images'])): ?>
                                    <p style="margin-bottom: 1rem; margin-top: 1rem;"><strong>Land Images</strong></p>
                                    <ul class="document-list">
                                        <?php foreach ($result['images'] as $item): ?>
                                            <li>
                                                <a href="uploads/land/<?php echo urlencode($item['filename']); ?>" target="_blank">
                                                    <i class="fas fa-image"></i> <?php echo htmlspecialchars($item['filename']); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php else: ?>
                                <p style="color: #999; margin: 0;">No documents available.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Map Section -->
                    <?php if (!empty($result['land']['latitude']) && !empty($result['land']['longitude'])): ?>
                        <div class="map-container">
                            <div class="section-title" style="margin-bottom: 1rem;">
                                <i class="fas fa-map-location-dot"></i> Location Map
                            </div>
                            <iframe
                                width="100%"
                                height="350"
                                style="border:0;border-radius:10px;"
                                loading="lazy"
                                allowfullscreen
                                src="https://www.openstreetmap.org/export/embed.html?bbox=<?php echo urlencode($result['land']['longitude'] - 0.01); ?>%2C<?php echo urlencode($result['land']['latitude'] - 0.01); ?>%2C<?php echo urlencode($result['land']['longitude'] + 0.01); ?>%2C<?php echo urlencode($result['land']['latitude'] + 0.01); ?>&layer=mapnik&marker=<?php echo urlencode($result['land']['latitude']); ?>%2C<?php echo urlencode($result['land']['longitude']); ?>">
                            </iframe>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="generate_verification_report.php?title_number=<?php echo urlencode($searchTerm); ?>" target="_blank" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download PDF Report
                        </a>
                        <a href="verify-land.php" class="btn btn-secondary">
                            <i class="fas fa-search"></i> Search Again
                        </a>
                        <a href="properties.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i> View Properties
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer>
      <div class="footer-container">
        <div class="footer-about">
          <h3>E-Land</h3>
          <p>Verify land ownership and details before making investment decisions.</p>
        </div>

        <div class="footer-links">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="land-on-sale.php">Buy Land</a></li>
            <li><a href="land-on-lease.php">Lease Land</a></li>
            <li><a href="verify-land.php">Verify Land</a></li>
          </ul>
        </div>

        <div class="footer-contact">
          <h4>Contact</h4>
          <p>Email: <a href="mailto:info@e-land.com">info@e-land.com</a></p>
          <p>Phone: <a href="tel:+254700000000">+254 (0) 700 000 000</a></p>
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
        <p>&copy; 2026 E-Land. All rights reserved.</p>
      </div>
    </footer>
</body>
</html>