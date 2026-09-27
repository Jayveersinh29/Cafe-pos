<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_login();
require_admin();
$pageTitle = 'Settings';

$message = '';
$messageType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password'])) {
        $message = 'Current password is incorrect.';
        $messageType = 'danger';
    } elseif (strlen($new) < 6) {
        $message = 'New password must be at least 6 characters.';
        $messageType = 'danger';
    } elseif ($new !== $confirm) {
        $message = 'New password and confirmation do not match.';
        $messageType = 'danger';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->execute([$hash, $user['id']]);
        $message = 'Password updated successfully.';
        $messageType = 'success';
    }
}

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
  <h4 class="mb-3">Settings</h4>

  <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card stat-card p-3">
        <h6 class="mb-3">Change Password</h6>
        <form method="POST">
          <div class="mb-2">
            <label class="form-label small">Current Password</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label small">New Password</label>
            <input type="password" name="new_password" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label small">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>
          <button type="submit" name="change_password" value="1" class="btn btn-dark">Update Password</button>
        </form>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card stat-card p-3">
        <h6 class="mb-3">Receipt Details</h6>
        <p class="text-muted small mb-2">
          The café name, address, and phone number printed on receipts are set directly
          in <code>pages/print_bill.php</code> (look for the "Edit these to match your cafe" section
          near the top of the file). Update those three lines and every future receipt will use them.
        </p>
        <p class="text-muted small mb-0">
          Categories and products are managed from the <a href="products.php">Products</a> page.
        </p>
      </div>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
