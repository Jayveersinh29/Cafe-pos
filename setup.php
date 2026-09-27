<?php
/**
 * ONE-TIME SETUP SCRIPT
 * ----------------------------------------------------------------
 * Run this once in your browser, e.g.:
 *   http://localhost/cafe-pos/setup.php
 *
 * It sets working passwords (hashed with PHP's own password_hash())
 * for the two default accounts created by database/cafe_pos.sql:
 *
 *   admin / admin123   (role: admin)
 *   staff / staff123   (role: staff)
 *
 * DELETE THIS FILE after running it once — it is not meant to stay
 * on a live server.
 * ----------------------------------------------------------------
 */

require_once __DIR__ . '/config/database.php';

$defaults = [
    'admin' => 'admin123',
    'staff' => 'staff123',
];

$results = [];
foreach ($defaults as $username => $plainPassword) {
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->execute([$hash, $username]);
    $results[] = "$username -> password set to \"$plainPassword\" (rows updated: " . $stmt->rowCount() . ")";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Cafe POS - Setup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5" style="max-width:600px;">
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="card-title mb-3">✅ Setup complete</h4>
      <ul class="list-group mb-3">
        <?php foreach ($results as $r): ?>
          <li class="list-group-item"><?= htmlspecialchars($r) ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="text-danger fw-bold">Please delete setup.php now for security.</p>
      <a href="login.php" class="btn btn-primary">Go to Login</a>
    </div>
  </div>
</div>
</body>
</html>
