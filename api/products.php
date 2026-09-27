<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

require_login(); // any logged-in staff/admin can call this

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

    // Staff (POS screen) only need active products; admin product page gets everything.
    $onlyActive = !is_admin();
    $sql = "SELECT p.id, p.name, p.category_id, c.name AS category_name, p.price, p.image, p.is_active
            FROM products p
            JOIN categories c ON c.id = p.category_id";
    if ($onlyActive) {
        $sql .= " WHERE p.is_active = 1";
    }
    $sql .= " ORDER BY p.name";
    $products = $pdo->query($sql)->fetchAll();

    echo json_encode(['success' => true, 'categories' => $categories, 'products' => $products]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();

    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $id         = $data['id'] ?? null;
    $name       = trim($data['name'] ?? '');
    $categoryId = (int)($data['category_id'] ?? 0);
    $price      = (float)($data['price'] ?? 0);
    $isActive   = isset($data['is_active']) ? (int)$data['is_active'] : 1;

    if ($name === '' || $categoryId <= 0 || $price < 0) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid name, category and price.']);
        exit;
    }

    if ($id) {
        $stmt = $pdo->prepare("UPDATE products SET name=?, category_id=?, price=?, is_active=? WHERE id=?");
        $stmt->execute([$name, $categoryId, $price, $isActive, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, category_id, price, is_active) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $categoryId, $price, $isActive]);
        $id = $pdo->lastInsertId();
    }

    echo json_encode(['success' => true, 'id' => $id]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported method']);
