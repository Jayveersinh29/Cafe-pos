<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // List every currently-held bill (any staff member can pick one up at the register).
    $stmt = $pdo->query("
        SELECT b.id, b.bill_no, b.grand_total, b.created_at,
               COALESCE(c.name, 'Walk-in') AS customer_name,
               COALESCE(SUM(bi.quantity), 0) AS item_count
        FROM bills b
        LEFT JOIN customers c ON c.id = b.customer_id
        LEFT JOIN bill_items bi ON bi.bill_id = b.id
        WHERE b.status = 'held'
        GROUP BY b.id
        ORDER BY b.created_at DESC
    ");
    $held = $stmt->fetchAll();
    echo json_encode(['success' => true, 'held_bills' => $held]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Resume: fetch the full bill + items, then remove the parked record
    // (bill_items are removed automatically via ON DELETE CASCADE).
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($data['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid bill id.']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT b.*, COALESCE(c.name,'Walk-in') AS customer_name, c.mobile AS customer_mobile, c.notes AS customer_notes
        FROM bills b
        LEFT JOIN customers c ON c.id = b.customer_id
        WHERE b.id = ? AND b.status = 'held'
    ");
    $stmt->execute([$id]);
    $bill = $stmt->fetch();

    if (!$bill) {
        echo json_encode(['success' => false, 'message' => 'That held bill was not found (it may already have been resumed).']);
        exit;
    }

    $itemsStmt = $pdo->prepare("SELECT product_id, product_name, quantity, price, total FROM bill_items WHERE bill_id = ?");
    $itemsStmt->execute([$id]);
    $items = $itemsStmt->fetchAll();

    try {
        $pdo->beginTransaction();
        $del = $pdo->prepare("DELETE FROM bills WHERE id = ? AND status = 'held'");
        $del->execute([$id]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Could not resume this bill: ' . $e->getMessage()]);
        exit;
    }

    echo json_encode(['success' => true, 'bill' => $bill, 'items' => $items]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported method']);
