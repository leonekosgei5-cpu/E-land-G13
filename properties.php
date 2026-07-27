<?php
session_start();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Properties - E-Land | Buy, Sell & Lease Land in Kenya</title>
    <link rel="stylesheet" href="style.css" />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <style>
      /* Properties Page Specific Styles */
      .properties-header {
        background: linear-gradient(135deg, #0f5d32 0%, #1f7f49 100%);
        color: white;
        padding: 3rem 0;
        margin-bottom: 2rem;
      }

      .properties-header h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
      }

      .properties-header p {
        font-size: 1.1rem;
        opacity: 0.9;
      }

      /* Filter Section */
      .filters-section {
        background: white;
        padding: 2rem;
        margin-bottom: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      }

      .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
      }

      .filter-group {
        display: flex;
        flex-direction: column;
      }

      .filter-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #2c3e50;
        font-size: 0.9rem;
      }

      .filter-group input,
      .filter-group select {
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        transition: border-color 0.3s;
      }

      .filter-group input:focus,
      .filter-group select:focus {
        outline: none;
        border-color: #0f5d32;
        box-shadow: 0 0 5px rgba(15, 93, 50, 0.3);
      }

      .filter-btn {
        background-color: #0f5d32;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
        align-self: flex-end;
        margin-top: auto;
      }

      .filter-btn:hover {
        background-color: #1f7f49;
      }

      /* Properties Grid */
      .properties-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
      }

      .property-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        cursor: pointer;
      }

      .property-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      }

      .property-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
        position: relative;
        background: #f0f0f0;
      }

      .property-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #0f5d32;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-weight: 600;
        font-size: 0.85rem;
      }

      .property-content {
        padding: 1.5rem;
      }

      .property-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.5rem;
      }

      .property-location {
        color: #7f8c8d;
        font-size: 0.95rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      .property-location i {
        color: #0f5d32;
      }

      .property-details {
        display: flex;
        justify-content: space-around;
        margin-bottom: 1.5rem;
        padding: 1rem 0;
        border-top: 1px solid #ecf0f1;
        border-bottom: 1px solid #ecf0f1;
      }

      .detail-item {
        text-align: center;
      }

      .detail-item i {
        color: #0f5d32;
        font-size: 1.3rem;
        margin-bottom: 0.3rem;
      }

      .detail-value {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.9rem;
      }

      .detail-label {
        color: #95a5a6;
        font-size: 0.75rem;
        margin-top: 0.3rem;
      }

      .property-price {
        font-size: 1.8rem;
        font-weight: 700;
        color: #0f5d32;
        margin-bottom: 1rem;
      }

      .property-price small {
        font-size: 0.7rem;
        color: #95a5a6;
        font-weight: normal;
      }

      .property-description {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        line-height: 1.5;
      }

      .property-actions {
        display: flex;
        gap: 0.5rem;
      }

      .btn-view,
      .btn-contact {
        flex: 1;
        padding: 0.7rem;
        border: none;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 0.9rem;
      }

      .btn-view {
        background-color: #0f5d32;
        color: white;
      }

      .btn-view:hover {
        background-color: #1f7f49;
      }

      .btn-contact {
        background-color: #ecf0f1;
        color: #2c3e50;
        border: 1px solid #bdc3c7;
      }

      .btn-contact:hover {
        background-color: #bdc3c7;
      }

      .no-results {
        text-align: center;
        padding: 3rem;
        color: #7f8c8d;
      }

      .no-results i {
        font-size: 3rem;
        color: #bdc3c7;
        margin-bottom: 1rem;
      }

      /* Modal Styles */
      .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 1000;
        overflow-y: auto;
      }

      .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .modal-content {
        background: white;
        border-radius: 8px;
        max-width: 900px;
        width: 95%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
      }

      .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2rem;
        border-bottom: 1px solid #ecf0f1;
        position: sticky;
        top: 0;
        background: white;
        z-index: 1001;
      }

      .modal-header h2 {
        margin: 0;
        color: #2c3e50;
        font-size: 1.8rem;
      }

      .modal-close {
        background: none;
        border: none;
        font-size: 2rem;
        color: #7f8c8d;
        cursor: pointer;
        transition: color 0.3s;
      }

      .modal-close:hover {
        color: #2c3e50;
      }

      .modal-body {
        padding: 2rem;
      }

      .modal-image-carousel {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
      }

      .modal-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
        background: #f0f0f0;
      }

      .property-details-full {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
      }

      .details-section {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        border-left: 4px solid #0f5d32;
      }

      .details-section h3 {
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      .details-section i {
        color: #0f5d32;
        font-size: 1.2rem;
      }

      .details-list {
        list-style: none;
        padding: 0;
        margin: 0;
      }

      .details-list li {
        padding: 0.5rem 0;
        color: #555;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      .details-list li:before {
        content: "✓";
        color: #0f5d32;
        font-weight: bold;
        margin-right: 0.5rem;
      }

      .access-info {
        background: linear-gradient(135deg, #0f5d32 0%, #1f7f49 100%);
        color: white;
        padding: 2rem;
        border-radius: 8px;
        margin-bottom: 2rem;
      }

      .access-info h3 {
        margin-top: 0;
        font-size: 1.3rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
      }

      .access-info ul {
        list-style: none;
        padding: 0;
        margin: 1rem 0 0 0;
      }

      .access-info li {
        padding: 0.7rem 0;
        display: flex;
        align-items: center;
        gap: 0.7rem;
      }

      .access-info i {
        font-size: 1.2rem;
      }

      .price-section {
        background: #ecf0f1;
        padding: 2rem;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 2rem;
      }

      .price-label {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
      }

      .price-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #0f5d32;
      }

      .modal-actions {
        display: flex;
        gap: 1rem;
        padding-top: 2rem;
        border-top: 1px solid #ecf0f1;
      }

      .modal-btn {
        flex: 1;
        padding: 1rem;
        border: none;
        border-radius: 4px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1rem;
      }

      .modal-btn-primary {
        background-color: #0f5d32;
        color: white;
      }

      .modal-btn-primary:hover {
        background-color: #1f7f49;
      }

      .modal-btn-secondary {
        background-color: #3498db;
        color: white;
      }

      .modal-btn-secondary:hover {
        background-color: #2980b9;
      }

      @media (max-width: 768px) {
        .properties-header h1 {
          font-size: 1.8rem;
        }

        .filters-grid {
          grid-template-columns: 1fr;
        }

        .properties-grid {
          grid-template-columns: 1fr;
        }

        .modal-content {
          width: 98%;
          max-height: 95vh;
        }

        .modal-header {
          padding: 1.5rem;
        }

        .modal-header h2 {
          font-size: 1.4rem;
        }

        .modal-body {
          padding: 1.5rem;
        }

        .property-details-full {
          grid-template-columns: 1fr;
        }

        .modal-actions {
          flex-direction: column;
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
          <li>
            <a href="properties.php" class="nav-link active">Properties</a>
          </li>
          <li><a href="land-on-sale.php" class="nav-link">Buy</a></li>
          <li><a href="land-on-lease.php" class="nav-link">Lease</a></li>
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

    <!-- Properties Header -->
    <div class="properties-header">
      <div class="container">
        <h1>Available Properties</h1>
        <p>Discover premium land opportunities across Kenya</p>
      </div>
    </div>

    <!-- Main Container -->
    <div class="container">
      <!-- Filter Section -->
      <div class="filters-section">
        <div class="filters-grid">
          <div class="filter-group">
            <label for="location">Location</label>
            <select id="location">
              <option value="">All Locations</option>
              <option value="nairobi">Nairobi</option>
              <option value="karen">Karen</option>
              <option value="kiambu">Kiambu</option>
              <option value="nyeri">Nyeri</option>
              <option value="mombasa">Mombasa</option>
              <option value="nakuru">Nakuru</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="type">Property Type</label>
            <select id="type">
              <option value="">All Types</option>
              <option value="sale">For Sale</option>
              <option value="lease">For Lease</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="price-range">Price Range (KES)</label>
            <select id="price-range">
              <option value="">All Prices</option>
              <option value="500k-2m">500K - 2M</option>
              <option value="2m-5m">2M - 5M</option>
              <option value="5m-10m">5M - 10M</option>
              <option value="10m">Above 10M</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="size">Land Size (Acres)</label>
            <select id="size">
              <option value="">All Sizes</option>
              <option value="0.5-1">0.5 - 1</option>
              <option value="1-2">1 - 2</option>
              <option value="2-5">2 - 5</option>
              <option value="5">Above 5</option>
            </select>
          </div>

          <button class="filter-btn">
            <i class="fas fa-filter"></i> Filter
          </button>
        </div>
      </div>

      <!-- Properties Grid -->
      <div class="properties-grid">
        <!-- Property Card 1 -->
        <div class="property-card">
          <div style="position: relative">
            <img
              src="images/karen.jpg"
              alt="Karen Green Acres"
              class="property-image"
            />
            <span class="property-badge">For Sale</span>
          </div>
          <div class="property-content">
            <h3 class="property-title">Karen Green Acres</h3>
            <p class="property-location">
              <i class="fas fa-map-marker-alt"></i> Karen, Nairobi
            </p>
            <div class="property-details">
              <div class="detail-item">
                <i class="fas fa-ruler"></i>
                <div class="detail-value">2.5</div>
                <div class="detail-label">Acres</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-tree"></i>
                <div class="detail-value">Mixed</div>
                <div class="detail-label">Land Use</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-road"></i>
                <div class="detail-value">Good</div>
                <div class="detail-label">Access</div>
              </div>
            </div>
            <div class="property-price">8.5M <small>KES</small></div>
            <p class="property-description">
              Prime greenland property with excellent road access, suitable for
              residential or commercial development.
            </p>
            <div class="property-actions">
              <button class="btn-view">View Details</button>
              <button class="btn-contact">Contact</button>
            </div>
          </div>
        </div>

        <!-- Property Card 2 -->
        <div class="property-card">
          <div style="position: relative">
            <img
              src="images/kiambu.jpg"
              alt="Kiambu Valley Estate"
              class="property-image"
            />
            <span class="property-badge">For Sale</span>
          </div>
          <div class="property-content">
            <h3 class="property-title">Kiambu Valley Estate</h3>
            <p class="property-location">
              <i class="fas fa-map-marker-alt"></i> Kiambu County
            </p>
            <div class="property-details">
              <div class="detail-item">
                <i class="fas fa-ruler"></i>
                <div class="detail-value">1.8</div>
                <div class="detail-label">Acres</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-tree"></i>
                <div class="detail-value">Arable</div>
                <div class="detail-label">Land Use</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-water"></i>
                <div class="detail-value">Yes</div>
                <div class="detail-label">Water</div>
              </div>
            </div>
            <div class="property-price">6.2M <small>KES</small></div>
            <p class="property-description">
              Beautiful arable land with water access. Perfect for farming or
              agribusiness ventures. Very fertile soil.
            </p>
            <div class="property-actions">
              <button class="btn-view">View Details</button>
              <button class="btn-contact">Contact</button>
            </div>
          </div>
        </div>

        <!-- Property Card 3 -->
        <div class="property-card">
          <div style="position: relative">
            <img
              src="images/nyeri.jpg"
              alt="Nyeri Green Valley"
              class="property-image"
            />
            <span class="property-badge">For Lease</span>
          </div>
          <div class="property-content">
            <h3 class="property-title">Nyeri Green Valley</h3>
            <p class="property-location">
              <i class="fas fa-map-marker-alt"></i> Nyeri County
            </p>
            <div class="property-details">
              <div class="detail-item">
                <i class="fas fa-ruler"></i>
                <div class="detail-value">3.0</div>
                <div class="detail-label">Acres</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-tree"></i>
                <div class="detail-value">Tea Farm</div>
                <div class="detail-label">Land Use</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-water"></i>
                <div class="detail-value">Yes</div>
                <div class="detail-label">Water</div>
              </div>
            </div>
            <div class="property-price">450K <small>KES/month</small></div>
            <p class="property-description">
              Productive tea farm with established plants and working structures. Long-term lease available.
            </p>
            <div class="property-actions">
              <button class="btn-view">View Details</button>
              <button class="btn-contact">Contact</button>
            </div>
          </div>
        </div>

        <!-- Property Card 4 -->
        <div class="property-card">
          <div style="position: relative">
            <img
              src="images/nakuru.jpg"
              alt="Nakuru Prime Land"
              class="property-image"
            />
            <span class="property-badge">For Sale</span>
          </div>
          <div class="property-content">
            <h3 class="property-title">Nakuru Prime Land</h3>
            <p class="property-location">
              <i class="fas fa-map-marker-alt"></i> Nakuru County
            </p>
            <div class="property-details">
              <div class="detail-item">
                <i class="fas fa-ruler"></i>
                <div class="detail-value">4.5</div>
                <div class="detail-label">Acres</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-tree"></i>
                <div class="detail-value">Mixed</div>
                <div class="detail-label">Land Use</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-road"></i>
                <div class="detail-value">Tarmac</div>
                <div class="detail-label">Access</div>
              </div>
            </div>
            <div class="property-price">12.8M <small>KES</small></div>
            <p class="property-description">
              Large tract of prime greenland with tarmac road access. Ideal for commercial or large residential projects.
            </p>
            <div class="property-actions">
              <button class="btn-view">View Details</button>
              <button class="btn-contact">Contact</button>
            </div>
          </div>
        </div>

        <!-- Property Card 5 -->
        <div class="property-card">
          <div style="position: relative">
            <img
              src="images/mombasa.jpg"
              alt="Mombasa Coastal Land"
              class="property-image"
            />
            <span class="property-badge">For Sale</span>
          </div>
          <div class="property-content">
            <h3 class="property-title">Mombasa Coastal Land</h3>
            <p class="property-location">
              <i class="fas fa-map-marker-alt"></i> Mombasa County
            </p>
            <div class="property-details">
              <div class="detail-item">
                <i class="fas fa-ruler"></i>
                <div class="detail-value">2.0</div>
                <div class="detail-label">Acres</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-water"></i>
                <div class="detail-value">Beach</div>
                <div class="detail-label">Proximity</div>
              </div>
              <div class="detail-item">
                <i class="fas fa-road"></i>
                <div class="detail-value">Good</div>
                <div class="detail-label">Access</div>
              </div>
            </div>
            <div class="property-price">15.0M <small>KES</small></div>
            <p class="property-description">
              Premium coastal greenland perfect for resort development or private estate. Close to beach and amenities.
            </p>
            <div class="property-actions">
              <button class="btn-view">View Details</button>
              <button class="btn-contact">Contact</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Property Details Modal -->
    <div id="propertyModal" class="modal">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h2 id="modalTitle">Property Details</h2>
          </div>
          <button type="button" class="modal-close" onclick="closePropertyModal()" aria-label="Close">×</button>
        </div>

        <div class="modal-body">
          <!-- Price Section -->
          <div class="price-section">
            <div class="price-label">Price</div>
            <div class="price-value" id="modalPrice">---</div>
          </div>

          <!-- Images -->
          <div class="modal-image-carousel" id="imageCarousel"></div>

          <!-- Access Information -->
          <div class="access-info">
            <h3><i class="fas fa-map-marker-alt"></i> Access & Location Details</h3>
            <ul id="accessList"></ul>
          </div>

          <!-- Details Grid -->
          <div class="property-details-full" id="detailsGrid"></div>

          <!-- Description -->
          <div class="details-section">
            <h3><i class="fas fa-info-circle"></i> Description</h3>
            <p id="modalDescription" style="margin: 0; line-height: 1.6"></p>
          </div>

          <!-- Action Buttons -->
          <div class="modal-actions">
            <button
              class="modal-btn modal-btn-primary"
              onclick="contactSeller()"
            >
              <i class="fas fa-phone"></i> Contact Seller
            </button>
            <button
              class="modal-btn modal-btn-secondary"
              onclick="scheduleVisit()"
            >
              <i class="fas fa-calendar"></i> Schedule Visit
            </button>
          </div>
        </div>
      </div>
    </div>

    <footer>
      <div class="footer-container">
        <div class="footer-about">
          <h3>E-Land</h3>
          <p>Premium land management and trading platform in Kenya.</p>
        </div>
        <div class="footer-links">
          <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="land-on-sale.php">Buy</a></li>
            <li><a href="land-on-lease.php">Lease</a></li>
            <li><a href="about.php">About</a></li>
          </ul>
        </div>
        <div class="footer-contact">
          <p>Email: info@e-land.com</p>
          <p>Phone: +254 (0) 700 000 000</p>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2024 E-Land. All rights reserved.</p>
      </div>
    </footer>

    <script>
      // Comprehensive property data with access information
      const propertyData = {
        1: {
          id: 1,
          title: "Karen Green Acres",
          price: "8.5M",
          priceUnit: "KES",
          location: "Karen, Nairobi",
          acres: 2.5,
          landUse: "Mixed (Residential/Commercial)",
          access: "Good",
          description:
            "Prime greenland property with excellent road access, suitable for residential or commercial development. Surrounded by established residential communities with good security and infrastructure.",
          images: [
            "https://images.unsplash.com/photo-1500382017468-f049863256f0?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500346571288-f2fd57de6b94?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500595046891-e51df1bdc82f?w=500&h=300&fit=crop",
          ],
          accessDetails: [
            "Road Access: Paved tarmac road with direct access",
            "Distance to Nairobi CBD: 18 km",
            "Nearest Market: Karen Market - 2 km",
            "School Proximity: International School - 3 km",
            "Water Supply: Borehole drilling possible",
            "Electricity: Main grid connection available",
            "Public Transport: Regular matatu service",
          ],
          amenities: [
            "Gated Community Access",
            "24/7 Security",
            "Good Road Network",
            "Close to Shopping Centers",
          ],
          infrastructure: [
            "Tarmac Road Access",
            "Electricity Supply",
            "Water Pipeline",
            "Telephone Network",
          ],
          nearby: [
            "Karen Shopping Center",
            "Karen Hospital",
            "International Schools",
            "Police Station",
          ],
        },
        2: {
          id: 2,
          title: "Kiambu Valley Estate",
          price: "6.2M",
          priceUnit: "KES",
          location: "Kiambu County",
          acres: 1.8,
          landUse: "Arable",
          access: "Good",
          description:
            "Beautiful arable land with water access. Perfect for farming or agribusiness ventures. Very fertile soil with excellent climatic conditions.",
          images: [
            "https://images.unsplash.com/photo-1500595046891-e51df1bdc82f?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500346571288-f2fd57de6b94?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500382017468-f049863256f0?w=500&h=300&fit=crop",
          ],
          accessDetails: [
            "Road Access: Murram road to property",
            "Distance to Kiambu Town: 8 km",
            "Distance to Nairobi: 25 km",
            "Water: Permanent water source",
            "Climate: Cool, suitable for horticulture",
            "Labour: Local workforce available",
            "Market: Farmers market nearby",
          ],
          amenities: [
            "Water Source",
            "Fertile Soil",
            "Cool Climate",
            "Open Land",
          ],
          infrastructure: [
            "Water Tank",
            "Farm Buildings",
            "Access Road",
            "Farm Equipment",
          ],
          nearby: [
            "Kiambu Town",
            "Agricultural Extension Office",
            "Farmers Cooperative",
            "Local Market",
          ],
        },
        3: {
          id: 3,
          title: "Nyeri Green Valley",
          price: "450K",
          priceUnit: "KES/month",
          location: "Nyeri County",
          acres: 3.0,
          landUse: "Tea Farm",
          access: "Good",
          description:
            "Productive tea farm with established plants and working structures. Long-term lease available with option for management support.",
          images: [
            "https://images.unsplash.com/photo-1500346571288-f2fd57de6b94?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500595046891-e51df1bdc82f?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500382017468-f049863256f0?w=500&h=300&fit=crop",
          ],
          accessDetails: [
            "Road Access: Tarmac road to property boundary",
            "Distance to Nyeri Town: 12 km",
            "Altitude: 2,100m - Ideal for tea production",
            "Climate: Cool, wet climate perfect for tea",
            "Rainfall: 1,500-2,000mm annually",
            "Labour: Local workforce available",
            "Market Access: Tea factory buyer network established",
          ],
          amenities: [
            "Established Tea Plants",
            "Factory Buildings",
            "Water Harvesting System",
            "Storage Facilities",
          ],
          infrastructure: [
            "Tea Processing Facility",
            "Worker Housing",
            "Water Tank",
            "Farm Equipment",
          ],
          nearby: [
            "Nyeri Town",
            "Nanyuki",
            "Mweiga Tea Factory",
            "Agricultural Extension",
          ],
        },
        4: {
          id: 4,
          title: "Nakuru Prime Land",
          price: "12.8M",
          priceUnit: "KES",
          location: "Nakuru County",
          acres: 4.5,
          landUse: "Mixed",
          access: "Tarmac",
          description:
            "Large tract of prime greenland with tarmac road access. Ideal for commercial or large residential projects.",
          images: [
            "https://images.unsplash.com/photo-1500382017468-f049863256f0?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500595046891-e51df1bdc82f?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500346571288-f2fd57de6b94?w=500&h=300&fit=crop",
          ],
          accessDetails: [
            "Road Access: Main tarmac road frontage",
            "Distance to Nakuru Town: 8 km",
            "Highway Access: Nakuru-Nairobi highway - 3 km",
            "Altitude: 1,890m",
            "Airport Proximity: Nakuru Airfield - 15 km",
            "Industrial Zone: Nearby industrial area",
            "Public Utilities: All available",
          ],
          amenities: [
            "Tarmac Road Frontage",
            "Flat Terrain",
            "Large Plot Size",
            "Commercial Zoning Potential",
          ],
          infrastructure: [
            "Tarmac Road",
            "Electricity Supply",
            "Water Pipeline",
            "Drainage System",
          ],
          nearby: [
            "Nakuru Town",
            "Industrial Park",
            "Shopping Centers",
            "Medical Facilities",
          ],
        },
        5: {
          id: 5,
          title: "Mombasa Coastal Land",
          price: "15.0M",
          priceUnit: "KES",
          location: "Mombasa County",
          acres: 2.0,
          landUse: "Coastal",
          beach: "500m",
          description:
            "Premium coastal greenland perfect for resort development or private estate. Close to beach and local amenities.",
          images: [
            "https://images.unsplash.com/photo-1500595046891-e51df1bdc82f?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500346571288-f2fd57de6b94?w=500&h=300&fit=crop",
            "https://images.unsplash.com/photo-1500382017468-f049863256f0?w=500&h=300&fit=crop",
          ],
          accessDetails: [
            "Road Access: Main road access with beach connection",
            "Distance to Mombasa CBD: 12 km",
            "Beach Distance: 500m to pristine beach",
            "Airport: Moi International Airport - 15 km",
            "Climate: Tropical, warm year-round",
            "Tourism: High tourist area",
            "Market: Strong tourist and investment demand",
          ],
          amenities: [
            "Beach Proximity",
            "Tourist Area",
            "Good Infrastructure",
            "International Access",
          ],
          infrastructure: [
            "Main Road",
            "Electricity Supply",
            "Water Supply",
            "Waste Management",
          ],
          nearby: [
            "Beach",
            "Mombasa Town",
            "Hotels",
            "Restaurants",
          ],
        },
      };

      // Open property modal
      function openPropertyModal(propertyId) {
        const property = propertyData[propertyId];
        if (!property) return;

        // Set title and price
        document.getElementById("modalTitle").textContent = property.title;
        document.getElementById("modalPrice").textContent =
          property.price + " " + property.priceUnit;

        // Set images carousel
        const carousel = document.getElementById("imageCarousel");
        carousel.innerHTML = property.images
          .map(
            (img) =>
              `<img src="${img}" alt="${property.title}" class="modal-image">`,
          )
          .join("");

        // Set access details
        const accessList = document.getElementById("accessList");
        accessList.innerHTML = property.accessDetails
          .map(
            (detail) =>
              `<li><strong>${detail.split(":")[0]}:</strong> ${detail.split(":")[1]}</li>`,
          )
          .join("");

        // Set details grid
        const detailsGrid = document.getElementById("detailsGrid");
        let detailsHTML = `
          <div class="details-section">
            <h3><i class="fas fa-ruler"></i> Land Details</h3>
            <ul class="details-list">
              <li><strong>Size:</strong> ${property.acres} Acres</li>
              <li><strong>Land Use:</strong> ${property.landUse}</li>
              <li><strong>Location:</strong> ${property.location}</li>
            </ul>
          </div>
          <div class="details-section">
            <h3><i class="fas fa-wrench"></i> Infrastructure</h3>
            <ul class="details-list">
              ${property.infrastructure.map((item) => `<li>${item}</li>`).join("")}
            </ul>
          </div>
          <div class="details-section">
            <h3><i class="fas fa-shopping-bag"></i> Nearby Amenities</h3>
            <ul class="details-list">
              ${property.nearby.map((item) => `<li>${item}</li>`).join("")}
            </ul>
          </div>
        `;
        detailsGrid.innerHTML = detailsHTML;

        // Set description
        document.getElementById("modalDescription").textContent =
          property.description;

        // Show modal
        document.getElementById("propertyModal").classList.add("active");
        document.body.style.overflow = "hidden";
      }

      // Close modal
      function closePropertyModal() {
        document.getElementById("propertyModal").classList.remove("active");
        document.body.style.overflow = "auto";
      }

      // Contact seller
      function contactSeller() {
        alert(
          "Thank you for your interest!\n\nOur sales team will contact you shortly with more information.",
        );
      }

      // Schedule visit
      function scheduleVisit() {
        alert(
          "Visit scheduling feature coming soon!\n\nPlease contact our team directly to arrange a property viewing.",
        );
      }

      // Close modal when clicking outside of it
      document.addEventListener("click", function (event) {
        const modal = document.getElementById("propertyModal");
        if (event.target === modal) {
          closePropertyModal();
        }
      });

      // Filter functionality
      document
        .querySelector(".filter-btn")
        .addEventListener("click", function () {
          const location = document.getElementById("location").value;
          const type = document.getElementById("type").value;
          const priceRange = document.getElementById("price-range").value;
          const size = document.getElementById("size").value;

          console.log("Filtering by:", { location, type, priceRange, size });
          alert(
            "Filter applied! (In production, this would filter the properties)",
          );
        });

      // Contact button functionality
      document.querySelectorAll(".btn-contact").forEach((btn) => {
        btn.addEventListener("click", function () {
          alert("Please contact our sales team for more information!");
        });
      });

      // View details button functionality
      document.querySelectorAll(".btn-view").forEach((btn, index) => {
        btn.addEventListener("click", function (e) {
          e.preventDefault();
          const propertyId = index + 1;
          openPropertyModal(propertyId);
        });
      });

      // Smooth scroll effect
      document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute("href"));
          if (target) {
            target.scrollIntoView({ behavior: "smooth" });
          }
        });
      });
    </script>
  </body>
</html>
