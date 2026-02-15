<?php
// Test Report Service
define('APP_PATH', __DIR__ . '/app');
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/services/ReportService.php';

try {
    echo "Testing ReportService...\n";
    
    $service = new ReportService();
    
    echo "1. Testing dashboardSummary()...\n";
    $summary = $service->dashboardSummary();
    print_r($summary);
    echo "OK\n\n";

    echo "2. Testing dailySales()...\n";
    $daily = $service->dailySales(); // defaults to today
    print_r($daily['summary']);
    echo "OK\n\n";

    echo "3. Testing topProducts()...\n";
    $top = $service->topProducts();
    echo "Count: " . count($top) . "\n";
    echo "OK\n\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>
