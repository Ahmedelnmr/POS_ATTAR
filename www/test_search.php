<?php
// Standalone Test Script for Product Search
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'app/database/Database.php';
require_once 'app/repositories/ProductRepository.php';

echo "<h1>Search API Test</h1>";

try {
    $repo = new ProductRepository();
    
    // Test 1: Count Products
    $count = $repo->count();
    echo "<p>Total Active Products: <strong>$count</strong></p>";
    
    // Test 2: Search for 'سكر' (Sugar)
    $query = 'سكر';
    echo "<p>Searching for: <strong>$query</strong></p>";
    
    $results = $repo->search($query);
    
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
    if (empty($results)) {
        echo "<h2 style='color:red'>NO RESULTS FOUND</h2>";
        
        // Debug: Show all products to see what exists
        echo "<h3>First 5 Products in DB:</h3>";
        $all = $repo->search('', 5);
        echo "<pre>";
        print_r($all);
        echo "</pre>";
    } else {
        echo "<h2 style='color:green'>SUCCESS: Found " . count($results) . " items</h2>";
    }

} catch (Exception $e) {
    echo "<h2 style='color:red'>EXCEPTION: " . $e->getMessage() . "</h2>";
}
