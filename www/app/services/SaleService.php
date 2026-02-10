<?php
/**
 * Sale Service - Business Logic Layer
 */

class SaleService {
    private $saleRepo;

    public function __construct() {
        $this->saleRepo = new SaleRepository();
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
