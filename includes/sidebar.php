<?php
$current = basename($_SERVER['PHP_SELF']);
function nav_active($file, $current) {
    return $file === $current ? 'active' : '';
}
?>
<aside class="app-sidebar" id="appSidebar">
  <nav class="nav flex-column p-2">
    <a class="nav-link <?= nav_active('dashboard.php', $current) ?>" href="<?= base_url('dashboard.php') ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a class="nav-link <?= nav_active('pos.php', $current) ?>" href="<?= base_url('pages/pos.php') ?>">
      <i class="bi bi-shop"></i> POS
    </a>
    <a class="nav-link <?= nav_active('bills.php', $current) ?>" href="<?= base_url('pages/bills.php') ?>">
      <i class="bi bi-receipt"></i> Bills
    </a>
    <a class="nav-link <?= nav_active('customers.php', $current) ?>" href="<?= base_url('pages/customers.php') ?>">
      <i class="bi bi-people"></i> Customers
    </a>
    <?php if (is_admin()): ?>
    <a class="nav-link <?= nav_active('products.php', $current) ?>" href="<?= base_url('pages/products.php') ?>">
      <i class="bi bi-box-seam"></i> Products
    </a>
    <a class="nav-link <?= nav_active('reports.php', $current) ?>" href="<?= base_url('pages/reports.php') ?>">
      <i class="bi bi-bar-chart"></i> Reports
    </a>
    <a class="nav-link <?= nav_active('settings.php', $current) ?>" href="<?= base_url('pages/settings.php') ?>">
      <i class="bi bi-gear"></i> Settings
    </a>
    <?php endif; ?>
  </nav>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
