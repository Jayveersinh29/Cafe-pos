<?php
define('APP_ROOT', '');
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_login();

$pageTitle = 'Dashboard';

// ---- Stats ----
$todaySales = $pdo->query("SELECT COALESCE(SUM(grand_total),0) AS total FROM bills WHERE DATE(created_at) = CURDATE() AND status='completed'")->fetch()['total'];
$todayBills = $pdo->query("SELECT COUNT(*) AS cnt FROM bills WHERE DATE(created_at) = CURDATE() AND status='completed'")->fetch()['cnt'];
$totalProducts = $pdo->query("SELECT COUNT(*) AS cnt FROM products WHERE is_active = 1")->fetch()['cnt'];
$totalCustomers = $pdo->query("SELECT COUNT(*) AS cnt FROM customers")->fetch()['cnt'];

$recentBills = $pdo->query("
    SELECT b.bill_no, b.grand_total, b.payment_method, b.created_at,
           COALESCE(c.name, 'Walk-in') AS customer_name
    FROM bills b
    LEFT JOIN customers c ON c.id = b.customer_id
    WHERE b.status = 'completed'
    ORDER BY b.created_at DESC
    LIMIT 8
")->fetchAll();

// Last 7 days sales for the chart
$last7 = $pdo->query("
    SELECT DATE(created_at) AS day, COALESCE(SUM(grand_total),0) AS total
    FROM bills
    WHERE status = 'completed' AND created_at >= (CURDATE() - INTERVAL 6 DAY)
    GROUP BY DATE(created_at)
")->fetchAll(PDO::FETCH_KEY_PAIR);

$chartLabels = [];
$chartValues = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $chartLabels[] = date('d M', strtotime($d));
    $chartValues[] = isset($last7[$d]) ? (float)$last7[$d] : 0;
}

require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/sidebar.php';
?>
<main class="app-main">
  <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="mb-0">Dashboard</h4>
    <a href="pages/pos.php" class="btn btn-accent btn-dark"><i class="bi bi-shop"></i> Quick POS</a>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="card stat-card p-3">
        <div class="text-muted small">Today's Sales</div>
        <div class="stat-value"><?= formatMoney($todaySales) ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card stat-card p-3">
        <div class="text-muted small">Today's Bills</div>
        <div class="stat-value"><?= (int)$todayBills ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card stat-card p-3">
        <div class="text-muted small">Products</div>
        <div class="stat-value"><?= (int)$totalProducts ?></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="card stat-card p-3">
        <div class="text-muted small">Customers</div>
        <div class="stat-value"><?= (int)$totalCustomers ?></div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card stat-card p-3">
        <h6 class="mb-3">Sales - Last 7 Days</h6>
        <canvas id="dashboardChart" height="140"></canvas>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card stat-card p-3">
        <h6 class="mb-3">Recent Bills</h6>
        <?php if (empty($recentBills)): ?>
          <div class="text-muted small">No bills yet.</div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Bill No</th><th>Customer</th><th class="text-end">Total</th></tr></thead>
            <tbody>
              <?php foreach ($recentBills as $b): ?>
              <tr>
                <td class="small"><?= htmlspecialchars($b['bill_no']) ?></td>
                <td class="small"><?= htmlspecialchars($b['customer_name']) ?></td>
                <td class="text-end small"><?= formatMoney($b['grand_total']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>

<?php
function formatMoney($amount) {
    return '₹' . number_format((float)$amount, 2);
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('dashboardChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($chartLabels) ?>,
    datasets: [{
      label: 'Sales',
      data: <?= json_encode($chartValues) ?>,
      borderColor: '#d98324',
      backgroundColor: 'rgba(217,131,36,0.15)',
      tension: 0.3,
      fill: true
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
