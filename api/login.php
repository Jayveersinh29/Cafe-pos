<?php
define('APP_ROOT', '../');
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: ../login.php?error=required');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    header('Location: ../login.php?error=invalid');
    exit;
}

$_SESSION['user_id']   = $user['id'];
$_SESSION['username']  = $user['username'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['role']      = $user['role'];

header('Location: ../dashboard.php');
exit;
