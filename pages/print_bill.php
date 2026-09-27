<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT b.*, COALESCE(c.name,'Walk-in') AS customer_name, c.mobile AS customer_mobile
    FROM bills b
    LEFT JOIN customers c ON c.id = b.customer_id
    WHERE b.id = ?
");
$stmt->execute([$id]);
$bill = $stmt->fetch();

if (!$bill) {
    die('Bill not found.');
}

$itemsStmt = $pdo->prepare("SELECT product_name, quantity, price, total FROM bill_items WHERE bill_id = ?");
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

// ---- Edit these to match your cafe ----
$cafeName    = 'The Corner Cafe';
$cafeAddress = '123 Market Street, Your City';
$cafePhone   = '+91 90000 00000';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Bill <?= htmlspecialchars($bill['bill_no']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between mb-3 no-print" style="max-width:340px;margin:0 auto;">
    <a href="bills.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    <button onclick="window.print()" class="btn btn-sm btn-dark"><i class="bi bi-printer"></i> Print</button>
  </div>

  <div id="printArea">
    <div class="receipt">
      <div class="text-center">
        <strong style="font-size:1.1rem;"><?= htmlspecialchars($cafeName) ?></strong><br>
        <?= htmlspecialchars($cafeAddress) ?><br>
        <?= htmlspecialchars($cafePhone) ?>
      </div>
      <hr>
      Bill No: <?= htmlspecialchars($bill['bill_no']) ?><br>
      Date: <?= date('d/m/Y', strtotime($bill['created_at'])) ?><br>
      Time: <?= date('h:i A', strtotime($bill['created_at'])) ?><br>
      Customer: <?= htmlspecialchars($bill['customer_name']) ?>
      <?= $bill['customer_mobile'] ? ' (' . htmlspecialchars($bill['customer_mobile']) . ')' : '' ?>
      <hr>
      <table style="width:100%;">
        <thead>
          <tr>
            <td><strong>Item</strong></td>
            <td class="text-center"><strong>Qty</strong></td>
            <td class="text-end"><strong>Price</strong></td>
            <td class="text-end"><strong>Total</strong></td>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item): ?>
          <tr>
            <td><?= htmlspecialchars($item['product_name']) ?></td>
            <td class="text-center"><?= (int)$item['quantity'] ?></td>
            <td class="text-end"><?= number_format($item['price'], 2) ?></td>
            <td class="text-end"><?= number_format($item['total'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <hr>
      <div class="d-flex justify-content-between"><span>Subtotal:</span><span>₹<?= number_format($bill['subtotal'], 2) ?></span></div>
      <div class="d-flex justify-content-between"><span>Discount:</span><span>₹<?= number_format($bill['discount'], 2) ?></span></div>
      <?php if ($bill['tax'] > 0): ?>
      <div class="d-flex justify-content-between"><span>Tax:</span><span>₹<?= number_format($bill['tax'], 2) ?></span></div>
      <?php endif; ?>
      <hr>
      <div class="d-flex justify-content-between fw-bold" style="font-size:1.1rem;">
        <span>TOTAL:</span><span>₹<?= number_format($bill['grand_total'], 2) ?></span>
      </div>
      <hr>
      Payment: <?= htmlspecialchars($bill['payment_method']) ?><br>
      <?php if ($bill['payment_method'] === 'Cash' && $bill['amount_received'] !== null): ?>
        Received: ₹<?= number_format($bill['amount_received'], 2) ?><br>
        Change: ₹<?= number_format($bill['change_amount'], 2) ?><br>
      <?php endif; ?>
      <div class="text-center mt-2">
        Thank You!<br>Visit Again :)
      </div>
    </div>
  </div>
</div>
<style>
  @media print {
    .no-print { display: none !important; }
  }
</style>
</body>
</html>
