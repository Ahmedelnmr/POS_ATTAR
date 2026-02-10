<!-- Inventory View -->
<div class="page-header">
    <div>
        <h1>📋 إدارة المخزون</h1>
    </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">📦</div>
        <div class="stat-info">
            <h4><?= $summary['total_products'] ?? 0 ?></h4>
            <p>إجمالي المنتجات</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">💰</div>
        <div class="stat-info">
            <h4><?= number_format($summary['total_value'] ?? 0, 2) ?></h4>
            <p>قيمة المخزون</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">⚠️</div>
        <div class="stat-info">
            <h4><?= $summary['low_stock_count'] ?? 0 ?></h4>
            <p>منتجات منخفضة</p>
        </div>
    </div>
</div>

<?php if (!empty($lowStock)): ?>
<!-- Low Stock Alerts -->
<div class="card">
    <div class="card-header">
        <h3>⚠️ تنبيهات المخزون المنخفض (<?= count($lowStock) ?>)</h3>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>النوع</th>
                    <th>المخزون الحالي</th>
                    <th>الحد الأدنى</th>
                    <th>الحالة</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowStock as $p): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($p['name']) ?></td>
                    <td><span class="badge badge-info"><?= $p['type'] ?></span></td>
                    <td><span class="badge badge-danger"><?= $p['stock_quantity'] ?></span></td>
                    <td><?= $p['min_stock'] ?></td>
                    <td>
                        <?php if ($p['stock_quantity'] <= 0): ?>
                            <span class="badge badge-danger">نفد</span>
                        <?php else: ?>
                            <span class="badge badge-warning">منخفض</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- All Products Stock -->
<div class="card">
    <div class="card-header">
        <h3>📦 مخزون المنتجات</h3>
    </div>
    
    <div style="margin-bottom:12px;">
        <form class="d-flex align-center gap-1" method="GET">
            <input type="hidden" name="page" value="inventory">
            <input type="text" name="search" class="form-control" style="max-width:300px;" placeholder="🔍 بحث..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit" class="btn btn-outline btn-sm">بحث</button>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>المنتج</th>
                    <th>النوع</th>
                    <th>المخزون</th>
                    <th>الحد الأدنى</th>
                    <th>القيمة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($p['name']) ?></td>
                    <td><span class="badge badge-info"><?= $p['type'] ?></span></td>
                    <td>
                        <?php if ($p['min_stock'] > 0 && $p['stock_quantity'] <= $p['min_stock']): ?>
                            <span class="badge badge-danger"><?= $p['stock_quantity'] ?></span>
                        <?php else: ?>
                            <span class="badge badge-success"><?= $p['stock_quantity'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['min_stock'] ?></td>
                    <td class="text-muted"><?= number_format($p['stock_quantity'] * $p['purchase_price'], 2) ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline" onclick="openAdjustModal(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>', <?= $p['stock_quantity'] ?>)">📝 تعديل</button>
                        <button class="btn btn-sm btn-ghost" onclick="viewMovements(<?= $p['id'] ?>)">📊 الحركات</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal-overlay" id="adjustModal">
    <div class="modal">
        <div class="modal-header">
            <h3>📝 تعديل المخزون</h3>
            <button class="modal-close" onclick="closeModal('adjustModal')">&times;</button>
        </div>
        <div class="modal-body">
            <p class="fw-bold mb-2" id="adjustProductName"></p>
            <div class="form-group">
                <label>الكمية الحالية</label>
                <input type="text" class="form-control" id="adjustCurrentQty" readonly>
            </div>
            <div class="form-group">
                <label>الكمية الجديدة</label>
                <input type="number" class="form-control" id="adjustNewQty" step="0.01">
            </div>
            <div class="form-group">
                <label>السبب</label>
                <input type="text" class="form-control" id="adjustReason" placeholder="سبب التعديل">
            </div>
            <input type="hidden" id="adjustProductId">
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('adjustModal')">إلغاء</button>
            <button class="btn btn-primary" onclick="saveAdjustment()">💾 حفظ</button>
        </div>
    </div>
</div>

<!-- Movements Modal -->
<div class="modal-overlay" id="movementsModal">
    <div class="modal" style="max-width:700px;">
        <div class="modal-header">
            <h3>📊 حركات المخزون</h3>
            <button class="modal-close" onclick="closeModal('movementsModal')">&times;</button>
        </div>
        <div class="modal-body" id="movementsContent">
            <div class="spinner"></div>
        </div>
    </div>
</div>

<script>
function openAdjustModal(id, name, currentQty) {
    document.getElementById('adjustProductId').value = id;
    document.getElementById('adjustProductName').textContent = name;
    document.getElementById('adjustCurrentQty').value = currentQty;
    document.getElementById('adjustNewQty').value = currentQty;
    openModal('adjustModal');
}

async function saveAdjustment() {
    const productId = document.getElementById('adjustProductId').value;
    const newQty = parseFloat(document.getElementById('adjustNewQty').value);
    const reason = document.getElementById('adjustReason').value;

    const res = await apiRequest('?page=inventory&action=adjust', {
        method: 'POST',
        body: { product_id: productId, new_quantity: newQty, reason: reason }
    });

    if (res.success) {
        showToast('✅ تم تعديل المخزون');
        closeModal('adjustModal');
        setTimeout(() => location.reload(), 500);
    } else {
        showToast(res.message || 'خطأ', 'error');
    }
}

async function viewMovements(productId) {
    openModal('movementsModal');
    const content = document.getElementById('movementsContent');
    content.innerHTML = '<div class="spinner"></div>';

    const res = await apiRequest(`?page=inventory&action=movements&product_id=${productId}`);
    if (res.success && res.data && res.data.length > 0) {
        const typeLabels = { purchase: '🟢 شراء', sale: '🔴 بيع', adjustment: '🟡 تعديل' };
        content.innerHTML = `
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>التاريخ</th><th>النوع</th><th>الكمية</th><th>ملاحظات</th></tr></thead>
                    <tbody>
                        ${res.data.map(m => `
                            <tr>
                                <td class="fs-sm">${m.created_at}</td>
                                <td>${typeLabels[m.type] || m.type}</td>
                                <td class="${m.quantity >= 0 ? 'text-success' : 'text-danger'} fw-bold">${m.quantity >= 0 ? '+' : ''}${m.quantity}</td>
                                <td class="text-muted fs-sm">${m.notes || '—'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>`;
    } else {
        content.innerHTML = '<div class="empty-state"><p>لا توجد حركات</p></div>';
    }
}
</script>
