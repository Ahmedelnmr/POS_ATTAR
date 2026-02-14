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
        
        $discount = floatval($invoiceData['discount'] ?? 0);
        $invoiceData['total'] = round(max(0, $total - $discount), 2);
        $invoiceData['discount'] = $discount; // Ensure it's set for repo

        try {
            $db->beginTransaction();

            // Create invoice + items
            $invoiceId = $this->purchaseRepo->create($invoiceData, $items);

            // Update stock and cost for each item
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $qty = $item['quantity'];
                
                // Determine actual quantity to add to stock
                $qtyToAdd = $qty;
                $isPack = isset($item['unit_mode']) && $item['unit_mode'] === 'pack';
                
                if ($isPack) {
                    // If buying packs, multiply by pack size
                    // We need to fetch product to be sure about current pack size or use sent data
                    // Ideally use data from product at time of addition, but DB is safer source of truth
                    // For now, we trust the pack_qty sent from frontend or fetch it?
                    // Let's fetch product to be safe and get current cost fields
                    $product = $this->productRepo->findById($productId);
                    if ($product && $product['pack_unit_quantity']) {
                        $qtyToAdd = $qty * $product['pack_unit_quantity'];
                    }
                } else {
                     $product = $this->productRepo->findById($productId);
                }

                // Increment stock
                $this->productRepo->updateStock($productId, $qtyToAdd);

                // Update Product Cost (Last Purchase Price) - Only if > 0 (Bonus Item Handling)
                if ($item['purchase_price'] > 0) {
                    $updateData = [];
                    if ($isPack) {
                        // Update Pack Purchase Price
                        $updateData['pack_purchase_price'] = $item['purchase_price']; // Price per pack
                        
                        // Auto-calculate Unit Purchase Price
                        if ($product['pack_unit_quantity'] > 0) {
                            $updateData['purchase_price'] = $item['purchase_price'] / $product['pack_unit_quantity'];
                        }
                    } else {
                        // Update Unit Purchase Price
                        $updateData['purchase_price'] = $item['purchase_price'];
                        
                        // Auto-calculate Pack Purchase Price if applicable
                        if ($product['pack_unit_quantity'] > 0) {
                            $updateData['pack_purchase_price'] = $item['purchase_price'] * $product['pack_unit_quantity'];
                        }
                    }
                    
                    if (!empty($updateData)) {
                        $this->productRepo->update($productId, $updateData);
                    }
                }

                // Record stock movement
                $this->stockMovementRepo->create([
                    'product_id' => $productId,
                    'type' => 'purchase',
                    'quantity' => $qtyToAdd, // Always record in base units
                    'reference_type' => 'purchase_invoice',
                    'reference_id' => $invoiceId,
                    'notes' => 'شراء - فاتورة #' . $invoiceId . ($isPack ? ' (عبوات)' : ''),
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

    public function getLastInvoiceItems($supplierId) {
        return $this->purchaseRepo->getLastInvoiceItems($supplierId);
    }

    public function count() {
        return $this->purchaseRepo->count();
    }

    public function getSupplierInvoiceCount($supplierId) {
        return $this->purchaseRepo->getSupplierInvoiceCount($supplierId);
    }
}
