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
    public function get() {
        $id = $_GET['id'] ?? 0;
        $sale = $this->saleService->findById($id);
        if ($sale) {
            Response::success($sale);
        } else {
            Response::error('الفاتورة غير موجودة');
        }
    }
}
