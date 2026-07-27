<?php
session_start();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terms and Conditions - E-Land</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <nav class="navbar">
      <div class="nav-container">
        <div class="logo">E-Land</div>
        <ul class="nav-menu">
          <li><a href="homepage.php">Home</a></li>
          <li><a href="my-land.php">My Land</a></li>
          <li><a href="land-on-sale.php">Land on Sale</a></li>
          <li><a href="land-on-lease.php">Land on Lease</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="terms.php">Terms</a></li>
        </ul>
        <div class="auth-buttons">
          <?php if(isset($_SESSION['user_id'])): ?>
          <span
            >Welcome, <?php echo htmlspecialchars($_SESSION['user_name']);
            ?>!</span
          >
          <a href="logout.php">Logout</a>
          <?php else: ?>
          <a href="login.html">Login</a>
          <a href="register.php">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </nav>
    <header class="page-header">
      <h1>Terms and Conditions</h1>
      <p>Please read these terms carefully before using E-Land.</p>
    </header>
    <main>
      <div class="container">
        <h2> Acceptance of Terms</h2>
        <p>By using E-Land you agree to these Terms and Conditions.</p>
        <h2>1. Purpose</h2>
        <p>
          E-Land provides digital land verification, land sale, lease and
          management services.
        </p>
        <h2>2. Eligibility</h2>
        <ul>
          <li>Be 18 years or older.</li>
          <li>Provide accurate information.</li>
          <li>Submit valid identification.</li>
        </ul>
        <h2>3. Registration</h2>
        <p>
          Users must keep their account information accurate and protect their
          passwords.
        </p>
        <h2>4. Land Verification</h2>
        <p>
          Verification is based on submitted information and available records.
        </p>
        <h2>5. Property Listings</h2>
        <p>
          Only genuine property owners or authorized representatives may create
          listings.
        </p>
        <h2>6. Purchases and Leases</h2>
        <p>Requests do not create a binding agreement until accepted.</p>
        <h2>7. Prohibited Activities</h2>
        <ul>
          <li>Fraud.</li>
          <li>False documents.</li>
          <li>Unauthorized access.</li>
          <li>Illegal activity.</li>
        </ul>
        <h2>8. Privacy</h2>
        <p>
          Your information is used only to provide platform services and comply
          with applicable law.
        </p>
        <h2>9. Intellectual Property</h2>
        <p>All platform content belongs to E-Land.</p>
        <h2>10. Limitation of Liability</h2>
        <p>
          E-Land is not liable for losses arising from inaccurate user
          information or disputes.
        </p>
        <h2>11. Account Suspension</h2>
        <p>Accounts may be suspended for violating these terms.</p>
        <h2>12. Governing Law</h2>
        <p>These terms are governed by the laws of Kenya.</p>
        <h2>13. Contact</h2>
        <p>
          Email: support@eland.co.ke<br />Phone: +254 746 151 340<br />Nairobi,
          Kenya
        </p>
      </div>
    </main>
    <footer>
      <div class="container">
        <p>&copy; 2026 E-Land. All rights reserved.</p>
      </div>
    </footer>
  </body>
</html>
