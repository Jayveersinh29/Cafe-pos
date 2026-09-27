<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
require_login();

$data = json_decode(file_get_contents('php://input'), true) ?? [];

$items = $data['items'] ?? [];
if (!is_array($items) || count($items) === 0) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty.']);
    exit;
}

$customerName   = trim($data['customer']['name'] ?? 'Walk-in') ?: 'Walk-in';
$customerMobile = trim($data['customer']['mobile'] ?? '');
$customerNotes  = trim($data['customer']['notes'] ?? '');

$subtotal   = (float)($data['subtotal'] ?? 0);
$discount   = (float)($data['discount'] ?? 0);
$tax        = (float)($data['tax'] ?? 0);
$grandTotal = (float)($data['grand_total'] ?? 0);
$paymentMethod = in_array($data['payment_method'] ?? '', ['Cash', 'UPI', 'Card', 'Other'], true)
    ? $data['payment_method'] : 'Cash';
$amountReceived = $data['amount_received'] !== null ? (float)$data['amount_received'] : null;
$changeAmount = ($paymentMethod === 'Cash' && $amountReceived !== null) ? max(0, $amountReceived - $grandTotal) : null;
$status = in_array($data['status'] ?? 'completed', ['completed', 'held'], true) ? $data['status'] : 'completed';

$userId = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Save/find customer (only create a row if any details were actually given)
    $customerId = null;
    if ($customerMobile !== '' || $customerName !== 'Walk-in') {
        $stmt = $pdo->prepare("INSERT INTO customers (name, mobile, notes) VALUES (?, ?, ?)");
        $stmt->execute([$customerName, $customerMobile ?: null, $customerNotes ?: null]);
        $customerId = $pdo->lastInsertId();
    }

    // Generate a unique bill number: CA-YYYYMMDD-NNNN
    // Based on the HIGHEST sequence number used today, not a row count —
    // a row count would collide once any bill for the day has been
    // deleted (e.g. a held bill that was later resumed).
    // Wrapped in a retry loop as a safety net against two saves landing
    // at the exact same instant.
    $datePart = date('Ymd');
    $billId = null;
    $billNo = null;
    $maxAttempts = 5;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        $maxStmt = $pdo->prepare("
            SELECT MAX(CAST(SUBSTRING_INDEX(bill_no, '-', -1) AS UNSIGNED)) AS max_seq
            FROM bills
            WHERE bill_no LIKE ?
        ");
        $maxStmt->execute(["CA-$datePart-%"]);
        $seq = (int)($maxStmt->fetch()['max_seq'] ?? 0) + $attempt; // nudge forward on each retry
        $billNo = sprintf('CA-%s-%04d', $datePart, $seq);

        try {
            $stmt = $pdo->prepare("
                INSERT INTO bills
                    (bill_no, customer_id, user_id, subtotal, discount, tax, grand_total, payment_method, amount_received, change_amount, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $billNo, $customerId, $userId, $subtotal, $discount, $tax, $grandTotal,
                $paymentMethod, $amountReceived, $changeAmount, $status,
            ]);
            $billId = $pdo->lastInsertId();
            break; // inserted successfully
        } catch (PDOException $e) {
            $isDuplicate = $e->getCode() === '23000';
            if ($isDuplicate && $attempt < $maxAttempts) {
                continue; // another request grabbed this number first — try the next one
            }
            throw $e;
        }
    }

    $itemStmt = $pdo->prepare("
        INSERT INTO bill_items (bill_id, product_id, product_name, quantity, price, total)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    foreach ($items as $item) {
        $productId = isset($item['product_id']) ? (int)$item['product_id'] : null;
        $name      = trim($item['name'] ?? 'Item');
        $qty       = max(1, (int)($item['qty'] ?? 1));
        $price     = (float)($item['price'] ?? 0);
        $lineTotal = $qty * $price;
        $itemStmt->execute([$billId, $productId, $name, $qty, $price, $lineTotal]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'bill_id' => $billId, 'bill_no' => $billNo]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Could not save bill: ' . $e->getMessage()]);
}
