<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid bill id.']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.*, COALESCE(c.name,'Walk-in') AS customer_name, c.mobile AS customer_mobile,
           u.full_name AS staff_name, u.username AS staff_username
    FROM bills b
    LEFT JOIN customers c ON c.id = b.customer_id
    JOIN users u ON u.id = b.user_id
    WHERE b.id = ?
");
$stmt->execute([$id]);
$bill = $stmt->fetch();

if (!$bill) {
    echo json_encode(['success' => false, 'message' => 'Bill not found.']);
    exit;
}

$itemsStmt = $pdo->prepare("SELECT product_name, quantity, price, total FROM bill_items WHERE bill_id = ?");
$itemsStmt->execute([$id]);
$items = $itemsStmt->fetchAll();

echo json_encode(['success' => true, 'bill' => $bill, 'items' => $items]);
