<?php
/**
 * Sale Service - Business Logic Layer
 */

class SaleService {
    private $saleRepo;
    private $productRepo;
    private $stockMovementRepo;

    public function __construct() {
        $this->saleRepo = new SaleRepository();
        $this->productRepo = new ProductRepository();
        $this->stockMovementRepo = new StockMovementRepository();
    }

    /**
     * Delete/Void Sale and Restore Stock
     */
    public function deleteSale($id, $reason = 'Manual Delete') {
        $sale = $this->saleRepo->findById($id);
        if (!$sale) {
            return ['success' => false, 'message' => 'البيع غير موجود'];
        }

        $db = Database::getInstance();
        
        try {
            $db->beginTransaction();

            // Restore Stock
            if (!empty($sale['items'])) {
                foreach ($sale['items'] as $item) {
                     // Increase Stock (return to inventory)
                     $this->productRepo->updateStock($item['product_id'], $item['quantity']);
                     
                     // Log Stock Movement
                     $this->stockMovementRepo->create([
                         'product_id' => $item['product_id'],
                         'type' => 'adjustment', // Changed from 'return' to 'adjustment' to pass CHECK constraint
                         'quantity' => $item['quantity'],
                         'reference_type' => 'void_sale',
                         'reference_id' => $id,
                         'notes' => "إلغاء فاتورة #{$sale['sale_number']} - $reason" 
                     ]);
                }
            }

            // Delete Sale
            $this->saleRepo->delete($id);

            $db->commit();
            return ['success' => true, 'items' => $sale['items']];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Update Sale Items (Transaction: Void Old -> Create New)
     */
    public function updateSale($id, $items, $total, $discount, $reason) {
        $sale = $this->saleRepo->findById($id);
        if (!$sale) {
            return ['success' => false, 'message' => 'البيع غير موجود'];
        }

        $db = Database::getInstance();
        try {
            $db->beginTransaction();

            // 1. VOID OLD ITEMS (Restore Stock)
            if (!empty($sale['items'])) {
                foreach ($sale['items'] as $oldItem) {
                    $this->productRepo->updateStock($oldItem['product_id'], $oldItem['quantity']);
                    
                    $this->stockMovementRepo->create([
                        'product_id' => $oldItem['product_id'],
                        'type' => 'adjustment',
                        'quantity' => $oldItem['quantity'],
                        'reference_type' => 'sale_update_void',
                        'reference_id' => $id,
                        'notes' => "تعديل فاتورة #{$sale['sale_number']} - استرجاع قديم"
                    ]);
                }
            }

            // 2. DELETE OLD ITEMS
            $this->saleRepo->deleteItems($id);

            // 3. INSERT NEW ITEMS (Deduct Stock)
            foreach ($items as $item) {
                if ($item['quantity'] <= 0) continue;
                
                // Deduct Stock
                $this->productRepo->updateStock($item['product_id'], -$item['quantity']);

                // Log Movement
                $this->stockMovementRepo->create([
                    'product_id' => $item['product_id'],
                    'type' => 'sale',
                    'quantity' => -$item['quantity'],
                    'reference_type' => 'sale_update_new',
                    'reference_id' => $id,
                    'notes' => "تعديل فاتورة #{$sale['sale_number']} - إضافة جديد"
                ]);

                // Insert Item
                $this->saleRepo->addItem($id, $item);
            }

            // 4. UPDATE SALE TOTALS
            $this->saleRepo->updateTotals($id, $total, $discount, $reason);

            $db->commit();
            return ['success' => true];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => 'حدث خطأ أثناء التعديل: ' . $e->getMessage()];
        }
    }

    public function findById($id) {
        return $this->saleRepo->findById($id);
    }

    public function getAll($dateFrom = '', $dateTo = '') {
        return $this->saleRepo->getAll($dateFrom, $dateTo);
    }

    public function getTodaySummary() {
        return $this->saleRepo->getTodaySummary();
    }

    public function getSalesTotals($dateFrom, $dateTo) {
        return $this->saleRepo->getSalesTotals($dateFrom, $dateTo);
    }

    public function count() {
        return $this->saleRepo->count();
    }
}
