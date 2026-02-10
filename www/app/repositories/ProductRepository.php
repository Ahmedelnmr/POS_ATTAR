<?php
/**
 * Product Repository - Data Access Layer
 */

class ProductRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all active products
     */
    public function getAll($search = '', $category = '', $type = '') {
        $sql = "SELECT * FROM products WHERE is_active = 1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (name LIKE :search OR barcode LIKE :search2 OR plu_code LIKE :search3)";
            $params[':search'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }

        if ($category !== '') {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }

        if ($type !== '') {
            $sql .= " AND type = :type";
            $params[':type'] = $type;
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find product by ID
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Find product by barcode
     */
    public function findByBarcode($barcode) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE barcode = :barcode AND is_active = 1");
        $stmt->execute([':barcode' => $barcode]);
        return $stmt->fetch();
    }

    /**
     * Find product by PLU code
     */
    public function findByPLU($plu) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE plu_code = :plu AND is_active = 1");
        $stmt->execute([':plu' => $plu]);
        return $stmt->fetch();
    }

    /**
     * Search products by name (for POS quick search)
     */
    public function search($query, $limit = 20) {
        $stmt = $this->db->prepare(
            "SELECT id, name, barcode, plu_code, type, sale_price_unit, sale_price_pack, pack_quantity, stock_quantity 
             FROM products 
             WHERE is_active = 1 AND (name LIKE :q OR barcode LIKE :q2 OR plu_code LIKE :q3)
             ORDER BY name ASC
             LIMIT :limit"
        );
        $stmt->bindValue(':q', "%$query%", PDO::PARAM_STR);
        $stmt->bindValue(':q2', "%$query%", PDO::PARAM_STR);
        $stmt->bindValue(':q3', "%$query%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Create a new product
     */
    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO products (name, barcode, plu_code, type, purchase_price, sale_price_unit, sale_price_pack, pack_quantity, stock_quantity, min_stock, category, notes)
             VALUES (:name, :barcode, :plu_code, :type, :purchase_price, :sale_price_unit, :sale_price_pack, :pack_quantity, :stock_quantity, :min_stock, :category, :notes)"
        );
        $stmt->execute([
            ':name' => $data['name'],
            ':barcode' => $data['barcode'] ?: null,
            ':plu_code' => $data['plu_code'] ?: null,
            ':type' => $data['type'] ?? 'unit',
            ':purchase_price' => $data['purchase_price'] ?? 0,
            ':sale_price_unit' => $data['sale_price_unit'] ?? 0,
            ':sale_price_pack' => $data['sale_price_pack'] ?: null,
            ':pack_quantity' => $data['pack_quantity'] ?: null,
            ':stock_quantity' => $data['stock_quantity'] ?? 0,
            ':min_stock' => $data['min_stock'] ?? 0,
            ':category' => $data['category'] ?: null,
            ':notes' => $data['notes'] ?: null,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Update existing product
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE products SET 
                name = :name, barcode = :barcode, plu_code = :plu_code, type = :type,
                purchase_price = :purchase_price, sale_price_unit = :sale_price_unit,
                sale_price_pack = :sale_price_pack, pack_quantity = :pack_quantity,
                min_stock = :min_stock, category = :category, notes = :notes,
                updated_at = datetime('now', 'localtime')
             WHERE id = :id"
        );
        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':barcode' => $data['barcode'] ?: null,
            ':plu_code' => $data['plu_code'] ?: null,
            ':type' => $data['type'] ?? 'unit',
            ':purchase_price' => $data['purchase_price'] ?? 0,
            ':sale_price_unit' => $data['sale_price_unit'] ?? 0,
            ':sale_price_pack' => $data['sale_price_pack'] ?: null,
            ':pack_quantity' => $data['pack_quantity'] ?: null,
            ':min_stock' => $data['min_stock'] ?? 0,
            ':category' => $data['category'] ?: null,
            ':notes' => $data['notes'] ?: null,
        ]);
    }

    /**
     * Soft delete product
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE products SET is_active = 0, updated_at = datetime('now', 'localtime') WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Update stock quantity
     */
    public function updateStock($id, $quantityChange) {
        $stmt = $this->db->prepare("UPDATE products SET stock_quantity = stock_quantity + :qty, updated_at = datetime('now', 'localtime') WHERE id = :id");
        return $stmt->execute([':qty' => $quantityChange, ':id' => $id]);
    }

    /**
     * Get low stock products
     */
    public function getLowStock() {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE is_active = 1 AND stock_quantity <= min_stock AND min_stock > 0 ORDER BY stock_quantity ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all distinct categories
     */
    public function getCategories() {
        $stmt = $this->db->prepare("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Count all active products
     */
    public function count() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE is_active = 1");
        $stmt->execute();
        return $stmt->fetchColumn();
    }
}
