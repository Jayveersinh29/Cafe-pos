<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');
require_admin();

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$id = (int)($data['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product id.']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    // Likely a foreign key constraint because the product is used in bill_items.
    echo json_encode([
        'success' => false,
        'message' => 'This product is used in existing bills and cannot be deleted. Try deactivating it instead.',
    ]);
}
