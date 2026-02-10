<?php
// Test Database Connection and Content
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/app/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check products
    $stmt = $db->query("SELECT * FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'count' => count($products),
        'products' => $products,
        'php_version' => phpversion(),
        'extensions' => get_loaded_extensions()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
