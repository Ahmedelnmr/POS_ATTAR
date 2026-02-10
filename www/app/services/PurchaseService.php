<?php
/**
 * Purchase Service - Business Logic Layer
 */

class PurchaseService {
    private $purchaseRepo;
    private $productRepo;
    private $stockMovementRepo;

    public function __construct() {
        $this->purchaseRepo = new PurchaseRepository();
        $this->productRepo = new ProductRepository();
        $this->stockMovementRepo = new StockMovementRepository();
    }

    /**
     * Create purchase invoice with stock update (transactional)
     */
    public function createInvoice($invoiceData, $items) {
        $db = Database::getInstance();
        
        // Validate
        if (empty($invoiceData['supplier_id'])) {
            return ['success' => false, 'message' => 'المورد مطلوب'];
        }
        if (empty($items)) {
            return ['success' => false, 'message' => 'يجب إضافة منتج واحد على الأقل'];
        }

        // Calculate total
        $total = 0;
        foreach ($items as &$item) {
            $item['subtotal'] = round($item['quantity'] * $item['purchase_price'], 2);
            $total += $item['subtotal'];
        }
        $invoiceData['total'] = round($total, 2);

        try {
            $db->beginTransaction();

            // Create invoice + items
            $invoiceId = $this->purchaseRepo->create($invoiceData, $items);

            // Update stock for each item
            foreach ($items as $item) {
                // Increment product stock
                $this->productRepo->updateStock($item['product_id'], $item['quantity']);

                // Update purchase price on product
                $this->productRepo->update($item['product_id'], [
                    'name' => '', // Will be ignored if we add a specific method
                ]);

                // Record stock movement
                $this->stockMovementRepo->create([
                    'product_id' => $item['product_id'],
                    'type' => 'purchase',
                    'quantity' => $item['quantity'],
                    'reference_type' => 'purchase_invoice',
                    'reference_id' => $invoiceId,
                    'notes' => 'شراء - فاتورة #' . $invoiceId,
                ]);
            }

            $db->commit();
            return ['success' => true, 'id' => $invoiceId];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'خطأ: ' . $e->getMessage()];
        }
    }

    public function findById($id) {
        return $this->purchaseRepo->findById($id);
    }

    public function getAll($supplierId = '', $dateFrom = '', $dateTo = '') {
        return $this->purchaseRepo->getAll($supplierId, $dateFrom, $dateTo);
    }

    public function count() {
        return $this->purchaseRepo->count();
    }
}
