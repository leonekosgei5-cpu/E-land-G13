<?php
session_start();
require_once 'mongodb.php';

$featuredProperties = [];
$featuredError = '';

try {
    $featuredCursor = mongo_find('land_records', [], ['limit' => 6]);
    $featuredProperties = $featuredCursor->toArray();
} catch (MongoDB\Driver\Exception\MongoDBException $e) {
    $featuredError = $e->getMessage();
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>E-Land - Land Management System</title>
    <link rel="stylesheet" href="style.css" />
    <link
      rel="stylesheet"
      href="home.css"
    />
  </head>
  <body>
    <nav class="navbar">
      <div class="nav-container">
        <div class="logo"><a href="homepage.php">E-Land</a></div>
        <ul class="nav-menu">
          <li><a href="homepage.php" class="nav-link active">Home</a></li>
          <li><a href="my-land.php" class="nav-link">My Land</a></li>
          <li><a href="land-on-sale.php" class="nav-link">Land on Sale</a></li>
          <li>
            <a href="land-on-lease.php" class="nav-link">Land on Lease</a>
          </li>
          <li><a href="about.php" class="nav-link">About</a></li>
        </ul>
        <div class="auth-buttons">
          <?php if(isset($_SESSION['user_id'])): ?>

          <span class="user-greeting" style="color: white; margin-right: 1rem">
            Welcome, <?php echo $_SESSION['user_name']; ?>!
          </span>

          <a href="logout.php" class="btn-login">Logout</a>

          <?php else: ?>

          <a href="login.html" class="btn-login">Login</a>
          <a href="register.php" class="btn-register">Register</a>

          <?php endif; ?>
        </div>
      </div>
    </nav>

    <header class="hero">
      <div class="hero-content">
        <h1>Welcome to E-Land</h1>
        <p class="tagline">
          Shamba la Haki - Your Digital Land Management Platform
        </p>
        <p class="subtitle">
          Find, buy, lease, or manage land properties with ease
        </p>
      </div>
    </header>

    <!-- Featured Properties Section -->
    <section class="featured-properties">
      <div class="container">
        <h2>Featured Properties</h2>
        <p class="section-subtitle">Discover premium land opportunities across Kenya</p>
        <div class="featured-grid">
          <?php if ($featuredError): ?>
            <div class="alert alert-danger">Failed to load featured properties: <?php echo htmlspecialchars($featuredError); ?></div>
          <?php elseif (empty($featuredProperties)): ?>
            <div class="alert alert-info">No featured properties available yet.</div>
          <?php else: ?>
            <?php foreach ($featuredProperties as $property): ?>
              <?php
                $status = isset($property->status) ? $property->status : 'For Sale';
                $badgeClass = strtolower(str_replace(' ', '-', $status));
                $image = isset($property->image_url) ? $property->image_url : 'images/default-property.jpg';
                $location = isset($property->county) ? htmlspecialchars($property->county) : 'Kenya';
                $size = isset($property->land_size) ? htmlspecialchars($property->land_size) : 'N/A';
                $price = isset($property->price) ? htmlspecialchars($property->price) : 'Price on request';
                $mainTitle = isset($property->title_number) ? htmlspecialchars($property->title_number) : 'Property';
                $detailsLink = 'properties.php';
              ?>
              <div class="featured-card">
                <div class="featured-image">
                  <img src="<?php echo $image; ?>" alt="<?php echo $mainTitle; ?>">
                  <span class="property-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                  <div class="property-overlay">
                    <button class="overlay-btn" onclick="window.location.href='<?php echo $detailsLink; ?>'"><i class="fas fa-search-plus"></i> View Details</button>
                  </div>
                </div>
                <div class="featured-info">
                  <h3><?php echo $mainTitle; ?></h3>
                  <p class="property-location"><i class="fas fa-map-marker-alt"></i> <?php echo $location; ?></p>
                  <div class="property-meta">
                    <span><i class="fas fa-ruler"></i> <?php echo $size; ?></span>
                    <span><i class="fas fa-road"></i> <?php echo isset($property->land_category) ? htmlspecialchars($property->land_category) : 'Land'; ?></span>
                  </div>
                  <p class="property-price"><?php echo $price; ?></p>
                  <a href="<?php echo $detailsLink; ?>" class="btn-small">Learn More</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="view-all-btn">
          <a href="properties.php" class="btn-primary">View All Properties</a>
        </div>
      </div>
    </section>

    <main>
      <section class="features">
        <div class="container">
          <h2>Our Services</h2>
          <div class="feature-grid">
            <div class="feature-card">
              <div class="feature-icon">🏡</div>
              <h3>My Land</h3>
              <p>Manage and view your own land properties</p>
              <a href="my-land.php" class="btn-primary">View My Land</a>
            </div>

            <div class="feature-card">
              <div class="feature-icon">💰</div>
              <h3>Land on Sale</h3>
              <p>Browse and purchase available land properties</p>
              <a href="land-on-sale.php" class="btn-primary">Browse Sales</a>
            </div>

            <div class="feature-card">
              <div class="feature-icon">🔑</div>
              <h3>Land on Lease</h3>
              <p>Find and lease land for your business or farming</p>
              <a href="land-on-lease.php" class="btn-primary">Browse Leases</a>
            </div>
          </div>
        </div>
      </section>

      <section class="cta">
        <div class="container">
          <h2>Ready to Get Started?</h2>
          <p>Join our platform to explore land opportunities</p>
          <div class="cta-buttons">

<?php if(isset($_SESSION['user_id'])): ?>

    <a href="my-land.php" class="btn-primary">Go to My Dashboard</a>

<?php else: ?>

    <a href="register.php" class="btn-primary">Create Account</a>
    <a href="login.php" class="btn-secondary">Sign In</a>

<?php endif; ?>

</div>
        </div>
      </section>
    </main>

    <footer>
      <div class="footer-container">
        <div class="footer-about">
          <h3>E-Land</h3>
          <p>Modern land verification and property discovery made simple for buyers, sellers, and tenants across Kenya.</p>
        </div>

        <div class="footer-links">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="about.php">About Us</a></li>
            <li><a href="my-land.php">My Land</a></li>
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
            <a href="https://wa.me/254746151340" target="_blank" rel="noopener noreferrer">WhatsApp</a>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>© 2026 E-Land. All rights reserved.</p>
      </div>
    </footer>
  </body>
</html>
