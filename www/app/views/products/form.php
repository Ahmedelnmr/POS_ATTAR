<!-- Product Form (Create/Edit) -->
<?php $isEdit = !empty($product); ?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? '✏️ تعديل المنتج' : '➕ إضافة منتج جديد' ?></h1>
    </div>
    <a href="?page=products" class="btn btn-outline">← العودة للقائمة</a>
</div>

<div class="card">
    <form method="POST" action="?page=products&action=save" id="productForm">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $product['id'] ?>">
        <?php endif; ?>

        <div class="form-row">
            <div class="form-group">
                <label>اسم المنتج *</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($product['name'] ?? '') ?>" placeholder="أدخل اسم المنتج">
            </div>
            <div class="form-group">
                <label>الباركود</label>
                <div style="display:flex; align-items:center;">
                    <input type="text" name="barcode" id="barcode" class="form-control" value="<?= htmlspecialchars($product['barcode'] ?? '') ?>" placeholder="اختياري" style="flex:1;">
                    <button type="button" class="btn btn-warning" onclick="startScanner()" style="margin-right:10px; min-width:80px;">📷 مسح</button>
                </div>
            </div>
            <div class="form-group">
                <label>كود PLU</label>
                <input type="text" name="plu_code" class="form-control" value="<?= htmlspecialchars($product['plu_code'] ?? '') ?>" placeholder="كود سريع">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>النوع *</label>
                <select name="type" class="form-control" required>
                    <option value="unit" <?= ($product['type'] ?? '') === 'unit' ? 'selected' : '' ?>>وحدة (قطعة)</option>
                    <option value="pack" <?= ($product['type'] ?? '') === 'pack' ? 'selected' : '' ?>>عبوة (جملة)</option>
                    <option value="weight" <?= ($product['type'] ?? '') === 'weight' ? 'selected' : '' ?>>وزن (كجم)</option>
                </select>
            </div>
            <div class="form-group">
                <label>القسم</label>
                <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($product['category'] ?? '') ?>" placeholder="مثال: بهارات، بقالة" list="categoryList">
                <datalist id="categoryList">
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>سعر شراء القطعة الواحدة *</label>
                <input type="number" name="purchase_price" id="purchase_price" class="form-control" step="0.01" min="0" required value="<?= $product['purchase_price'] ?? '0' ?>" readonly style="background-color: #f0f0f0;">
                <small class="text-muted">يحسب تلقائياً من سعر شراء الوحدة الكاملة</small>
            </div>
            <div class="form-group">
                <label>سعر بيع القطعة الواحدة *</label>
                <input type="number" name="sale_price_unit" class="form-control" step="0.01" min="0" required value="<?= $product['sale_price_unit'] ?? '0' ?>">
            </div>
        </div>

        <fieldset style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
            <legend style="font-size: 16px; font-weight: bold; padding: 0 10px;">📦 بيانات التعبئة والجملة (اختياري)</legend>
            
            <div class="form-row">
                <div class="form-group">
                    <label>نوع التعبئة</label>
                    <select name="pack_type" id="pack_type" class="form-control">
                        <option value="">-- بدون تعبئة --</option>
                        <?php 
                        $packTypes = ['كرتونة', 'لفة', 'عبوة', 'حزمة', 'صندوق', 'علبة', 'دستة', 'باكيت', 'شنطة', 'كيس'];
                        foreach ($packTypes as $type): 
                        ?>
                        <option value="<?= $type ?>" <?= ($product['pack_type'] ?? '') === $type ? 'selected' : '' ?>><?= $type ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>عدد القطع في الوحدة</label>
                    <input type="number" name="pack_unit_quantity" id="pack_unit_quantity" class="form-control" min="1" value="<?= $product['pack_unit_quantity'] ?? '' ?>" placeholder="مثال: 12">
                    <small class="text-muted">كم قطعة في كل وحدة (كرتونة/لفة/etc.)</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>سعر شراء الوحدة الكاملة</label>
                    <input type="number" name="pack_purchase_price" id="pack_purchase_price" class="form-control" step="0.01" min="0" value="<?= $product['pack_purchase_price'] ?? '' ?>" placeholder="مثال: 120">
                    <small class="text-muted">سيحسب سعر القطعة تلقائياً</small>
                </div>
                <div class="form-group">
                    <label>سعر بيع الوحدة الكاملة</label>
                    <input type="number" name="pack_sale_price" id="pack_sale_price" class="form-control" step="0.01" min="0" value="<?= $product['pack_sale_price'] ?? '' ?>" placeholder="مثال: 150">
                    <small class="text-muted">سعر البيع للوحدة كاملة (مستقل عن سعر القطعة)</small>
                </div>
            </div>
        </fieldset>


        <div class="form-row">
            <?php if (!$isEdit): ?>
            <div class="form-group">
                <label>المخزون الابتدائي</label>
                <input type="number" name="stock_quantity" class="form-control" step="0.01" min="0" value="0">
            </div>
            <?php endif; ?>
            <div class="form-group">
                <label>حد المخزون الأدنى</label>
                <input type="number" name="min_stock" class="form-control" step="0.01" min="0" value="<?= $product['min_stock'] ?? '0' ?>">
            </div>
        </div>

        <div class="form-group">
            <label>ملاحظات</label>
            <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($product['notes'] ?? '') ?></textarea>
        </div>

        <div class="btn-group mt-2">
            <button type="submit" class="btn btn-primary btn-lg">💾 <?= $isEdit ? 'تحديث المنتج' : 'حفظ المنتج' ?></button>
            <a href="?page=products" class="btn btn-outline btn-lg">إلغاء</a>
        </div>
    </form>
</div>

<!-- Scanner Modal -->
<div id="scannerModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3>📷 مسح الباركود</h3>
            <button type="button" class="modal-close" onclick="stopScanner()">×</button>
        </div>
        <div class="modal-body">
            <div id="reader" style="width: 100%;"></div>
            <p class="text-muted text-center mt-2">وجه الكاميرا نحو الباركود</p>
        </div>
    </div>
</div>

<script src="public/js/html5-qrcode.min.js?v=<?= time() ?>"></script>
<script>
let html5QrCode;

function startScanner() {
    document.getElementById("scannerModal").style.display = "flex";
    
    // Check if html5QrCode is defined
    if (typeof Html5Qrcode === "undefined") {
        alert("خطأ: مكتبة المسح الضوئي غير محملة. تأكد من وجود ملف html5-qrcode.min.js");
        return;
    }

    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start(
        { facingMode: "environment" }, 
        { fps: 10, qrbox: 250 },
        (decodedText, decodedResult) => {
            // Success
            document.getElementById("barcode").value = decodedText;
            // Play sound
            let audio = new Audio("public/audio/beep.mp3");
            audio.play().catch(e => {});
            
            stopScanner();
        },
        (errorMessage) => {
            // ignore
        }
    ).catch(err => {
        console.error(err);
        alert("فشل تشغيل الكاميرا: " + err);
        stopScanner();
    });
}

function stopScanner() {
    document.getElementById("scannerModal").style.display = "none";
    if (html5QrCode) {
        html5QrCode.stop().then(() => {
            html5QrCode.clear(); 
        }).catch(err => {
            console.error("Failed to stop scanner", err);
        });
    }
}

// Auto-calculate purchase price per unit when pack price or quantity changes
document.getElementById('pack_purchase_price').addEventListener('input', calculateUnitPrice);
document.getElementById('pack_unit_quantity').addEventListener('input', calculateUnitPrice);

function calculateUnitPrice() {
    var packPrice = parseFloat(document.getElementById('pack_purchase_price').value) || 0;
    var packQty = parseFloat(document.getElementById('pack_unit_quantity').value) || 1;
    if (packPrice > 0 && packQty > 0) {
        var unitPrice = packPrice / packQty;
        document.getElementById('purchase_price').value = unitPrice.toFixed(2);
    }
}

</script>
