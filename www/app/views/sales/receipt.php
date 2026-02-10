<!-- Sale Receipt View -->
<div class="page-header">
    <div>
        <h1>🧾 إيصال بيع #<?= $sale['sale_number'] ?? $sale['id'] ?></h1>
    </div>
    <div class="btn-group">
        <button class="btn btn-primary" onclick="window.print()">🖨️ طباعة</button>
        <a href="?page=sales" class="btn btn-outline">← العودة</a>
    </div>
</div>

<div style="max-width:400px;margin:0 auto;">
    <div class="receipt" id="printableReceipt">
        <h2>إيصال بيع</h2>
        <div style="text-align:center;font-size:11px;color:#666;">
            فاتورة رقم: <?= $sale['sale_number'] ?? $sale['id'] ?><br>
            <?= $sale['datetime'] ?>
        </div>
        <div class="line"></div>
        <table>
            <thead>
                <tr>
                    <th style="text-align:right">المنتج</th>
                    <th>الكمية</th>
                    <th>السعر</th>
                    <th>المجموع</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sale['items'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td style="text-align:center"><?= $item['quantity'] ?></td>
                    <td style="text-align:center"><?= number_format($item['price'], 2) ?></td>
                    <td style="text-align:left"><?= number_format($item['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="line"></div>
        <?php if ($sale['discount'] > 0): ?>
        <div style="display:flex;justify-content:space-between;">
            <span>المجموع الفرعي:</span>
            <span><?= number_format($sale['subtotal'], 2) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;">
            <span>الخصم:</span>
            <span><?= number_format($sale['discount'], 2) ?></span>
        </div>
        <?php endif; ?>
        <div class="total-line">
            الإجمالي: <?= number_format($sale['total'], 2) ?>
        </div>
        <div class="line"></div>
        <div style="text-align:center;font-size:10px;color:#888;margin-top:8px;">
            شكراً لتسوقكم 🙏
        </div>
    </div>
</div>
