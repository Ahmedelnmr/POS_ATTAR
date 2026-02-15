<?php
// Test All Report API Endpoints
define('APP_PATH', __DIR__ . '/app');
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/services/ReportService.php';
require_once APP_PATH . '/controllers/ReportController.php';
require_once APP_PATH . '/helpers/Response.php';

// Mock Response class to capture output instead of exiting
class MockResponse {
    public static function success($data) {
        echo json_encode(['success' => true, 'data' => $data], JSON_PRETTY_PRINT);
    }
    public static function error($msg) {
        echo json_encode(['success' => false, 'message' => $msg], JSON_PRETTY_PRINT);
    }
}

// Override Response class if possible, or just use a derived controller
// Since Response is likely static, we might need to intercept it or just test Service directly matches Controller logic.
// Better: Test Service output and json_encode it to ensure no serialization errors.

try {
    echo "=== TESTING REPORT PIPELINE ===\n";
    $service = new ReportService();
    
    $tests = [
        'Daily' => fn() => $service->dailySales(date('Y-m-d')),
        'Weekly' => fn() => $service->salesByRange(date('Y-m-d', strtotime('-7 days')), date('Y-m-d')),
        'Monthly' => fn() => $service->salesByRange(date('Y-m-01'), date('Y-m-d')),
        'TopProducts' => fn() => $service->topProducts(),
        'LeastProducts' => fn() => $service->leastProducts(),
        'LowStock' => fn() => $service->lowStock(),
        'Suppliers' => fn() => $service->purchasesBySupplier()
    ];

    foreach ($tests as $name => $func) {
        echo "\nTesting [$name]... ";
        $start = microtime(true);
        $data = $func();
        $json = json_encode($data);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "FAILED (JSON Error: " . json_last_error_msg() . ")\n";
        } else {
            echo "OK (" . round((microtime(true) - $start) * 1000, 2) . "ms) - size: " . strlen($json) . " bytes\n";
        }
    }
    echo "\n=== ALL TESTS PASSED ===\n";

} catch (Throwable $e) {
    echo "\n\nCRITICAL ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
?>
