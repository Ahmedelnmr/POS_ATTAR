<!-- Purchase Invoice Form -->
<div class="page-header">
    <div>
        <h1>➕ فاتورة شراء جديدة</h1>
    </div>
    <a href="?page=purchases" class="btn btn-outline">← العودة</a>
</div>

<div class="card">
    <div class="form-row">
        <div class="form-group">
            <label>المورد *</label>
            <select id="supplierId" class="form-control" required>
                <option value="">اختر المورد</option>
                <?php foreach ($suppliers as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>رقم الفاتورة</label>
            <input type="text" id="invoiceNumber" class="form-control" placeholder="اختياري">
        </div>
        <div class="form-group">
            <label>التاريخ</label>
            <input type="date" id="invoiceDate" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
    </div>
</div>

<!-- Add Items -->
<div class="card">
    <div class="card-header">
        <h3>📦 أصناف الفاتورة</h3>
    </div>

    <div class="form-row" style="margin-bottom:16px;align-items:flex-end;">
        <div class="form-group" style="flex:2;">
            <label>المنتج</label>
            <select id="itemProduct" class="form-control">
                <option value="">اختر المنتج</option>
                <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= $p['purchase_price'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['barcode'] ?: 'بدون باركود' ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>الكمية</label>
            <input type="number" id="itemQty" class="form-control" value="1" min="0.01" step="0.01">
        </div>
        <div class="form-group">
            <label>سعر الشراء</label>
            <input type="number" id="itemPrice" class="form-control" step="0.01" min="0" placeholder="0.00">
        </div>
        <div class="form-group">
            <button class="btn btn-primary" onclick="addPurchaseItem()">➕ إضافة</button>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المنتج</th>
                    <th>الكمية</th>
                    <th>سعر الشراء</th>
                    <th>المجموع</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="purchaseItems">
                <tr id="emptyItems">
                    <td colspan="6" class="text-center text-muted" style="padding:30px;">لم يتم إضافة أصناف</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="fw-bold text-left">الإجمالي:</td>
                    <td class="fw-bold text-accent fs-lg" id="purchaseTotal">0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="form-group">
    <label>ملاحظات</label>
    <textarea id="invoiceNotes" class="form-control" rows="2"></textarea>
</div>

<button class="btn btn-success btn-lg" onclick="savePurchaseInvoice()">💾 حفظ فاتورة الشراء</button>

<script>
let purchaseItemsList = [];

// Auto-fill price when product selected
document.getElementById('itemProduct').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    if (option.dataset.price) {
        document.getElementById('itemPrice').value = option.dataset.price;
    }
});

function addPurchaseItem() {
    const select = document.getElementById('itemProduct');
    const productId = select.value;
    const option = select.options[select.selectedIndex];
    
    if (!productId) { showToast('اختر منتج', 'error'); return; }

    const qty = parseFloat(document.getElementById('itemQty').value);
    const price = parseFloat(document.getElementById('itemPrice').value);

    if (!qty || qty <= 0) { showToast('أدخل كمية صحيحة', 'error'); return; }
    if (!price || price < 0) { showToast('أدخل سعر صحيح', 'error'); return; }

    purchaseItemsList.push({
        product_id: parseInt(productId),
        product_name: option.dataset.name,
        quantity: qty,
        purchase_price: price,
        subtotal: qty * price
    });

    renderPurchaseItems();
    select.value = '';
    document.getElementById('itemQty').value = '1';
    document.getElementById('itemPrice').value = '';
}

function removePurchaseItem(index) {
    purchaseItemsList.splice(index, 1);
    renderPurchaseItems();
}

function renderPurchaseItems() {
    const tbody = document.getElementById('purchaseItems');
    
    if (purchaseItemsList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding:30px;">لم يتم إضافة أصناف</td></tr>';
        document.getElementById('purchaseTotal').textContent = '0.00';
        return;
    }

    let total = 0;
    tbody.innerHTML = purchaseItemsList.map((item, i) => {
        total += item.subtotal;
        return `
            <tr>
                <td>${i + 1}</td>
                <td class="fw-bold">${item.product_name}</td>
                <td>${item.quantity}</td>
                <td>${item.purchase_price.toFixed(2)}</td>
                <td class="fw-bold">${item.subtotal.toFixed(2)}</td>
                <td><button class="btn btn-sm btn-ghost text-danger" onclick="removePurchaseItem(${i})">✕</button></td>
            </tr>
        `;
    }).join('');

    document.getElementById('purchaseTotal').textContent = total.toFixed(2);
}

async function savePurchaseInvoice() {
    const supplierId = document.getElementById('supplierId').value;
    if (!supplierId) { showToast('اختر المورد', 'error'); return; }
    if (purchaseItemsList.length === 0) { showToast('أضف أصناف للفاتورة', 'error'); return; }

    const payload = {
        supplier_id: parseInt(supplierId),
        invoice_number: document.getElementById('invoiceNumber').value,
        date: document.getElementById('invoiceDate').value,
        notes: document.getElementById('invoiceNotes').value,
        items: purchaseItemsList
    };

    const res = await apiRequest('?page=purchases&action=save', {
        method: 'POST',
        body: payload
    });

    if (res.success) {
        showToast('✅ تم حفظ فاتورة الشراء وتحديث المخزون');
        setTimeout(() => window.location.href = '?page=purchases', 1000);
    } else {
        showToast(res.message || 'خطأ', 'error');
    }
}
</script>
