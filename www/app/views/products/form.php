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
                </div>
            </div>
            <div class="form-group">
                <label>كود PLU</label>
                <input type="text" name="plu_code" class="form-control" value="<?= htmlspecialchars($product['plu_code'] ?? '') ?>" placeholder="كود سريع">
            </div>
        </div>

        <!-- Hidden Type Field (Default to 'unit') -->
        <input type="hidden" name="type" value="unit">
        <!-- Removed Category Field -->

        <fieldset style="border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
            <legend style="font-size: 16px; font-weight: bold; padding: 0 10px;">📦 بيانات التسعير والتعبئة</legend>
            
            <!-- Weight Mode Toggle -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label class="custom-control custom-checkbox">
                    <input type="checkbox" id="is_weight" class="custom-control-input" onchange="toggleWeightMode()">
                    <span class="custom-control-label" style="font-weight:bold; color:#2c3e50;">⚖️ هذا المنتج يباع بالوزن (شوال / كيلو)</span>
                </label>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label id="lbl_pack_type">نوع التعبئة</label>
                    <select name="pack_type" id="pack_type" class="form-control">
                        <option value="">-- بدون تعبئة --</option>
                        <?php 
                        $packTypes = ['كرتونة', 'لفة', 'عبوة', 'حزمة', 'صندوق', 'علبة', 'دستة', 'باكيت', 'شنطة', 'كيس', 'شوال', 'جت'];
                        foreach ($packTypes as $type): 
                        ?>
                        <option value="<?= $type ?>" <?= ($product['pack_type'] ?? '') === $type ? 'selected' : '' ?>><?= $type ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label id="lbl_pack_qty">عدد القطع في الوحدة</label>
                    <input type="number" name="pack_unit_quantity" id="pack_unit_quantity" class="form-control" min="0.001" step="0.001" value="<?= $product['pack_unit_quantity'] ?? '' ?>" placeholder="مثال: 50">
                    <small class="text-muted" id="help_pack_qty">كم قطعة في كل وحدة</small>
                </div>
            </div>

            <hr style="margin: 15px 0; border-top: 1px dashed #eee;">

            <!-- Purchase Prices Row -->
            <div class="form-row">
                <div class="form-group">
                    <label id="lbl_pack_buy">سعر شراء الوحدة الكاملة (الجملة)</label>
                    <input type="number" name="pack_purchase_price" id="pack_purchase_price" class="form-control" step="0.01" min="0" value="<?= $product['pack_purchase_price'] ?? '' ?>" placeholder="مثال: 1200">
                    <small class="text-muted">أدخل السعر هنا ليحسب سعر الوحدة الفرعية</small>
                </div>
                <div class="form-group">
                    <label id="lbl_unit_buy">سعر شراء القطعة الواحدة *</label>
                    <input type="number" name="purchase_price" id="purchase_price" class="form-control" step="0.001" min="0" required value="<?= $product['purchase_price'] ?? '0' ?>" readonly style="background-color: #f0f0f0;">
                    <small class="text-muted">محسوب تلقائياً</small>
                </div>
            </div>

            <!-- Sale Prices Row -->
            <div class="form-row">
                <div class="form-group">
                    <label id="lbl_pack_sell">سعر بيع الوحدة الكاملة (الجملة)</label>
                    <input type="number" name="pack_sale_price" id="pack_sale_price" class="form-control" step="0.01" min="0" value="<?= $product['pack_sale_price'] ?? '' ?>" placeholder="مثال: 1500">
                    <small class="text-muted">سعر بيع الشوال/الكرتونة كاملة</small>
                </div>
                <div class="form-group">
                    <label id="lbl_unit_sell">سعر بيع القطعة الواحدة *</label>
                    <input type="number" name="sale_price_unit" class="form-control" step="0.001" min="0" required value="<?= $product['sale_price_unit'] ?? '0' ?>">
                    <small class="text-muted">سعر بيع المستهلك</small>
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


<script>
// Global Scanner Listener
let scanBuffer = '';
let lastKeyTime = 0;
const SCAN_TIMING_THRESHOLD = 100; // ms

document.addEventListener('keydown', function(e) {
    // If user is typing in an input, allow it (unless it's the body)
    // BUT we want to capture scanner input even if focus is elsewhere, 
    // provided it looks like a scan (fast input).
    // However, if user is typing in "Name" field, we shouldn't hijack it.
    // The requirement is "don't press anything", implies focus might be anywhere or nowhere.
    
    // Strategy: 
    // 1. If focus is on "Barcode" field, let standard input handle it (scanner acts as keyboard).
    // 2. If focus is on another field (Name, Price), we probably shouldn't interfere OR 
    //    we should check if the input is very fast.
    //    If I scan while focused on "Name", the scanner types the barcode into "Name".
    //    The user explicitly said "I don't want to press scan, just scan automatically".
    //    Usually this means "I open the page, scan, and it fills the barcode".
    //    If the page loads, where is the focus?
    //    If I autofocus "Name" (line 19), then scanner will fill Name.
    //    I should probably CHANGE autofocus to Barcode? Or detect scan and move it?
    
    //    If I detect a scan (fast input), I should move the buffer to the Barcode field.
    
    const target = e.target;
    // If scanning while focused on an input, the input receives keys.
    // We can intercept 'Enter' and check if the content looks like a barcode?
    // Or we use the buffer logic generally.
    
    const currentTime = Date.now();
    if (currentTime - lastKeyTime > SCAN_TIMING_THRESHOLD) {
        scanBuffer = '';
    }
    lastKeyTime = currentTime;

    if (e.ctrlKey || e.altKey || e.metaKey) return;

    if (e.key === 'Enter') {
        if (scanBuffer.length > 0) {
            // It's a scan!
            e.preventDefault(); // Stop form submission
            console.log('Scanner detected:', scanBuffer);
            
            var barcodeField = document.getElementById('barcode');
            if (barcodeField) {
                barcodeField.value = scanBuffer;
                // Optional: Flash the field to show update
                barcodeField.style.backgroundColor = '#dcfce7';
                setTimeout(() => barcodeField.style.backgroundColor = '', 500);
            }
            scanBuffer = '';
        }
    } else if (e.key.length === 1) {
        scanBuffer += e.key;
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Check if editing a weight product
    var type = document.querySelector('input[name="type"]').value;
    if (type === 'weight') {
        document.getElementById('is_weight').checked = true;
    }
    toggleWeightMode();
    
    // Attach calculation listeners
    document.getElementById('pack_purchase_price').addEventListener('input', calculateUnitPrice);
    document.getElementById('pack_unit_quantity').addEventListener('input', calculateUnitPrice);
});

function toggleWeightMode() {
    var isWeight = document.getElementById('is_weight').checked;
    var typeInput = document.querySelector('input[name="type"]');
    
    // Update Hidden Type
    typeInput.value = isWeight ? 'weight' : 'unit';

    // Labels
    if (isWeight) {
        // Weight Mode (Sack -> Kg)
        document.getElementById('lbl_pack_type').textContent = 'نوع العبوة (شوال/كيس)';
        document.getElementById('lbl_pack_qty').textContent = 'وزن العبوة (كجم)';
        document.getElementById('help_pack_qty').textContent = 'مثال: 50.5 (وزن الشوال)';
        
        document.getElementById('lbl_pack_buy').textContent = 'سعر شراء العبوة كاملة';
        document.getElementById('lbl_unit_buy').textContent = 'سعر شراء الكيلو *';
        
        document.getElementById('lbl_pack_sell').textContent = 'سعر بيع العبوة كاملة';
        document.getElementById('lbl_unit_sell').textContent = 'سعر بيع الكيلو *';
        
        // Steps
        document.getElementById('pack_unit_quantity').setAttribute('step', '0.001');
        document.getElementById('pack_unit_quantity').setAttribute('min', '0.001');
    } else {
        // Unit Mode (Pack -> Piece)
        document.getElementById('lbl_pack_type').textContent = 'نوع التعبئة';
        document.getElementById('lbl_pack_qty').textContent = 'عدد القطع في الوحدة';
        document.getElementById('help_pack_qty').textContent = 'كم قطعة في كل وحدة (كرتونة/لفة)';
        
        document.getElementById('lbl_pack_buy').textContent = 'سعر شراء الوحدة الكاملة (الجملة)';
        document.getElementById('lbl_unit_buy').textContent = 'سعر شراء القطعة الواحدة *';
        
        document.getElementById('lbl_pack_sell').textContent = 'سعر بيع الوحدة الكاملة (الجملة)';
        document.getElementById('lbl_unit_sell').textContent = 'سعر بيع القطعة الواحدة *';
        
        // Steps
        document.getElementById('pack_unit_quantity').setAttribute('step', '1');
        document.getElementById('pack_unit_quantity').setAttribute('min', '1');
    }
}

function calculateUnitPrice() {
    var packPrice = parseFloat(document.getElementById('pack_purchase_price').value) || 0;
    var packQty = parseFloat(document.getElementById('pack_unit_quantity').value) || 1;
    if (packPrice > 0 && packQty > 0) {
        var unitPrice = packPrice / packQty;
        document.getElementById('purchase_price').value = unitPrice.toFixed(3);
    }
}
</script>
