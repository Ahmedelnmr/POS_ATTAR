<?php
/**
 * Sale Controller
 */

class SaleController {
    private $saleService;

    public function __construct() {
        $this->saleService = new SaleService();
    }

    public function index() {
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        $sales = $this->saleService->getAll($dateFrom, $dateTo);
        $todaySummary = $this->saleService->getTodaySummary();

        include APP_PATH . '/views/layout/header.php';
        include APP_PATH . '/views/sales/index.php';
        include APP_PATH . '/views/layout/footer.php';
    }

    public function view() {
        $id = $_GET['id'] ?? 0;
        $sale = $this->saleService->findById($id);
        if (!$sale) {
            header('Location: ?page=sales');
            exit;
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Response::success($sale);
        }

        include APP_PATH . '/views/layout/header.php';
        include APP_PATH . '/views/sales/receipt.php';
        include APP_PATH . '/views/layout/footer.php';
    }

    /**
     * API: Get sale details
     */
    /**
     * API: Get sale details
     */
    public function get() {
        $id = $_GET['id'] ?? 0;
        $sale = $this->saleService->findById($id);
        if ($sale) {
            Response::success($sale);
        } else {
            Response::error('الفاتورة غير موجودة');
        }
    }

    /**
     * Delete and restock sale
     */
    public function delete() {
        $id = $_GET['id'] ?? 0;
        $result = $this->saleService->deleteSale($id, 'User Requested Delete');
        
        if ($result['success']) {
            header('Location: ?page=sales&msg=deleted');
        } else {
            header('Location: ?page=sales&error=' . urlencode($result['message']));
        }
        exit;
    }

    /**
     * Show Edit Form
     */
    public function edit_form() {
        $id = $_GET['id'] ?? 0;
        $sale = $this->saleService->findById($id);
        
        if (!$sale) {
            header('Location: ?page=sales&error=' . urlencode('الفاتورة غير موجودة'));
            exit;
        }

        // Get all products for the datalist
        $productRepo = new ProductRepository();
        $products = $productRepo->getAll();

        include APP_PATH . '/views/layout/header.php';
        include APP_PATH . '/views/sales/edit.php';
        include APP_PATH . '/views/layout/footer.php';
    }

    /**
     * Handle Update Request
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header('Location: ?page=sales');
             exit;
        }

        $id = $_POST['sale_id'];
        $items = json_decode($_POST['items_json'], true);
        $total = floatval($_POST['total']);
        $discount = floatval($_POST['discount']);
        $reason = $_POST['reason'] ?? 'Manual Edit';

        // DEBUG: Log the incoming data
        file_put_contents('debug_sale_update.log', date('Y-m-d H:i:s') . "\nID: $id\nItems: " . print_r($items, true) . "\n", FILE_APPEND);

        if (empty($items)) {
             header('Location: ?page=sales&action=edit_form&id=' . $id . '&error=' . urlencode('لا توجد أصناف'));
             exit;
        }

        $result = $this->saleService->updateSale($id, $items, $total, $discount, $reason);

        // DEBUG: Log result
        if (!$result['success']) {
            file_put_contents('debug_sale_update.log', "Error: " . $result['message'] . "\n", FILE_APPEND);
        }

        if ($result['success']) {
            header('Location: ?page=sales&msg=updated');
        } else {
            header('Location: ?page=sales&action=edit_form&id=' . $id . '&error=' . urlencode($result['message']));
        }
        exit;
    }
}
