<?php
/**
 * Purchase Controller
 */

class PurchaseController {
    private $purchaseService;

    public function __construct() {
        $this->purchaseService = new PurchaseService();
    }

    public function index() {
        $supplierId = $_GET['supplier_id'] ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $invoices = $this->purchaseService->getAll($supplierId, $dateFrom, $dateTo);
        $suppliers = (new SupplierService())->getAll();

        include APP_PATH . '/views/layout/header.php';
        include APP_PATH . '/views/purchases/index.php';
        include APP_PATH . '/views/layout/footer.php';
    }

    public function create() {
        $suppliers = (new SupplierService())->getAll();
        $products = (new ProductService())->getAll();

        include APP_PATH . '/views/layout/header.php';
        include APP_PATH . '/views/purchases/form.php';
        include APP_PATH . '/views/layout/footer.php';
    }

    public function save() {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            // Form submit fallback
            $input = $_POST;
        }

        $invoiceData = [
            'supplier_id' => $input['supplier_id'] ?? '',
            'invoice_number' => $input['invoice_number'] ?? '',
            'date' => $input['date'] ?? date('Y-m-d'),
            'notes' => $input['notes'] ?? '',
        ];

        $items = $input['items'] ?? [];

        $result = $this->purchaseService->createInvoice($invoiceData, $items);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'json') !== false) {
            if ($result['success']) {
                Response::success($result);
            } else {
                Response::error($result['message'] ?? 'خطأ');
            }
        } else {
            header('Location: ?page=purchases');
            exit;
        }
    }

    public function view() {
        $id = $_GET['id'] ?? 0;
        $invoice = $this->purchaseService->findById($id);
        if (!$invoice) {
            header('Location: ?page=purchases');
            exit;
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Response::success($invoice);
        } else {
            include APP_PATH . '/views/layout/header.php';
            include APP_PATH . '/views/purchases/view.php';
            include APP_PATH . '/views/layout/footer.php';
        }
    }
}
