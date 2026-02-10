<?php
/**
 * Product Controller
 */

class ProductController {
    private $productService;

    public function __construct() {
        $this->productService = new ProductService();
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $type = $_GET['type'] ?? '';
        $products = $this->productService->getAll($search, $category, $type);
        $categories = $this->productService->getCategories();

        include APP_PATH . '/views/layout/header.php';
        include APP_PATH . '/views/products/index.php';
        include APP_PATH . '/views/layout/footer.php';
    }

    public function create() {
        $categories = $this->productService->getCategories();
        $product = null;
        
        include APP_PATH . '/views/layout/header.php';
        include APP_PATH . '/views/products/form.php';
        include APP_PATH . '/views/layout/footer.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $product = $this->productService->findById($id);
        if (!$product) {
            header('Location: ?page=products');
            exit;
        }
        $categories = $this->productService->getCategories();

        include APP_PATH . '/views/layout/header.php';
        include APP_PATH . '/views/products/form.php';
        include APP_PATH . '/views/layout/footer.php';
    }

    public function save() {
        $data = $_POST;
        $id = $data['id'] ?? '';

        if ($id) {
            $result = $this->productService->update($id, $data);
        } else {
            $result = $this->productService->create($data);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            // AJAX request
            if ($result['success']) {
                Response::success($result);
            } else {
                Response::error($result['errors'] ?? $result['message'] ?? 'خطأ');
            }
        } else {
            header('Location: ?page=products');
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? $_POST['id'] ?? 0;
        $this->productService->delete($id);

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            Response::success(null, 'تم الحذف');
        } else {
            header('Location: ?page=products');
            exit;
        }
    }

    /**
     * API: Search products (AJAX)
     */
    public function search() {
        $q = $_GET['q'] ?? '';
        $results = $this->productService->search($q);
        Response::success($results);
    }

    /**
     * API: Get product by ID
     */
    public function get() {
        $id = $_GET['id'] ?? 0;
        $product = $this->productService->findById($id);
        if ($product) {
            Response::success($product);
        } else {
            Response::error('المنتج غير موجود');
        }
    }
}
