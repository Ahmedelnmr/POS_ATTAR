<?php
/**
 * Product Service - Business Logic Layer
 */

class ProductService {
    private $productRepo;

    public function __construct() {
        $this->productRepo = new ProductRepository();
    }

    public function getAll($search = '', $category = '', $type = '') {
        return $this->productRepo->getAll($search, $category, $type);
    }

    public function findById($id) {
        return $this->productRepo->findById($id);
    }

    public function findByBarcode($barcode) {
        return $this->productRepo->findByBarcode($barcode);
    }

    public function findByPLU($plu) {
        return $this->productRepo->findByPLU($plu);
    }

    public function search($query) {
        return $this->productRepo->search($query);
    }

    public function create($data) {
        // Sanitize
        if (isset($data['barcode'])) {
            $data['barcode'] = trim($data['barcode']);
        }

        // Validate
        $validator = new Validator($data);
        $validator->required('name', 'اسم المنتج')
                  ->numeric('purchase_price', 'سعر الشراء')
                  ->numeric('sale_price_unit', 'سعر البيع')
                  ->min('purchase_price', 0, 'سعر الشراء')
                  ->min('sale_price_unit', 0, 'سعر البيع');

        if (!$validator->passes()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Check duplicate barcode
        if (!empty($data['barcode'])) {
            $existing = $this->productRepo->findByBarcode($data['barcode']);
            if ($existing) {
                return ['success' => false, 'errors' => ['barcode' => 'الباركود موجود بالفعل']];
            }
        }

        $id = $this->productRepo->create($data);
        return ['success' => true, 'id' => $id];
    }

    public function update($id, $data) {
        // Sanitize
        if (isset($data['barcode'])) {
            $data['barcode'] = trim($data['barcode']);
        }

        $validator = new Validator($data);
        $validator->required('name', 'اسم المنتج')
                  ->numeric('purchase_price', 'سعر الشراء')
                  ->numeric('sale_price_unit', 'سعر البيع');

        if (!$validator->passes()) {
            return ['success' => false, 'errors' => $validator->errors()];
        }

        // Check duplicate barcode (exclude current)
        if (!empty($data['barcode'])) {
            $existing = $this->productRepo->findByBarcode($data['barcode']);
            if ($existing && $existing['id'] != $id) {
                return ['success' => false, 'errors' => ['barcode' => 'الباركود موجود بالفعل']];
            }
        }

        $this->productRepo->update($id, $data);
        return ['success' => true];
    }

    public function delete($id) {
        return $this->productRepo->delete($id);
    }

    public function getLowStock() {
        return $this->productRepo->getLowStock();
    }

    public function getCategories() {
        return $this->productRepo->getCategories();
    }

    public function count() {
        return $this->productRepo->count();
    }
}
