<?php
// Shared admin header with modern styling
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Admin - E_Land</title>
  <style>
    body {
      background: #f6f2e8;
      color: #1f2a1f;
      font-family: "Segoe UI", Arial, sans-serif;
    }
    .admin-shell {
      min-height: 100vh;
      background: linear-gradient(135deg, #f8f4e8 0%, #f1ebde 100%);
    }
    .topbar {
      background: linear-gradient(135deg, #0f6b3c 0%, #1a8d4d 100%);
      color: white;
      box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12);
    }
    .topbar .navbar-brand, .topbar .nav-link {
      color: white !important;
    }
    .topbar .nav-link:hover {
      opacity: 0.9;
    }
    .card {
      border: 1px solid #e6e0d2;
      box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
    }
    .btn-primary {
      background: linear-gradient(135deg, #0f6b3c, #1f8a52);
      border: none;
    }
    .btn-warning {
      background: #d4a017;
      border: none;
    }
    .table thead {
      background: #0f6b3c;
      color: white;
    }
  </style>
</head>
<body>
<div class="admin-shell">
<nav class="navbar navbar-expand-lg topbar mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="dashboard.php">🏞️ E-Land Admin</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="project_activities.php">Project Activities</a></li>
        <li class="nav-item"><a class="nav-link" href="view_land.php">Land Records</a></li>
        <li class="nav-item"><a class="nav-link" href="view_land_cases.php">Land Cases</a></li>
        <li class="nav-item"><a class="nav-link" href="verification_logs.php">Verification Logs</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container py-2">
