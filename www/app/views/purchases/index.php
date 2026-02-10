<!-- Purchases List View -->
<div class="page-header">
    <div>
        <h1>🧾 فواتير الشراء</h1>
        <p class="subtitle">إجمالي الفواتير: <?= count($invoices) ?></p>
    </div>
    <a href="?page=purchases&action=create" class="btn btn-primary">➕ فاتورة شراء جديدة</a>
</div>

<!-- Filters -->
<div class="card" style="padding:14px 20px;">
    <form class="d-flex align-center gap-2" method="GET">
        <input type="hidden" name="page" value="purchases">
        <select name="supplier_id" class="form-control" style="width:200px;">
            <option value="">كل الموردين</option>
            <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s['id'] ?>" <?= ($_GET['supplier_id'] ?? '') == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" class="form-control" style="width:160px;" value="<?= $_GET['date_from'] ?? '' ?>" placeholder="من">
        <input type="date" name="date_to" class="form-control" style="width:160px;" value="<?= $_GET['date_to'] ?? '' ?>" placeholder="إلى">
        <button type="submit" class="btn btn-outline">بحث</button>
    </form>
</div>

<div class="card">
    <?php if (!empty($invoices)): ?>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>المورد</th>
                    <th>رقم الفاتورة</th>
                    <th>التاريخ</th>
                    <th>الإجمالي</th>
                    <th>ملاحظات</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invoices as $inv): ?>
                <tr>
                    <td class="text-muted"><?= $inv['id'] ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($inv['supplier_name']) ?></td>
                    <td><?= $inv['invoice_number'] ?: '—' ?></td>
                    <td><?= $inv['date'] ?></td>
                    <td class="fw-bold text-accent"><?= number_format($inv['total'], 2) ?></td>
                    <td class="text-muted fs-sm"><?= $inv['notes'] ? htmlspecialchars(mb_substr($inv['notes'], 0, 40)) : '—' ?></td>
                    <td>
                        <a href="?page=purchases&action=view&id=<?= $inv['id'] ?>" class="btn btn-sm btn-outline">👁️ عرض</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="icon">🧾</div>
        <p>لا توجد فواتير شراء</p>
    </div>
    <?php endif; ?>
</div>
