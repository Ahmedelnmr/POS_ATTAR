<?php
ob_start();
/**
 * POS System - Main Entry Point & Router
 * Routes requests to appropriate controllers
 */

// Error reporting (enable for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Set timezone
date_default_timezone_set('Africa/Cairo');

// Set encoding
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}
header('Content-Type: text/html; charset=utf-8');

// Base path constant
define('BASE_PATH', __DIR__);
define('APP_PATH', __DIR__ . '/app');

// Autoload helpers
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/helpers/Response.php';
require_once APP_PATH . '/helpers/Validator.php';

// Initialize database & run migrations
$db = Database::getInstance();
$db->migrate();

// Determine the requested page and action
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Route map
$routes = [
    'dashboard'  => 'DashboardController',
    'pos'        => 'POSController',
    'products'   => 'ProductController',
    'suppliers'  => 'SupplierController',
    'purchases'  => 'PurchaseController',
    'inventory'  => 'InventoryController',
    'sales'      => 'SaleController',
    'reports'    => 'ReportController',
];

// Load required files based on route
if (isset($routes[$page])) {
    $controllerName = $routes[$page];
    $controllerFile = APP_PATH . "/controllers/{$controllerName}.php";
    
    if (file_exists($controllerFile)) {
        // Load repositories
        foreach (glob(APP_PATH . '/repositories/*.php') ?: [] as $repo) {
            require_once $repo;
        }
        
        // Load services
        foreach (glob(APP_PATH . '/services/*.php') ?: [] as $service) {
            require_once $service;
        }
        
        // Load engines
        foreach (glob(APP_PATH . '/pos_engine/*.php') ?: [] as $engine) {
            require_once $engine;
        }
        foreach (glob(APP_PATH . '/inventory_engine/*.php') ?: [] as $engine) {
            require_once $engine;
        }
        foreach (glob(APP_PATH . '/report_engine/*.php') ?: [] as $engine) {
            require_once $engine;
        }
        
        // Load controller
        require_once $controllerFile;
        
        $controller = new $controllerName();
        
        // Check if action method exists
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            // Default to index
            $controller->index();
        }
    } else {
        http_response_code(404);
        echo '<h1>Controller not found</h1>';
    }
} else {
    http_response_code(404);
    echo '<h1>Page not found</h1>';
}
