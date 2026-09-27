<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Bills';

$search = trim($_GET['q'] ?? '');
$sql = "
    SELECT b.id, b.bill_no, b.grand_total, b.payment_method, b.status, b.created_at,
           COALESCE(c.name,'Walk-in') AS customer_name, u.username AS staff_username
    FROM bills b
    LEFT JOIN customers c ON c.id = b.customer_id
    JOIN users u ON u.id = b.user_id
";
$params = [];
if ($search !== '') {
    $sql .= " WHERE b.bill_no LIKE ? OR c.name LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$sql .= " ORDER BY b.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bills = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">Bills</h4>
    <form class="d-flex gap-2" method="GET">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search bill no / customer" value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-sm btn-dark">Search</button>
    </form>
  </div>

  <div class="card stat-card p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Bill No</th><th>Customer</th><th>Staff</th><th>Payment</th>
            <th>Status</th><th class="text-end">Total</th><th>Date/Time</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($bills)): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No bills found.</td></tr>
          <?php else: foreach ($bills as $b): ?>
          <tr>
            <td><?= htmlspecialchars($b['bill_no']) ?></td>
            <td><?= htmlspecialchars($b['customer_name']) ?></td>
            <td><?= htmlspecialchars($b['staff_username']) ?></td>
            <td><?= htmlspecialchars($b['payment_method']) ?></td>
            <td>
              <span class="badge <?= $b['status'] === 'completed' ? 'bg-success' : 'bg-warning text-dark' ?>">
                <?= htmlspecialchars(ucfirst($b['status'])) ?>
              </span>
            </td>
            <td class="text-end">₹<?= number_format($b['grand_total'], 2) ?></td>
            <td class="small"><?= date('d M Y, h:i A', strtotime($b['created_at'])) ?></td>
            <td>
              <a href="print_bill.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-dark" target="_blank">
                <i class="bi bi-printer"></i> View
              </a>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
