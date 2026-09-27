<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_admin();
$pageTitle = 'Reports';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="mb-0">Sales Reports</h4>
    <select id="rangeSelect" class="form-select form-select-sm" style="width:auto;">
      <option value="today">Today</option>
      <option value="week">Last 7 Days</option>
      <option value="month">Last 30 Days</option>
    </select>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-4">
      <div class="card stat-card p-3">
        <div class="text-muted small">Total Sales</div>
        <div class="stat-value" id="reportTotalSales">₹0.00</div>
      </div>
    </div>
    <div class="col-6 col-lg-4">
      <div class="card stat-card p-3">
        <div class="text-muted small">Total Bills</div>
        <div class="stat-value" id="reportTotalBills">0</div>
      </div>
    </div>
    <div class="col-6 col-lg-4">
      <div class="card stat-card p-3">
        <div class="text-muted small">Average Bill</div>
        <div class="stat-value" id="reportAvgBill">₹0.00</div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card stat-card p-3">
        <h6 class="mb-3">Sales Trend</h6>
        <canvas id="salesChart" height="140"></canvas>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card stat-card p-3">
        <h6 class="mb-3">Top Products</h6>
        <div class="table-responsive">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
            <tbody id="topProductsBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>

<?php
$pageScripts = [
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js',
    '../assets/js/reports.js',
];
require __DIR__ . '/../includes/footer.php';
?>
