<?php
define('APP_ROOT', '');
require_once __DIR__ . '/includes/auth.php';

// Already logged in? Go straight to dashboard.
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    $error = match ($_GET['error']) {
        'invalid'  => 'Invalid username or password.',
        'required' => 'Please enter both username and password.',
        default    => 'Login failed. Please try again.',
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login · Cafe POS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-wrapper">
  <div class="card login-card">
    <div class="card-body p-4">
      <div class="text-center mb-4">
        <i class="bi bi-cup-hot-fill" style="font-size:2.5rem;color:var(--coffee-dark);"></i>
        <h4 class="mt-2 mb-0">Cafe POS</h4>
        <div class="text-muted small">Staff Login</div>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="api/login.php">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-dark w-100">Login</button>
      </form>

      <div class="text-muted small mt-3 text-center">
        Default: admin / admin123 &nbsp;·&nbsp; staff / staff123<br>
        (run <code>setup.php</code> once first)
      </div>
    </div>
  </div>
</div>
</body>
</html>
