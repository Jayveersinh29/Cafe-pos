<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_login();
$pageTitle = 'Customers';

$search = trim($_GET['q'] ?? '');
$sql = "
    SELECT c.id, c.name, c.mobile, c.notes, c.created_at,
           COUNT(b.id) AS total_orders, COALESCE(SUM(b.grand_total),0) AS total_spent
    FROM customers c
    LEFT JOIN bills b ON b.customer_id = c.id AND b.status = 'completed'
";
$params = [];
if ($search !== '') {
    $sql .= " WHERE c.name LIKE ? OR c.mobile LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$sql .= " GROUP BY c.id ORDER BY c.created_at DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">Customers</h4>
    <form class="d-flex gap-2" method="GET">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name / mobile" value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-sm btn-dark">Search</button>
    </form>
  </div>

  <div class="card stat-card p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th>Name</th><th>Mobile</th><th>Notes</th><th>Orders</th><th class="text-end">Total Spent</th><th>Since</th></tr>
        </thead>
        <tbody>
          <?php if (empty($customers)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No customers recorded yet. They're added automatically when you take an order with their details on the POS screen.</td></tr>
          <?php else: foreach ($customers as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c['name']) ?></td>
            <td><?= htmlspecialchars($c['mobile'] ?: '—') ?></td>
            <td class="small text-muted"><?= htmlspecialchars($c['notes'] ?: '—') ?></td>
            <td><?= (int)$c['total_orders'] ?></td>
            <td class="text-end">₹<?= number_format($c['total_spent'], 2) ?></td>
            <td class="small"><?= date('d M Y', strtotime($c['created_at'])) ?></td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
