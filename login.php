<?php
// Login page
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'Admin' && $password === 'admin123') {
        $_SESSION['username'] = $username;
        $_SESSION['loggedin'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}

if (isset($_SESSION['loggedin'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ModernTech HR | Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body {
      background: url('images/bg-2.png') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Segoe UI', sans-serif;
    }

    .login-card {
      backdrop-filter: blur(15px);
      background-color: rgba(255, 255, 255, 0.15);
      border-radius: 16px;
      box-shadow: 0 0 32px rgba(0, 0, 0, 0.25);
      padding: 2rem;
      color: #fff;
    }

    .login-card img {
      width: 80px;
      margin: 0 auto 1rem;
      display: block;
      border-radius: 50%;
      box-shadow: 0 0 32px rgba(0, 0, 0, 0.8);
    }

    .login-card h2 {
      text-align: center;
      color: #fff;
      font-weight: 600;
      text-shadow: 0 1px 4px rgba(0, 0, 0, 0.8);
    }

    .login-card p.subtext {
      text-align: center;
      margin-top: -0.5rem;
      color: #e0e0e0;
    }

    .form-control {
      border-radius: 8px;
    }

    .btn-login {
      background: #4f46e5;
      color: white;
      font-weight: 500;
      border-radius: 8px;
    }

    .btn-login:hover {
      background: #4338ca;
    }

    .credentials {
      text-align: center;
      font-size: 0.9rem;
      color: #ddd;
      margin-top: 1rem;
    }

    .alert {
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="d-flex vh-100 align-items-center justify-content-center">
    <div class="login-card col-md-4 col-sm-10">
      <img src="images/logo-2.png" alt="Logo" />
      <h2>ModernTech HR</h2>
      <p class="subtext">Login to your account</p>
      <p class="text-center text-secondary small">Admin</p>
      
      <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label for="username" class="form-label text-white">Username</label>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label text-white">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
        </div>
        <button type="submit" class="btn btn-login w-100 mt-3">Login</button>
      </form>

      <p class="credentials">Credentials: <strong>Admin</strong> / <strong>admin123</strong></p>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

