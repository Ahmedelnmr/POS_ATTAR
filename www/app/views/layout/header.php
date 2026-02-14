<?php
/**
 * Layout - Header & Sidebar
 */
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام نقاط البيع والمخزون</title>
    <link rel="stylesheet" href="public/css/main.css?v=<?= time() ?>">
    <script src="public/js/app_core.js?v=<?= time() ?>"></script>
</head>
<body>
<div class="toast-container" id="toastContainer"></div>
<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <h2>💰 POS System</h2>
            <span>نظام نقاط البيع والمخزون</span>
        </div>
        <nav class="sidebar-nav">
            <a href="?page=dashboard" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <span class="icon">📊</span> لوحة التحكم
            </a>
            <a href="?page=pos" class="nav-item <?= $currentPage === 'pos' ? 'active' : '' ?>">
                <span class="icon">🛒</span> نقطة البيع
            </a>
            <a href="?page=products" class="nav-item <?= $currentPage === 'products' ? 'active' : '' ?>">
                <span class="icon">📦</span> المنتجات
            </a>
            <a href="?page=suppliers" class="nav-item <?= $currentPage === 'suppliers' ? 'active' : '' ?>">
                <span class="icon">🏭</span> الموردين
            </a>
            <a href="?page=purchases" class="nav-item <?= $currentPage === 'purchases' ? 'active' : '' ?>">
                <span class="icon">🧾</span> المشتريات
            </a>
            <a href="?page=inventory" class="nav-item <?= $currentPage === 'inventory' ? 'active' : '' ?>">
                <span class="icon">📋</span> المخزون
            </a>
            <a href="?page=sales" class="nav-item <?= $currentPage === 'sales' ? 'active' : '' ?>">
                <span class="icon">💵</span> المبيعات
            </a>
            <a href="?page=reports" class="nav-item <?= $currentPage === 'reports' ? 'active' : '' ?>">
                <span class="icon">📈</span> التقارير
            </a>
        </nav>
        <div class="sidebar-footer">
            نظام نقاط البيع v1.0 &copy; <?= date('Y') ?>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
