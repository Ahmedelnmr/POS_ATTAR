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
                <input type="text" name="barcode" class="form-control" value="<?= htmlspecialchars($product['barcode'] ?? '') ?>" placeholder="اختياري">
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
                <label>سعر الشراء *</label>
                <input type="number" name="purchase_price" class="form-control" step="0.01" min="0" required value="<?= $product['purchase_price'] ?? '0' ?>">
            </div>
            <div class="form-group">
                <label>سعر البيع (وحدة) *</label>
                <input type="number" name="sale_price_unit" class="form-control" step="0.01" min="0" required value="<?= $product['sale_price_unit'] ?? '0' ?>">
            </div>
            <div class="form-group">
                <label>سعر البيع (عبوة)</label>
                <input type="number" name="sale_price_pack" class="form-control" step="0.01" min="0" value="<?= $product['sale_price_pack'] ?? '' ?>" placeholder="اختياري">
            </div>
            <div class="form-group">
                <label>كمية العبوة</label>
                <input type="number" name="pack_quantity" class="form-control" min="1" value="<?= $product['pack_quantity'] ?? '' ?>" placeholder="عدد الوحدات">
            </div>
        </div>

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
