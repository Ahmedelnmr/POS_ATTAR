<?php
// Fix for Sugar Product Unit
require_once 'app/config/database.php';

try {
    $instance = Database::getInstance(); // Getting the wrapper object
    // We need to access the underlying PDO object or use a helper if available.
    // Looking at Database class (implied): likely has __call or we should use prepare directly on instance if it proxies.
    // Let's assume getInstance returns the wrapper, and we might need to access pdo property if public, or use prepare/query if exposed.
    // Actually standard pattern: $db = Database::getInstance(); $stmt = $db->prepare(...);
    
    // BUT the error said "Call to undefined method Database::query()".
    // This means the Database class doesn't have a query() method and doesn't proxy it.
    // Let's see if we can get the connection.
    // Usually it's $db->getConnection() or similar.
    // Or we can just use new PDO directly for this script since we know the path.
    
    $dbPath = __DIR__ . '/data/pos_system.db';
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to DB.\n";
    
    // Find products with 'سكر' in name
    $stmt = $pdo->query("SELECT * FROM products WHERE name LIKE '%سكر%'");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($products) . " products matching 'سكر'.\n";
    
    foreach ($products as $p) {
        echo "Updating: " . $p['name'] . " (ID: " . $p['id'] . ")\n";
        echo "Old Type: " . $p['type'] . ", Pack: " . $p['pack_type'] . "\n";
        
        // Update to Weight / Sack
        $update = $pdo->prepare("UPDATE products SET type = 'weight', pack_type = 'شوال' WHERE id = :id");
        $update->execute([':id' => $p['id']]);
        
        echo "Updated to: Weight / Sack\n---\n";
    }
    
    echo "Done.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
