<?php
session_start();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>About Us - E-Land</title>
    <link rel="stylesheet" href="style.css" />
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
            <a href="land-on-lease.php" class="nav-link">Land on Lease</a>
          </li>
          <li><a href="about.php" class="nav-link active">About</a></li>
        </ul>
        <div class="auth-buttons">

<?php if(isset($_SESSION['user_id'])): ?>

    <span class="user-greeting" style="color:white; margin-right:1rem;">
        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
    </span>

    <a href="logout.php" class="btn-login">Logout</a>

<?php else: ?>

    <a href="login.html" class="btn-login">Login</a>
    <a href="register.php" class="btn-register">Register</a>

<?php endif; ?>

</div>
      </div>
    </nav>

    
    <header class="page-header">
      <h1>About E-Land</h1>
      <p>Your trusted platform for land management</p>
    </header>

    <main>
      <div class="page-content">
        <div class="container">
          <section style="margin-bottom: 3rem">
            <h2>Who We Are</h2>
            <p>
              E-Land is a digital platform dedicated to revolutionizing land
              management and transactions in Kenya. Our mission is to make land
              buying, selling, and leasing accessible to everyone by providing a
              transparent, secure, and efficient online marketplace.
            </p>
          </section>

          <section style="margin-bottom: 3rem">
            <h2>Our Mission</h2>
            <p>
              <strong>"Shamba ya Haki"</strong> - To empower landowners and
              buyers by providing a fair, transparent, and digital platform that
              simplifies land transactions and management in Kenya.
            </p>
          </section>

          <section style="margin-bottom: 3rem">
            <h2>Our Vision</h2>
            <p>
              To become the leading digital land marketplace in Africa, enabling
              millions to securely buy, sell, and lease land properties with
              confidence and ease.
            </p>
          </section>

          <section style="margin-bottom: 3rem">
            <h2>Why Choose E-Land?</h2>
            <div class="feature-grid">
              <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Secure</h3>
                <p>
                  All transactions are protected with industry-leading security
                  measures
                </p>
              </div>
              <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Digital First</h3>
                <p>Access our platform 24/7 from any device, anywhere</p>
              </div>
              <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3>Verified Properties</h3>
                <p>
                  All listings are verified to ensure authenticity and legality
                </p>
              </div>
              <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3>Support</h3>
                <p>Our dedicated support team is always ready to help you</p>
              </div>
            </div>
          </section>

          <section>
            <h2>Contact Information</h2>
            <div
              style="
                background-color: var(--light-color);
                padding: 2rem;
                border-radius: 8px;
              "
            >
              <p><strong>Email:</strong> aolukwo@gmail.com</p>
              <p><strong>Phone:</strong> +254 (0) 746151340</p>
              <p><strong>Address:</strong> Nairobi, Kenya</p>
              <p>
                <strong>Hours:</strong> Monday - Friday, 9:00 AM - 5:00 PM EAT
              </p>
            </div>
          </section>
        </div>
      </div>
    </main>

    <footer>
      <div class="footer-container">
        <div class="footer-about">
          <h3>E-Land</h3>
          <p>Connecting land seekers, owners, and communities through trusted digital property verification.</p>
        </div>

        <div class="footer-links">
          <h4>Quick Links</h4>
          <ul>
            <li><a href="about.php">About</a></li>
            <li><a href="my-land.php">My Land</a></li>
            <li><a href="land-on-sale.php">Land on Sale</a></li>
            <li><a href="land-on-lease.php">Land on Lease</a></li>
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
