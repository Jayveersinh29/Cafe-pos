<?php
/**
 * Database connection (PDO / MySQL)
 * Edit the constants below to match your XAMPP/WAMP setup.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'cafe_pos');
define('DB_USER', 'root');
define('DB_PASS', '');     // default XAMPP root password is empty
define('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die("Database connection failed: " . $e->getMessage() .
        "<br>Make sure MySQL is running and that you imported database/cafe_pos.sql.");
}
