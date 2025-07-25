<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ModernTech HR | Admin</title>

  <!--This is the Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      font-family: 'Inter', sans-serif;
    }

    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #0f2027;
      padding-top: 1rem;
      overflow-y: auto;
      z-index: 1030;
      overflow: hidden;
    }

    .main-content {
      margin-left: 250px;
      padding: 20px;
      background-color: #fbfbfb;
      min-height: 100vh;
    }

    @media (max-width: 768px) {
      .sidebar {
        position: relative;
        width: 100%;
        height: auto;
      }

      .main-content {
        margin-left: 0;
      }
    }

    .sidebar .nav-link {
      color: #e0f7fa;
    }

    .sidebar .nav-link.active {
      background-color: #0c4190;
      font-weight: bold;
    }

    .sidebar .nav-link:hover {
      background-color: #0d6efd;
      color: #fff;
    }

    .attendance-present {
      color: green;
    }

    .attendance-absent {
      color: red;
    }

    strong {
      font-weight: 700;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <nav class="sidebar">
    <div class="text-center mb-4">
      <img src="images/logo-2.png" alt="Logo" class="img-fluid rounded" style="max-width: 100px;">
    </div>
    <hr class="my-3 text-white">

    <ul class="nav flex-column px-3">
      <li class="nav-item">
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
          <i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'active' : '' ?>" href="employees.php">
          <i class="bi bi-people me-2"></i>Employees</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : '' ?>" href="attendance.php">
          <i class="bi bi-calendar-check me-2"></i>Attendance</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'leave.php' ? 'active' : '' ?>" href="leave.php">
          <i class="bi bi-calendar-event me-2"></i>Leave Requests</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'payroll.php' ? 'active' : '' ?>" href="payroll.php">
          <i class="bi bi-cash-coin me-2"></i>Payroll</a>
      </li>

      <hr class="my-3 text-white">

      <li class="nav-item">
        <a class="nav-link" href="logout.php">
          <i class="bi bi-box-arrow-right me-2"></i>Logout</a>
      </li>
    </ul>

    <hr class="my-3 text-white mx-3">
    <div class="admin p-2 text-white border border-info mx-3 mb-3 rounded mt-3">
      <i class="bi bi-person-circle me-2"></i>Logged in as: <strong class="text-info">Admin</strong>
    </div>
  </nav>

  <!-- Main content from the dasboard gose here -->
  <div class="main-content">
