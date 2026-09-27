<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
require_admin();

$range = $_GET['range'] ?? 'today';
$days = match ($range) {
    'week'  => 7,
    'month' => 30,
    default => 1, // today
};

$startDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' day'));

$totalsStmt = $pdo->prepare("
    SELECT COALESCE(SUM(grand_total),0) AS total_sales, COUNT(*) AS total_bills
    FROM bills
    WHERE status = 'completed' AND DATE(created_at) >= ?
");
$totalsStmt->execute([$startDate]);
$totals = $totalsStmt->fetch();

$topStmt = $pdo->prepare("
    SELECT bi.product_name, SUM(bi.quantity) AS total_qty, SUM(bi.total) AS total_amount
    FROM bill_items bi
    JOIN bills b ON b.id = bi.bill_id
    WHERE b.status = 'completed' AND DATE(b.created_at) >= ?
    GROUP BY bi.product_name
    ORDER BY total_qty DESC
    LIMIT 10
");
$topStmt->execute([$startDate]);
$topProducts = $topStmt->fetchAll();

$dailyStmt = $pdo->prepare("
    SELECT DATE(created_at) AS day, COALESCE(SUM(grand_total),0) AS total
    FROM bills
    WHERE status = 'completed' AND DATE(created_at) >= ?
    GROUP BY DATE(created_at)
");
$dailyStmt->execute([$startDate]);
$dailyRaw = $dailyStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$daily = [];
for ($i = $days - 1; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $daily[] = ['day' => date('d M', strtotime($d)), 'total' => $dailyRaw[$d] ?? 0];
}

echo json_encode([
    'success'      => true,
    'total_sales'  => (float)$totals['total_sales'],
    'total_bills'  => (int)$totals['total_bills'],
    'top_products' => $topProducts,
    'daily_sales'  => $daily,
]);
