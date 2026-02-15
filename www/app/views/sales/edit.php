<?php
/**
 * Edit Sale View
 * Allows modifying items, quantities, and prices dynamically.
 */
?>
<style>
/* Hide Browser Default Spinners */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>
<div class="page-header">
    <div>
        <h1>📝 تعديل فاتورة #<?= $sale['sale_number'] ?></h1>
        <p class="subtitle"><?= $sale['datetime'] ?></p>
    </div>
    <div>
        <a href="?page=sales" class="btn btn-outline">إلغاء وعودة</a>
    </div>
</div>

<div class="card" style="padding:20px;">

    <?php if (!empty($_GET['error'])): ?>
    <div class="alert alert-danger" style="background-color: #ffebee; color: #c62828; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
        ❌ <?= htmlspecialchars($_GET['error']) ?>
    </div>
    <?php endif; ?>

    <!-- Product Search -->
    <div class="form-group mb-3">
        <label>إضافة منتج</label>
        <div style="display:flex; gap:10px;">
            <input type="text" id="productSearch" list="productList" class="form-control" placeholder="ابحث عن منتج..." style="flex:1;">
            <button type="button" class="btn btn-primary" onclick="addProductRow()">➕ إضافة</button>
        </div>
        <datalist id="productList">
            <?php foreach ($products as $p): ?>
            <option value="<?= $p['name'] ?>" 
                    data-id="<?= $p['id'] ?>" 
                    data-price-unit="<?= $p['sale_price_unit'] ?>"
                    data-price-pack="<?= $p['pack_sale_price'] ?>"
                    data-pack-qty="<?= $p['pack_unit_quantity'] ?>"
                    data-type="<?= $p['type'] ?>">
            </option>
            <?php endforeach; ?>
        </datalist>
    </div>

    <!-- Explicit form action to route correctly -->
    <form action="index.php?page=sales&action=update" method="POST" id="editForm" onsubmit="return false;">
        <input type="hidden" name="sale_id" value="<?= $sale['id'] ?>">
        <input type="hidden" name="items_json" id="itemsJson">

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>المنتج</th>
                        <th width="120">النوع</th>
                        <th width="120">الكمية</th>
                        <th width="120">السعر</th>
                        <th width="120">الإجمالي</th>
                        <th width="50"></th>
                    </tr>
                </thead>
                <tbody id="itemsTableBody">
                    <!-- Items will be injected here via JS -->
                </tbody>
            </table>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:20px; margin-top:20px; text-align:right;">
            <div>
                <label>المجموع الفرعي:</label>
                <div class="fs-xl fw-bold" id="subtotalDisplay">0.00</div>
            </div>
            <div>
                <label>الخصم:</label>
                <input type="number" name="discount" id="discountInput" class="form-control" style="width:100px;" value="<?= $sale['discount'] ?>" onchange="updateTotals()">
            </div>
            <div>
                <label>الإجمالي النهائي:</label>
                <div class="fs-xl fw-bold text-accent" id="totalDisplay">0.00</div>
                <input type="hidden" name="total" id="totalInput">
            </div>
        </div>

        <div class="form-group mt-3">
            <label>سبب التعديل</label>
            <input type="text" name="reason" class="form-control" placeholder="مثال: خطأ في الكمية، استبدال منتج..." required>
        </div>

        <div class="mt-4 text-center">
            <!-- Use type=button and onclick to prevent default submission issues -->
            <button type="button" class="btn btn-lg btn-success" onclick="prepareSubmit()">💾 حفظ التعديلات</button>
        </div>
    </form>
</div>

<script>
// Initial Items from PHP
var items = <?= json_encode($sale['items']) ?>;
var productsMap = {};

// Build Products Map for easier access
<?php foreach ($products as $p): ?>
productsMap["<?= $p['name'] ?>"] = {
    id: "<?= $p['id'] ?>",
    price_unit: <?= $p['sale_price_unit'] ?>,
    price_pack: <?= $p['pack_sale_price'] ?? 0 ?>,
    pack_qty: <?= $p['pack_unit_quantity'] ?? 1 ?>,
    type: "<?= $p['type'] ?>"
};
<?php endforeach; ?>

function renderItems() {
    var tbody = document.getElementById('itemsTableBody');
    tbody.innerHTML = '';
    var subtotal = 0;

    items.forEach(function(item, index) {
        var rowTotal = item.quantity * item.price; // Use stored price
        item.subtotal = rowTotal; // IMPORTANT: Update subtotal in object
        subtotal += rowTotal;

        var tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${item.product_name}</td>
            <td>
                <select class="form-control form-control-sm" onchange="updateItemUnit(${index}, this.value)">
                    <option value="قطعة" ${item.unit_type === 'قطعة' ? 'selected' : ''}>قطعة</option>
                    <option value="pack" ${item.unit_type !== 'قطعة' ? 'selected' : ''}>عبوة/طرد</option>
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm no-spinner" value="${item.quantity}" min="0.1" step="0.1" onchange="updateItemQty(${index}, this.value)">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm no-spinner" value="${item.price}" min="0" step="0.01" onchange="updateItemPrice(${index}, this.value)">
            </td>
            <td class="fw-bold">${rowTotal.toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">×</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
    updateTotals();
}

function updateTotals() {
    var subtotal = 0;
    items.forEach(function(i) { subtotal += (i.quantity * i.price); });
    
    var discount = parseFloat(document.getElementById('discountInput').value) || 0;
    var total = subtotal - discount;

    document.getElementById('totalDisplay').textContent = total.toFixed(2);
    document.getElementById('totalInput').value = total;
}

function updateItemQty(index, val) {
    items[index].quantity = parseFloat(val) || 0;
    // Recalculate subtotal immediately
    items[index].subtotal = items[index].quantity * items[index].price;
    renderItems();
}

function updateItemPrice(index, val) {
    items[index].price = parseFloat(val) || 0;
    // Recalculate subtotal immediately
    items[index].subtotal = items[index].quantity * items[index].price;
    renderItems();
}

function updateItemUnit(index, unit) {
    items[index].unit_type = unit;
    // Auto-update price based on unit if product exists in map
    var p = productsMap[items[index].product_name];
    if (p) {
        if (unit === 'قطعة') {
            items[index].price = p.price_unit;
            items[index].sale_mode = 'unit';
        } else {
            items[index].price = p.price_pack;
            items[index].sale_mode = 'pack';
        }
    }
    // Recalculate subtotal immediately
    items[index].subtotal = items[index].quantity * items[index].price;
    renderItems();
}

function removeItem(index) {
    if(confirm('حذف الصنف؟')) {
        items.splice(index, 1);
        renderItems();
    }
}

function addProductRow() {
    var input = document.getElementById('productSearch');
    var val = input.value;
    var p = productsMap[val];

    if (p) {
        items.push({
            product_id: p.id,
            product_name: val,
            quantity: 1,
            unit_type: 'قطعة',
            price: p.price_unit,
            sale_mode: 'unit',
            subtotal: p.price_unit
        });
        input.value = '';
        renderItems();
    } else {
        alert('المنتج غير موجود');
    }
}

function prepareSubmit() {
    if (items.length === 0) {
        alert('لا يمكن حفظ فاتورة فارغة');
        return;
    }
    document.getElementById('itemsJson').value = JSON.stringify(items);
    document.getElementById('editForm').submit();
}

// Initial Render
renderItems();
</script>
