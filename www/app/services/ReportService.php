<?php
/**
 * Report Service - Business Logic Layer
 */

class ReportService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Daily sales report
     */
    public function dailySales($date = null) {
        $date = $date ?: date('Y-m-d');
        $stmt = $this->db->prepare(
            "SELECT s.id, s.sale_number, s.datetime, s.total, s.payment_method,
                    COUNT(si.id) as item_count
             FROM sales s
             LEFT JOIN sale_items si ON s.id = si.sale_id
             WHERE date(s.datetime) = :date
             GROUP BY s.id
             ORDER BY s.datetime DESC"
        );
        $stmt->execute([':date' => $date]);
        $sales = $stmt->fetchAll();

        // Summary
        $summaryStmt = $this->db->prepare(
            "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total,
                    COALESCE(SUM(discount), 0) as discount
             FROM sales WHERE date(datetime) = :date"
        );
        $summaryStmt->execute([':date' => $date]);
        $summary = $summaryStmt->fetch();

        return ['sales' => $sales, 'summary' => $summary, 'date' => $date];
    }

    /**
     * Sales report by date range
     */
    public function salesByRange($dateFrom, $dateTo) {
        $stmt = $this->db->prepare(
            "SELECT date(datetime) as sale_date, COUNT(*) as count, SUM(total) as total
             FROM sales 
             WHERE date(datetime) >= :from AND date(datetime) <= :to
             GROUP BY date(datetime)
             ORDER BY sale_date DESC"
        );
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        $daily = $stmt->fetchAll();

        $totalStmt = $this->db->prepare(
            "SELECT COUNT(*) as count, COALESCE(SUM(total), 0) as total
             FROM sales WHERE date(datetime) >= :from AND date(datetime) <= :to"
        );
        $totalStmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        $totals = $totalStmt->fetch();

        return ['daily' => $daily, 'totals' => $totals];
    }

    /**
     * Top selling products
     */
    public function topProducts($limit = 20, $dateFrom = null, $dateTo = null) {
        $sql = "SELECT si.product_id, si.product_name, 
                       SUM(si.quantity) as total_qty, SUM(si.subtotal) as total_revenue,
                       COUNT(DISTINCT si.sale_id) as sale_count
                FROM sale_items si
                JOIN sales s ON si.sale_id = s.id";
        $params = [];

        if ($dateFrom) {
            $sql .= " WHERE date(s.datetime) >= :from";
            $params[':from'] = $dateFrom;
            if ($dateTo) {
                $sql .= " AND date(s.datetime) <= :to";
                $params[':to'] = $dateTo;
            }
        }

        $sql .= " GROUP BY si.product_id, si.product_name
                   ORDER BY total_qty DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Least selling products
     */
    public function leastProducts($limit = 20) {
        $stmt = $this->db->prepare(
            "SELECT p.id, p.name, p.stock_quantity,
                    COALESCE(SUM(si.quantity), 0) as total_sold
             FROM products p
             LEFT JOIN sale_items si ON p.id = si.product_id
             WHERE p.is_active = 1
             GROUP BY p.id
             ORDER BY total_sold ASC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Low stock report
     */
    public function lowStock() {
        $stmt = $this->db->prepare(
            "SELECT * FROM products 
             WHERE is_active = 1 AND stock_quantity <= min_stock AND min_stock > 0
             ORDER BY (stock_quantity / CASE WHEN min_stock > 0 THEN min_stock ELSE 1 END) ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Purchases per supplier
     */
    public function purchasesBySupplier($dateFrom = null, $dateTo = null) {
        $sql = "SELECT s.id, s.name, COUNT(pi.id) as invoice_count, COALESCE(SUM(pi.total), 0) as total
                FROM suppliers s
                LEFT JOIN purchase_invoices pi ON s.id = pi.supplier_id
                WHERE s.is_active = 1";
        $params = [];

        if ($dateFrom) {
            $sql .= " AND pi.date >= :from";
            $params[':from'] = $dateFrom;
        }
        if ($dateTo) {
            $sql .= " AND pi.date <= :to";
            $params[':to'] = $dateTo;
        }

        $sql .= " GROUP BY s.id ORDER BY total DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Stock movement history
     */
    public function stockMovements($productId = null, $type = '', $limit = 100) {
        $sql = "SELECT sm.*, p.name as product_name 
                FROM stock_movements sm 
                LEFT JOIN products p ON sm.product_id = p.id 
                WHERE 1=1";
        $params = [];

        if ($productId) {
            $sql .= " AND sm.product_id = :product_id";
            $params[':product_id'] = $productId;
        }
        if ($type) {
            $sql .= " AND sm.type = :type";
            $params[':type'] = $type;
        }

        $sql .= " ORDER BY sm.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Dashboard summary
     */
    public function dashboardSummary() {
        // Today's sales
        $todayStmt = $this->db->prepare(
            "SELECT COUNT(*) as sales_count, COALESCE(SUM(total), 0) as sales_total
             FROM sales WHERE date(datetime) = date('now', 'localtime')"
        );
        $todayStmt->execute();
        $today = $todayStmt->fetch();

        // Product count
        $prodStmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE is_active = 1");
        $prodStmt->execute();
        $productCount = $prodStmt->fetchColumn();

        // Low stock count
        $lowStmt = $this->db->prepare(
            "SELECT COUNT(*) FROM products WHERE is_active = 1 AND stock_quantity <= min_stock AND min_stock > 0"
        );
        $lowStmt->execute();
        $lowStockCount = $lowStmt->fetchColumn();

        // Monthly sales
        $monthStmt = $this->db->prepare(
            "SELECT COALESCE(SUM(total), 0) as total FROM sales 
             WHERE strftime('%Y-%m', datetime) = strftime('%Y-%m', 'now', 'localtime')"
        );
        $monthStmt->execute();
        $monthTotal = $monthStmt->fetchColumn();

        return [
            'today_sales_count' => $today['sales_count'],
            'today_sales_total' => $today['sales_total'],
            'product_count' => $productCount,
            'low_stock_count' => $lowStockCount,
            'month_total' => $monthTotal,
        ];
    }

    /**
     * Financial Report (Income vs Expenses)
     */
    public function financialReport($dateFrom, $dateTo) {
        $sql = "
            SELECT 
                d.date,
                COALESCE(SUM(s.total), 0) as income,
                COALESCE(SUM(pi.total), 0) as expense
            FROM (
                SELECT date(datetime) as date FROM sales WHERE date(datetime) BETWEEN :from1 AND :to1
                UNION
                SELECT date FROM purchase_invoices WHERE date BETWEEN :from2 AND :to2
            ) d
            LEFT JOIN sales s ON date(s.datetime) = d.date
            LEFT JOIN purchase_invoices pi ON pi.date = d.date
            GROUP BY d.date
            ORDER BY d.date DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':from1' => $dateFrom, ':to1' => $dateTo,
            ':from2' => $dateFrom, ':to2' => $dateTo
        ]);
        
        $daily = $stmt->fetchAll();
        
        // Calculate totals
        $totalIncome = 0;
        $totalExpense = 0;
        foreach ($daily as $row) {
            $totalIncome += $row['income'];
            $totalExpense += $row['expense'];
        }
        
        return [
            'daily' => $daily,
            'totals' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'profit' => $totalIncome - $totalExpense
            ]
        ];
    }
}

