<?php
// Run migration manually
require_once 'app/database/Database.php';

try {
    $db = Database::getInstance();
    $sql = file_get_contents('app/database/migrations/003_add_discount_to_purchase_invoices.sql');
    
    // Check if column exists first to avoid error
    $check = $db->getConnection()->query("PRAGMA table_info(purchase_invoices)");
    $columns = $check->fetchAll(PDO::FETCH_COLUMN, 1);
    
    if (in_array('discount', $columns)) {
        echo "<h1>Migration Skipped: discount column already exists.</h1>";
    } else {
        $db->getConnection()->exec($sql);
        echo "<h1>Migration Applied Successfully!</h1>";
    }
} catch (Exception $e) {
    echo "<h1>Error: " . $e->getMessage() . "</h1>";
}
