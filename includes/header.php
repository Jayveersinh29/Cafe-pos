<?php
// Expects $pageTitle and APP_ROOT to be defined by the including page.
$pageTitle = $pageTitle ?? 'Cafe POS';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?> · Cafe POS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<nav class="navbar navbar-dark app-navbar px-3">
  <div class="d-flex align-items-center">
    <button class="btn btn-sm btn-outline-light d-lg-none me-2" id="sidebarToggle" type="button">
      <i class="bi bi-list"></i>
    </button>
    <a class="navbar-brand fw-bold" href="<?= base_url('dashboard.php') ?>">
      <i class="bi bi-cup-hot-fill me-1"></i> Cafe POS
    </a>
  </div>
  <div class="d-flex align-items-center gap-3">
    <span class="text-white-50 small d-none d-sm-inline">
      <i class="bi bi-person-circle"></i>
      <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>
      <span class="badge bg-light text-dark ms-1"><?= htmlspecialchars(ucfirst($user['role'])) ?></span>
    </span>
    <a href="<?= base_url('logout.php') ?>" class="btn btn-sm btn-outline-light">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</nav>
<div class="app-shell">
