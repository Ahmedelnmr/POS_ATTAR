<?php
/**
 * POS Main Screen - Standalone (no sidebar layout)
 */
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نقطة البيع - POS</title>
    <link rel="stylesheet" href="public/css/style.css?v=<?= time() ?>">
</head>
<body>
<div class="toast-container" id="toastContainer"></div>

<div class="pos-wrapper">
    <!-- Right Side: Cart & Checkout -->
    <div class="pos-sidebar">
        <!-- Barcode Input -->
        <div class="pos-input-area">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                <a href="?page=dashboard" class="btn btn-ghost btn-sm" title="العودة">🏠</a>
                <h3 style="flex:1;font-size:16px;">🛒 نقطة البيع</h3>
                <button class="btn btn-ghost btn-sm" onclick="clearCart()" title="تفريغ السلة">🗑️</button>
            </div>
            <div style="position:relative;">
                <input type="text" id="barcodeInput" class="pos-barcode-input" 
                       placeholder="📷 امسح الباركود أو أدخل الكود..." 
                       autocomplete="off" autofocus>
                <div class="pos-search-results" id="searchResults"></div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="pos-actions-bar">
            <button class="pos-action-btn" onclick="openManualPrice()">💲 سعر يدوي</button>
            <button class="pos-action-btn" onclick="openWeightInput()">⚖️ وزن</button>
            <button class="pos-action-btn" onclick="openSearch()">🔍 بحث</button>
            <button class="pos-action-btn" onclick="openDiscount()">🏷️ خصم</button>
        </div>

        <!-- Cart Table -->
        <div class="pos-cart">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>المنتج</th>
                        <th style="width:110px">الكمية</th>
                        <th style="width:70px">السعر</th>
                        <th style="width:80px">المجموع</th>
                        <th style="width:36px"></th>
                    </tr>
                </thead>
                <tbody id="cartBody">
                    <tr id="emptyCart">
                        <td colspan="6" class="text-center text-muted" style="padding:40px;">
                            السلة فارغة - امسح باركود أو ابحث عن منتج
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Totals & Checkout -->
        <div class="pos-totals">
            <div class="pos-total-row">
                <span>المجموع الفرعي:</span>
                <span id="subtotalDisplay">0.00</span>
            </div>
            <div class="pos-total-row">
                <span>الخصم:</span>
                <span id="discountDisplay">0.00</span>
            </div>
            <div class="pos-total-row grand-total">
                <span>الإجمالي:</span>
                <span id="totalDisplay">0.00</span>
            </div>
            <button class="pos-checkout-btn" id="checkoutBtn" onclick="processCheckout()" disabled>
                ✅ إتمام البيع (F5)
            </button>
        </div>
    </div>

    <!-- Left Side: Product Search / Grid -->
    <div class="pos-products-area pos-main">
        <div class="pos-products-search">
            <input type="text" class="form-control" id="productSearch" 
                   placeholder="🔍 ابحث عن منتج بالاسم أو الباركود..." 
                   oninput="searchProducts(this.value)">
        </div>
        <div class="pos-products-grid" id="productsGrid">
            <div class="empty-state" style="grid-column: 1/-1;">
                <div class="icon">📦</div>
                <p>ابحث عن منتج أو امسح الباركود</p>
            </div>
        </div>
    </div>
</div>

<!-- Manual Price Modal -->
<div class="modal-overlay" id="manualPriceModal">
    <div class="modal">
        <div class="modal-header">
            <h3>💲 سعر يدوي</h3>
            <button class="modal-close" onclick="closeModal('manualPriceModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>اسم المنتج</label>
                <input type="text" class="form-control" id="manualName" placeholder="أدخل اسم المنتج">
            </div>
            <div class="form-group">
                <label>السعر</label>
                <input type="number" class="form-control" id="manualPriceValue" step="0.01" min="0" placeholder="0.00">
            </div>
            <div class="form-group">
                <label>الكمية</label>
                <input type="number" class="form-control" id="manualQty" value="1" min="0.01" step="0.01">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('manualPriceModal')">إلغاء</button>
            <button class="btn btn-primary" onclick="addManualPrice()">إضافة للسلة</button>
        </div>
    </div>
</div>

<!-- Weight Input Modal -->
<div class="modal-overlay" id="weightModal">
    <div class="modal">
        <div class="modal-header">
            <h3>⚖️ إدخال الوزن</h3>
            <button class="modal-close" onclick="closeModal('weightModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>المنتج</label>
                <input type="text" class="form-control" id="weightProductSearch" 
                       placeholder="ابحث عن المنتج بالاسم أو PLU..." 
                       oninput="searchWeightProducts(this.value)">
                <div id="weightProductResults" style="margin-top:8px;"></div>
                <input type="hidden" id="weightProductId">
                <div id="weightProductName" class="mt-1 fw-bold" style="color:var(--accent);"></div>
            </div>
            <div class="form-group">
                <label>الوزن (كجم)</label>
                <input type="number" class="form-control" id="weightValue" step="0.001" min="0.001" placeholder="0.000">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('weightModal')">إلغاء</button>
            <button class="btn btn-primary" onclick="addWeightItem()">إضافة للسلة</button>
        </div>
    </div>
</div>

<!-- Discount Modal -->
<div class="modal-overlay" id="discountModal">
    <div class="modal">
        <div class="modal-header">
            <h3>🏷️ خصم</h3>
            <button class="modal-close" onclick="closeModal('discountModal')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>قيمة الخصم</label>
                <input type="number" class="form-control" id="discountValue" step="0.01" min="0" placeholder="0.00">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('discountModal')">إلغاء</button>
            <button class="btn btn-primary" onclick="applyDiscount()">تطبيق</button>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal-overlay" id="receiptModal">
    <div class="modal" style="max-width:380px;">
        <div class="modal-header">
            <h3>🧾 إيصال البيع</h3>
            <button class="modal-close" onclick="closeModal('receiptModal')">&times;</button>
        </div>
        <div class="modal-body" id="receiptContent">
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('receiptModal')">إغلاق</button>
            <button class="btn btn-primary" onclick="printReceipt()">🖨️ طباعة</button>
        </div>
    </div>
</div>

<script src="public/js/app.js"></script>
<script src="public/js/pos.js"></script>
</body>
</html>
