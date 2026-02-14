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
    <link rel="stylesheet" href="public/css/pos_simple.css?v=<?= time() ?>">
</head>
<body>
<script>
window.onerror = function(msg, url, line, col, error) {
    alert("JS Error: " + msg + "\nIn: " + url + "\nLine: " + line);
    return false;
};
</script>
<div class="toast-container" id="toastContainer"></div>

<div class="pos-fullscreen">
    <!-- Top Navigation Bar -->
    <div class="pos-topbar">
        <div style="display:flex;align-items:center;gap:20px;">
            <a href="?page=dashboard" class="btn btn-ghost btn-sm">🏠 الرئيسية</a>
            <h2>🛒 نقطة البيع</h2>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <button class="btn btn-outline btn-sm" onclick="openDiscount()">🏷️ خصم</button>
            <button class="btn btn-outline btn-sm" onclick="openManualPrice()">💲 يدوي</button>
            <button class="btn btn-outline btn-sm" onclick="openWeightInput()">⚖️ وزن</button>
            <button class="btn btn-danger btn-sm" onclick="clearCart()">🗑️ تفريغ</button>
        </div>
    </div>

    <!-- Input Section -->
    <div class="pos-input-section">
        <div class="pos-input-wrapper">
             <!-- Embedded Scanner Container -->
            <div id="embeddedScanner" style="width: 320px; height: 240px; background: #000; overflow: hidden; border-radius: 8px; border: 2px solid var(--primary); margin-left: 10px; position: relative;">
                <div id="reader" style="width: 100%; height: 100%;"></div>
                <div id="scannerStatus" style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,0,0,0.5); color:white; font-size:10px; text-align:center; padding:2px;">جاري التشغيل...</div>
            </div>

            <div style="flex: 1;">
                <div class="pos-input-group">
                    <span class="pos-input-icon">📷</span>
                    <input type="text" id="barcodeInput" class="pos-barcode-input" 
                           placeholder="امسح الباركود أو أدخل الكود (F2)..." 
                           autocomplete="off" autofocus>
                    <div class="pos-search-results" id="searchResults"></div>
                </div>
                <div class="pos-input-group">
                    <span class="pos-input-icon">🔍</span>
                    <input type="text" id="productSearch" class="pos-barcode-input" 
                           placeholder="ابحث عن منتج بالاسم..." 
                           oninput="searchProducts(this.value)">
                    <div class="pos-search-results" id="productSearchResults"></div>
                </div>
            </div>
        </div>
        <div class="pos-shortcuts-hint">
            <span>F2: باركود</span>
            <span>F3: تفاصيل</span>
            <span>F5: إتمام</span>
            <span>F8: تبديل</span>
            <span>↑↓: تنقل</span>
        </div>
    </div>

    <!-- Main Cart Area -->
    <div class="pos-main-cart">
        <div class="pos-cart-container">
            <table class="pos-cart-table">
                <thead>
                    <tr>
                        <th style="width:50px">#</th>
                        <th>المنتج</th>
                        <th style="width:140px">نوع الوحدة</th>
                        <th style="width:140px">الكمية</th>
                        <th style="width:100px">السعر</th>
                        <th style="width:110px">المجموع</th>
                        <th style="width:50px"></th>
                    </tr>
                </thead>
                <tbody id="cartBody">
                    <tr id="emptyCart">
                        <td colspan="7">
                            <div class="empty-cart-state">
                                <div class="icon">🛒</div>
                                <p>السلة فارغة - ابدأ بمسح باركود أو البحث عن منتج</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Totals Bar -->
    <div class="pos-bottombar">
        <div class="pos-bottombar-content">
            <div class="pos-totals">
                <div class="total-row">
                    <div class="total-row-label">المجموع الفرعي</div>
                    <div class="total-row-value" id="subtotalDisplay">0.00</div>
                </div>
                <div class="total-row">
                    <div class="total-row-label">الخصم</div>
                    <div class="total-row-value" id="discountDisplay">0.00</div>
                </div>
                <div class="total-row total-final">
                    <div class="total-row-label">الإجمالي</div>
                    <div class="total-row-value" id="totalDisplay">0.00</div>
                </div>
            </div>
            <button id="checkoutBtn" onclick="processCheckout()" disabled>
                ✅ إتمام البيع (F5)
            </button>
        </div>
    </div>
</div>

<!-- Removed Scanner Modal -->

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
            <h3>Receipt</h3>
            <button class="modal-close" onclick="closeModal('receiptModal')">&times;</button>
        </div>
        <div class="modal-body" id="receiptContent">
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('receiptModal')">Close</button>
            <button class="btn btn-primary" onclick="printReceipt()">Print</button>
        </div>
    </div>
</div>

<script src="public/js/html5-qrcode.min.js?v=<?= time() ?>"></script>
<script>
let html5QrCode;

function startScanner() {
    // Check if html5QrCode is defined
    if (typeof Html5Qrcode === "undefined") {
        document.getElementById('scannerStatus').textContent = "خطأ: المكتبة غير محملة";
        document.getElementById('scannerStatus').style.background = "red";
        return;
    }

    html5QrCode = new Html5Qrcode("reader");
    // Try environment first, but dont enforce strict if desktop (no back camera)
    // Actually Html5Qrcode handles "environment" gracefully usually
    html5QrCode.start(
        { facingMode: "environment" }, 
        { fps: 10, qrbox: { width: 250, height: 150 } }, // Explicit box size
        (decodedText, decodedResult) => {
            // Success
            document.getElementById('scannerStatus').textContent = "✅ تم المسح";
            document.getElementById('scannerStatus').style.background = "green";
            setTimeout(() => {
                 document.getElementById('scannerStatus').textContent = "📷 جاري المسح...";
                 document.getElementById('scannerStatus').style.background = "rgba(0,0,0,0.5)";
            }, 1000);
            
            // Check for duplicate scan
            if (window.lastScannedCode === decodedText) {
                const now = new Date().getTime();
                if (now - window.lastScannedTime < 2000) {
                    return; // Ignore duplicate scan within 2 seconds
                }
            }
            
            window.lastScannedCode = decodedText;
            window.lastScannedTime = new Date().getTime();

            // Add to cart directly
            addByCode(decodedText);
            
            // Play sound
            let audio = new Audio("public/audio/beep.mp3");
            audio.play().catch(e => {});

            // NO stopScanner() - Keep running!
        },
        (errorMessage) => {
            // ignore
        }
    ).then(() => {
        document.getElementById('scannerStatus').textContent = "📷 الكاميرا تعمل";
    }).catch(err => {
        console.warn("Camera start failed: " + err);
        document.getElementById('scannerStatus').textContent = "❌ خطأ: الكاميرا";
        document.getElementById('scannerStatus').style.background = "red";
    });
}

// Auto-start on load
document.addEventListener('DOMContentLoaded', function() {
    startScanner();
});


</script>
<script src="public/js/pos_core.js?v=<?= time() ?>"></script>
<script src="public/js/pos_shortcuts.js?v=<?= time() ?>"></script>
</body>
</html>
