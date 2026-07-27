<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

require_once __DIR__ . '/db.php';

function ensure_object_id($value)
{
    if ($value instanceof MongoDB\BSON\ObjectId) {
        return $value;
    }

    if (!is_string($value) || trim($value) === '') {
        return null;
    }

    try {
        return new MongoDB\BSON\ObjectId(trim($value));
    } catch (MongoDB\Driver\Exception\InvalidArgumentException $e) {
        return null;
    }
}

function ensure_owner_record($userId)
{
    if (!empty($_SESSION['owner_id'])) {
        $existingOwnerId = ensure_object_id($_SESSION['owner_id']);
        if ($existingOwnerId) {
            return (string) $existingOwnerId;
        }
    }

    $userObjectId = ensure_object_id($userId);
    if (!$userObjectId) {
        return null;
    }

    try {
        $existingOwner = mongo_find_one('owners', ['user_id' => $userObjectId]);
    } catch (MongoDB\Driver\Exception\MongoDBException $e) {
        $existingOwner = null;
    }

    if ($existingOwner) {
        $ownerId = (string) $existingOwner->_id;
        $_SESSION['owner_id'] = $ownerId;
        return $ownerId;
    }

    try {
        $user = mongo_find_one('users', ['_id' => $userObjectId]);
    } catch (MongoDB\Driver\Exception\MongoDBException $e) {
        $user = null;
    }

    if (!$user) {
        return null;
    }

    $ownerIdObj = new MongoDB\BSON\ObjectId();
    $ownerDocument = [
        '_id' => $ownerIdObj,
        'user_id' => $userObjectId,
        'firstname' => $user->firstname ?? '',
        'lastname' => $user->lastname ?? '',
        'email' => $user->email ?? '',
        'phone' => $user->phone ?? '',
        'created_at' => new MongoDB\BSON\UTCDateTime(),
    ];

    try {
        mongo_insert_one('owners', $ownerDocument);
        $ownerId = (string) $ownerIdObj;
        $_SESSION['owner_id'] = $ownerId;
        return $ownerId;
    } catch (MongoDB\Driver\Exception\MongoDBException $e) {
        return null;
    }
}

function find_land_record_by_title($titleNumber)
{
    try {
        return mongo_find_one('land_records', ['title_number' => $titleNumber]);
    } catch (MongoDB\Driver\Exception\MongoDBException $e) {
        return null;
    }
}

function create_land_record($ownerId, $recordType, $documentData)
{
    $document = array_merge([
        'user_type' => 'owner',
        'owner_id' => new MongoDB\BSON\ObjectId($ownerId),
        'record_type' => $recordType,
        'status' => 'Pending',
        'created_at' => new MongoDB\BSON\UTCDateTime(),
    ], $documentData);

    return mongo_update_one(
        'land_records',
        ['title_number' => $documentData['title_number']],
        $document,
        ['upsert' => true]
    );
}

function remove_land_record($recordId, $ownerId)
{
    $recordObjectId = ensure_object_id($recordId);
    $ownerObjectId = ensure_object_id($ownerId);

    if (!$recordObjectId || !$ownerObjectId) {
        return false;
    }

    try {
        mongo_delete_one('land_records', [
            '_id' => $recordObjectId,
            'owner_id' => $ownerObjectId,
        ]);
        return true;
    } catch (MongoDB\Driver\Exception\MongoDBException $e) {
        return false;
    }
}

function find_land_records($ownerId, $recordType)
{
    $ownerObjectId = ensure_object_id($ownerId);
    if (!$ownerObjectId) {
        return [];
    }

    try {
        $cursor = mongo_find(
            'land_records',
            [
                'owner_id' => $ownerObjectId,
                'record_type' => $recordType,
            ],
            ['sort' => ['created_at' => -1]]
        );
        return $cursor->toArray();
    } catch (MongoDB\Driver\Exception\MongoDBException $e) {
        return [];
    }
}

function bson_to_array($documents)
{
    return json_decode(json_encode($documents), true);
}

$successMessage = '';
$errorMessage = '';
$ownerId = ensure_owner_record($_SESSION['user_id']);

if ($ownerId === null) {
    $errorMessage = 'Unable to create or find owner record for this account.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $ownerId !== null) {
    $titleNumber = trim($_POST['title_number'] ?? '');
    $parcelNumber = trim($_POST['parcel_number'] ?? '');
    $blockNumber = trim($_POST['block_number'] ?? '');
    $county = trim($_POST['county'] ?? '');
    $subCounty = trim($_POST['sub_county'] ?? '');
    $ward = trim($_POST['ward'] ?? '');
    $physicalLocation = trim($_POST['physical_location'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');
    $landSize = trim($_POST['land_size'] ?? '');
    $landUse = trim($_POST['land_use'] ?? '');
    $registrationDate = trim($_POST['registration_date'] ?? '');
    $status = 'Pending';

    switch ($_POST['action']) {
        case 'add_property':
            $recordType = 'owned';
            break;
        case 'add_purchase':
            $recordType = 'purchasing';
            break;
        case 'add_lease':
            $recordType = 'leasing';
            break;
        case 'add_sale':
            $recordType = 'selling';
            break;
        default:
            $recordType = '';
    }

    if ($recordType === '') {
        $errorMessage = 'Invalid action provided.';
    } elseif ($titleNumber === '' || $parcelNumber === '' || $landSize === '' || $county === '') {
        $errorMessage = 'Please fill in all required fields.';
    } else {
        $existingLand = find_land_record_by_title($titleNumber);
        if ($existingLand && ((string) $existingLand->owner_id !== $ownerId)) {
            $errorMessage = 'This title number already exists for another owner.';
        } else {
            $documentData = [
                'title_number' => $titleNumber,
                'parcel_number' => $parcelNumber,
                'county' => $county,
                'land_size' => $landSize,
                'record_type' => $recordType,
                'status' => $status,
                'block_number' => $blockNumber,
                'sub_county' => $subCounty,
                'ward' => $ward,
                'physical_location' => $physicalLocation,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'land_use' => $landUse,
                'registration_date' => $registrationDate,
            ];

            try {
                create_land_record($ownerId, $recordType, $documentData);
                $successMessage = ucfirst(str_replace('_', ' ', $_POST['action'])) . ' saved successfully.';
            } catch (MongoDB\Driver\Exception\MongoDBException $e) {
                $errorMessage = 'Unable to save record: ' . $e->getMessage();
            }
        }
    }
}

if (isset($_GET['remove_id']) && $ownerId !== null) {
    $removeId = trim($_GET['remove_id']);
    $removeObjectId = ensure_object_id($removeId);
    if ($removeObjectId && remove_land_record($removeId, $ownerId)) {
        $successMessage = 'Property removed successfully.';
    } else {
        $errorMessage = 'Unable to remove property right now.';
    }
    header('Location: my-land.php');
    exit();
}

$ownedProperties = bson_to_array(find_land_records($ownerId, 'owned'));
$purchasingRecords = bson_to_array(find_land_records($ownerId, 'purchasing'));
$leasingRecords = bson_to_array(find_land_records($ownerId, 'leasing'));
$sellingRecords = bson_to_array(find_land_records($ownerId, 'selling'));
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Land - E-Land</title>
    <link rel="stylesheet" href="style.css" />
    <style>
      .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
      }
      .stat-card {
        background: linear-gradient(
          135deg,
          var(--primary-color),
          var(--secondary-color)
        );
        color: white;
        padding: 1.5rem;
        border-radius: 8px;
        text-align: center;
      }
      .stat-card h3 {
        font-size: 2rem;
        margin: 0.5rem 0;
      }
      .tab-buttons {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
      }
      .tab-btn {
        padding: 0.75rem 1.5rem;
        background-color: var(--light-color);
        border: 2px solid transparent;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
      }
      .tab-btn.active {
        background-color: var(--primary-color);
        color: white;
      }
      .tab-content {
        display: none;
      }
      .tab-content.active {
        display: block;
      }
      .chat-box {
        background-color: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        height: 400px;
        display: flex;
        flex-direction: column;
        margin-bottom: 1rem;
      }
      .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
      }
      .message {
        padding: 0.75rem 1rem;
        border-radius: 4px;
        max-width: 70%;
        word-wrap: break-word;
      }
      .message.user {
        align-self: flex-end;
        background-color: var(--primary-color);
        color: white;
      }
      .message.support {
        align-self: flex-start;
        background-color: var(--light-color);
        color: var(--text-color);
      }
      .chat-input {
        padding: 1rem;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 0.5rem;
      }
      .chat-input input {
        flex: 1;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
      }
      .form-section {
        background-color: white;
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
      }
      .property-manager {
        background: linear-gradient(135deg, #ffffff 0%, #f7fbff 100%);
        border: 1px solid #dde7f0;
        border-radius: 14px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 24px rgba(44, 62, 80, 0.06);
      }
      .property-form {
        display: grid;
        gap: 1rem;
      }
      .form-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
      }
      .property-form input,
      .property-form select {
        border: 1px solid #d8e2ea;
        border-radius: 10px;
        padding: 0.8rem 0.95rem;
      }
      .status-badge {
        display: inline-block;
        padding: 0.3rem 0.7rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.85rem;
      }
      .status-badge.owned {
        background: rgba(46, 204, 113, 0.15);
        color: #1e8449;
      }
      .status-badge.pending {
        background: rgba(243, 156, 18, 0.16);
        color: #b67700;
      }
      .status-badge.leased {
        background: rgba(52, 152, 219, 0.15);
        color: #206090;
      }
      .remove-property {
        background: none;
        border: none;
        color: #e74c3c;
        cursor: pointer;
        font-size: 0.95rem;
        padding: 0;
      }
      .empty-state {
        text-align: center;
        color: #7f8c8d;
        padding: 1.5rem;
      }
      .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        align-items: center;
        justify-content: center;
      }
      .modal.show {
        display: flex;
      }
      .modal-content {
        background-color: white;
        padding: 2rem;
        border-radius: 8px;
        max-width: 500px;
        width: 90%;
        text-align: center;
      }
      .modal-content h2 {
        color: var(--primary-color);
        margin-bottom: 1rem;
      }
      .modal-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
      }
      .modal-close {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 2rem;
        background: none;
        border: none;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.3s;
      }
      .modal-close:hover {
        color: var(--primary-color);
      }
      .modal-minimize {
        position: relative;
        font-size: 1.5rem;
        background: none;
        border: none;
        cursor: pointer;
        color: #666;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.3s;
      }
      .modal-minimize:hover {
        color: var(--primary-color);
      }
      .modal.minimized {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: auto;
        height: auto;
        background-color: transparent;
        align-items: flex-end;
        justify-content: flex-end;
        z-index: 999;
      }
      .modal.minimized .modal-content {
        max-width: 250px;
        width: auto;
        padding: 0.75rem 1rem;
        border-radius: 50px;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        background: linear-gradient(
          135deg,
          var(--primary-color),
          var(--secondary-color)
        );
        color: white;
        cursor: pointer;
      }
      .modal.minimized .modal-content h2,
      .modal.minimized .modal-content p,
      .modal.minimized .modal-content .modal-buttons,
      .modal.minimized .modal-content > div {
        display: none;
      }
      .modal.minimized .modal-content::before {
        content: "Login Alert";
        font-weight: 600;
        color: white;
      }
      .modal-content {
        position: relative;
      }
    </style>
  </head>
  <body>
    <nav class="navbar">
      <div class="nav-container">
        <div class="logo">E-Land</div>
        <ul class="nav-menu">
          <li><a href="homepage.php" class="nav-link">Home</a></li>
          <li><a href="my-land.php" class="nav-link active">My Land</a></li>
          <li><a href="verify-land.php" class="nav-link">Verify Records</a></li>
          <li><a href="land-on-sale.php" class="nav-link">Land on Sale</a></li>
          <li>
            <a href="land-on-lease.php" class="nav-link">Land on Lease</a>
          </li>
          <li><a href="about.php" class="nav-link">About</a></li>
        </ul>
        <div class="auth-buttons">
          <a href="logout.php" class="btn-login">Logout</a>
          <span
            class="user-greeting"
            id="userGreeting"
            style="color: white; margin-right: 1rem"
          ></span>
        </div>
      </div>
    </nav>

  

    <header class="page-header">
      <h1>My Land Dashboard</h1>
      <p>Manage your properties and transactions</p>
    </header>

    <main>
      <div class="dashboard-header">
        <h2>Welcome <?php echo $_SESSION['user_name']; ?></h2>
      </div>
      <div class="page-content">
        <div class="container">
          <!-- Dashboard Stats -->
          <div class="dashboard-grid" id="dashboardStats" style="display: none">
            <div class="stat-card">
              <p>My Properties</p>
              <h3 id="propertyCount">2</h3>
            </div>
            <div class="stat-card">
              <p>In Basket</p>
              <h3>1</h3>
            </div>
            <div class="stat-card">
              <p>Active Requests</p>
              <h3>3</h3>
            </div>
          </div>

          <!-- Tab Navigation -->
          <div class="tab-buttons" id="tabButtons" style="display: none">
            <button class="tab-btn active" onclick="switchTab('properties',this)">
              My Properties
            </button>
            <button class="tab-btn" onclick="switchTab('purchasing',this)">
              Properties I'm Buying
            </button>
            <button class="tab-btn" onclick="switchTab('leasing',this)">
              Properties I'm Leasing
            </button>
            <button class="tab-btn" onclick="switchTab('basket',this)">
              🛒 Basket
            </button>
            <button class="tab-btn" onclick="switchTab('sale-request',this)">
              Sell Property
            </button>
            <button class="tab-btn" onclick="switchTab('registration',this)">
              Register Land
            </button>
            <button class="tab-btn" onclick="switchTab('chat',this)">💬 Chat</button>
          </div>

          <!-- Properties Tab -->
          <div id="properties" class="tab-content active">
            <section style="margin-bottom: 3rem">
              <div
                style="
                  display: flex;
                  justify-content: space-between;
                  align-items: center;
                  margin-bottom: 1.5rem;
                  flex-wrap: wrap;
                  gap: 1rem;
                "
              >
                <div>
                  <h2 style="margin-bottom: 0.25rem">🏡 My Owned Lands</h2>
                  <p style="color: #6c757d; margin: 0">
                    Add, review, and remove your properties whenever needed.
                  </p>
                </div>
                <a href="#propertyForm" class="btn-primary">+ Add New Property</a>
              </div>

              <?php if ($successMessage !== ''): ?>
              <div style="background:#e8f7ee;color:#1f6f3f;padding:0.9rem 1rem;border-radius:8px;margin-bottom:1rem;">
                <?php echo htmlspecialchars($successMessage); ?>
              </div>
              <?php endif; ?>
              <?php if ($errorMessage !== ''): ?>
              <div style="background:#fdecea;color:#b42318;padding:0.9rem 1rem;border-radius:8px;margin-bottom:1rem;">
                <?php echo htmlspecialchars($errorMessage); ?>
              </div>
              <?php endif; ?>

              <div class="property-manager" id="propertyForm">
                <form method="post" class="property-form">
                  <input type="hidden" name="action" value="add_property" />
                  <div class="form-row">
                    <input type="text" name="title_number" placeholder="Title Number" required />
                    <input type="text" name="parcel_number" placeholder="Parcel Number" required />
                  </div>
                  <div class="form-row">
                    <input type="text" name="block_number" placeholder="Block Number" />
                    <input type="text" name="county" placeholder="County" required />
                  </div>
                  <div class="form-row">
                    <input type="text" name="sub_county" placeholder="Sub County" />
                    <input type="text" name="ward" placeholder="Ward" />
                  </div>
                  <div class="form-row">
                    <input type="text" name="physical_location" placeholder="Physical Location" />
                    <input type="text" name="latitude" placeholder="Latitude" />
                  </div>
                  <div class="form-row">
                    <input type="text" name="longitude" placeholder="Longitude" />
                    <input type="text" name="land_size" placeholder="Land Size" required />
                  </div>
                  <div class="form-row">
                    <input type="text" name="land_use" placeholder="Land Use" />
                    <input type="date" name="registration_date" />
                  </div>
                  <div class="form-row">
                    <select name="status">
                      <option value="Pending">Pending</option>
                      <option value="Verified">Verified</option>
                      <option value="Active">Active</option>
                    </select>
                  </div>
                  <button type="submit" class="btn-primary">Add Property</button>
                </form>
              </div>

              <table>
                <thead>
                  <tr>
                    <th>Title Number</th>
                    <th>Parcel Number</th>
                    <th>County</th>
                    <th>Land Size</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($ownedProperties)): ?>
                  <tr>
                    <td colspan="6" class="empty-state">No properties yet. Add your first land listing.</td>
                  </tr>
                  <?php else: ?>
                  <?php foreach ($ownedProperties as $property): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($property['title_number']); ?></td>
                    <td><?php echo htmlspecialchars($property['parcel_number']); ?></td>
                    <td><?php echo htmlspecialchars($property['county']); ?></td>
                    <td><?php echo htmlspecialchars($property['land_size']); ?></td>
                    <td><span class="status-badge owned"><?php echo htmlspecialchars($property['status']); ?></span></td>
                    <td>
                      <a href="my-land.php?remove_id=<?php echo htmlspecialchars($property['_id']['$oid'] ?? ''); ?>" class="remove-property" onclick="return confirm('Remove this property from your records?')">Remove</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </section>
          </div>

          <!-- Purchasing Tab -->
          <div id="purchasing" class="tab-content">
            <section style="margin-bottom: 3rem">
              <h2>💰 Lands I'm Buying</h2>
              <div class="property-manager" style="margin-bottom: 1.5rem;">
                <form method="post" class="property-form">
                  <input type="hidden" name="action" value="add_purchase" />
                  <div class="form-row">
                    <input type="text" name="title_number" placeholder="Title Number" required />
                    <input type="text" name="parcel_number" placeholder="Parcel Number" required />
                  </div>
                  <div class="form-row">
                    <input type="text" name="land_size" placeholder="Land Size" required />
                    <input type="text" name="county" placeholder="County" required />
                  </div>
                  <button type="submit" class="btn-primary">Save Purchase Request</button>
                </form>
              </div>
              <table>
                <thead>
                  <tr>
                    <th>Title Number</th>
                    <th>Parcel Number</th>
                    <th>County</th>
                    <th>Land Size</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($purchasingRecords)): ?>
                  <tr><td colspan="5" class="empty-state">No purchase requests yet.</td></tr>
                  <?php else: ?>
                  <?php foreach ($purchasingRecords as $record): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($record['title_number']); ?></td>
                    <td><?php echo htmlspecialchars($record['parcel_number']); ?></td>
                    <td><?php echo htmlspecialchars($record['county']); ?></td>
                    <td><?php echo htmlspecialchars($record['land_size']); ?></td>
                    <td><span class="status-badge pending"><?php echo htmlspecialchars($record['status']); ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </section>
          </div>

          <!-- Leasing Tab -->
          <div id="leasing" class="tab-content">
            <section style="margin-bottom: 3rem">
              <h2>🔑 Lands I'm Leasing</h2>
              <div class="property-manager" style="margin-bottom: 1.5rem;">
                <form method="post" class="property-form">
                  <input type="hidden" name="action" value="add_lease" />
                  <div class="form-row">
                    <input type="text" name="title_number" placeholder="Title Number" required />
                    <input type="text" name="parcel_number" placeholder="Parcel Number" required />
                  </div>
                  <div class="form-row">
                    <input type="text" name="land_size" placeholder="Land Size" required />
                    <input type="text" name="county" placeholder="County" required />
                  </div>
                  <button type="submit" class="btn-primary">Save Lease Request</button>
                </form>
              </div>
              <table>
                <thead>
                  <tr>
                    <th>Title Number</th>
                    <th>Parcel Number</th>
                    <th>County</th>
                    <th>Land Size</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($leasingRecords)): ?>
                  <tr><td colspan="5" class="empty-state">No lease requests yet.</td></tr>
                  <?php else: ?>
                  <?php foreach ($leasingRecords as $record): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($record['title_number']); ?></td>
                    <td><?php echo htmlspecialchars($record['parcel_number']); ?></td>
                    <td><?php echo htmlspecialchars($record['county']); ?></td>
                    <td><?php echo htmlspecialchars($record['land_size']); ?></td>
                    <td><span class="status-badge pending"><?php echo htmlspecialchars($record['status']); ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </section>
          </div>

          <!-- Basket Tab -->
          <div id="basket" class="tab-content">
            <section style="margin-bottom: 3rem">
              <h2>🛒 My Basket</h2>
              <p>Properties you've selected to buy</p>
              <div style="margin-top: 1.5rem">
                <div
                  style="
                    background-color: var(--light-color);
                    padding: 1rem;
                    border-radius: 4px;
                    margin-bottom: 1rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                  "
                >
                  <div>
                    <h4 style="margin: 0; color: var(--dark-color)">
                      Land in Nairobi, Kileleshwa
                    </h4>
                    <p
                      style="
                        margin: 0.5rem 0 0 0;
                        color: #666;
                        font-size: 0.9rem;
                      "
                    >
                      1.5 acres • KES 45,000,000 • Listed by John Kariuki
                    </p>
                  </div>
                  <div style="display: flex; gap: 0.5rem">
                    <a
                      href="#"
                      style="
                        background-color: var(--primary-color);
                        color: white;
                        padding: 0.5rem 1rem;
                        border-radius: 4px;
                        text-decoration: none;
                      "
                      >Request</a
                    >
                    <a
                      href="#"
                      style="
                        background-color: #e74c3c;
                        color: white;
                        padding: 0.5rem 1rem;
                        border-radius: 4px;
                        text-decoration: none;
                      "
                      >Remove</a
                    >
                  </div>
                </div>
              </div>
            </section>
          </div>

          <!-- Sale Request Tab -->
          <div id="sale-request" class="tab-content">
            <div class="form-section">
              <h2>List Your Property for Sale</h2>
              <form method="post">
                <input type="hidden" name="action" value="add_sale" />
                <div
                  style="
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 1rem;
                  "
                >
                  <div class="form-group">
                    <label for="saleTitleNumber">Title Number</label>
                    <input type="text" id="saleTitleNumber" name="title_number" required placeholder="e.g., LR/12345/2024" />
                  </div>
                  <div class="form-group">
                    <label for="saleParcelNumber">Parcel Number</label>
                    <input type="text" id="saleParcelNumber" name="parcel_number" required placeholder="e.g., 4567" />
                  </div>
                </div>
                <div class="form-group">
                  <label for="saleCounty">County</label>
                  <input type="text" id="saleCounty" name="county" required placeholder="e.g., Nairobi" />
                </div>
                <div class="form-group">
                  <label for="saleSize">Land Size</label>
                  <input type="text" id="saleSize" name="land_size" required placeholder="e.g., 2.5 acres" />
                </div>
                <button type="submit" class="btn-primary" style="width: 100%">Submit Sale Request</button>
              </form>
            </div>

            <div class="form-section">
              <h2>Recent Sale Requests</h2>
              <table>
                <thead>
                  <tr>
                    <th>Title Number</th>
                    <th>Parcel Number</th>
                    <th>County</th>
                    <th>Land Size</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($sellingRecords)): ?>
                  <tr><td colspan="5" class="empty-state">No sale requests yet.</td></tr>
                  <?php else: ?>
                  <?php foreach ($sellingRecords as $record): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($record['title_number']); ?></td>
                    <td><?php echo htmlspecialchars($record['parcel_number']); ?></td>
                    <td><?php echo htmlspecialchars($record['county']); ?></td>
                    <td><?php echo htmlspecialchars($record['land_size']); ?></td>
                    <td><span class="status-badge pending"><?php echo htmlspecialchars($record['status']); ?></span></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Land Registration Tab -->
          <div id="registration" class="tab-content">
            <div class="form-section">
              <h2>Request Land Registration</h2>
              <form onsubmit="handleRegistrationRequest(event)">
                <div
                  style="
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 1rem;
                  "
                >
                  <div class="form-group">
                    <label for="regLocation">Property Location</label>
                    <input
                      type="text"
                      id="regLocation"
                      required
                      placeholder="e.g., Nairobi, Westlands"
                    />
                  </div>
                  <div class="form-group">
                    <label for="regSize">Property Size (acres)</label>
                    <input
                      type="number"
                      id="regSize"
                      required
                      placeholder="e.g., 2.5"
                      step="0.1"
                    />
                  </div>
                </div>
                <div class="form-group">
                  <label for="regTitle">Current Ownership Status</label>
                  <select id="regTitle" required>
                    <option value="">Select status</option>
                    <option value="inherited">Inherited</option>
                    <option value="purchased">Purchased</option>
                    <option value="allocated">Allocated by Government</option>
                    <option value="customary">Customary Rights</option>
                  </select>
                </div>
                <div class="form-group">
                  <label for="regDescription">Property Details</label>
                  <textarea
                    id="regDescription"
                    required
                    placeholder="Provide detailed information..."
                    style="min-height: 120px"
                  ></textarea>
                </div>
                <div class="form-group">
                  <label for="regEvidence"
                    >Attach Ownership Evidence (PDF)</label
                  >
                  <input type="file" id="regEvidence" accept=".pdf" required />
                </div>
                <div class="form-group">
                  <label for="regSurvey">Attach Survey Map (PDF)</label>
                  <input type="file" id="regSurvey" accept=".pdf" required />
                </div>
                <button type="submit" class="btn-primary" style="width: 100%">
                  Submit Registration Request
                </button>
              </form>
            </div>
          </div>

          <!-- Chat Tab -->
          <div id="chat" class="tab-content">
            <section style="margin-bottom: 3rem">
              <h2>💬 Chat with Land Office</h2>
              <div class="chat-box">
                <div class="chat-messages" id="chatMessages">
                  <div class="message support">
                    <strong>Land Office Support:</strong><br />
                    Hello! How can we assist you today?
                  </div>
                </div>
                <div class="chat-input">
                  <input
                    type="text"
                    id="messageInput"
                    placeholder="Type your message here..."
                  />
                  <button type="button" onclick="sendMessage()">Send</button>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </main>

    <footer>
      <div class="footer-container">
        <div class="footer-about">
          <h3>E-Land</h3>
          <p>Trusted land verification and property discovery for Kenyan buyers, owners, and tenants.</p>
        </div>

        <div class="footer-links">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="about.php">About</a></li>
            <li><a href="land-on-sale.php">Land on Sale</a></li>
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

    <script>
function switchTab(tabName, button) {
  const contents = document.querySelectorAll(".tab-content");
  contents.forEach((content) => content.classList.remove("active"));

  const buttons = document.querySelectorAll(".tab-btn");
  buttons.forEach((btn) => btn.classList.remove("active"));

  document.getElementById(tabName).classList.add("active");
  button.classList.add("active");
}

function sendMessage() {
  const input = document.getElementById("messageInput");
  const message = input.value.trim();

  if (!message) return;

  const chatMessages = document.getElementById("chatMessages");

  const userMsg = document.createElement("div");
  userMsg.className = "message user";
  userMsg.textContent = message;

  chatMessages.appendChild(userMsg);

  input.value = "";
  chatMessages.scrollTop = chatMessages.scrollHeight;

  setTimeout(() => {
    const supportMsg = document.createElement("div");
    supportMsg.className = "message support";
    supportMsg.innerHTML =
      "<strong>Land Office Support:</strong><br>Thank you for your message. We will respond shortly.";

    chatMessages.appendChild(supportMsg);
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }, 1000);
}

function handleSaleRequest(event) {
  event.preventDefault();

  alert("Your land sale request has been submitted successfully!");

  event.target.reset();
}

function handleRegistrationRequest(event) {
  event.preventDefault();

  alert(
    "Your land registration request has been submitted! You will receive updates via email/SMS."
  );

  event.target.reset();
}

window.addEventListener("load", function () {
  console.log("Dashboard loaded");
});
</script>
  </body>
</html>
