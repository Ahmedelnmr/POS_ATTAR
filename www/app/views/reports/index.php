<!-- Reports Dashboard -->
<div class="page-header">
    <div>
        <h1>📈 التقارير</h1>
        <p class="subtitle">تقارير المبيعات والمخزون</p>
    </div>
</div>

<!-- Report Type Tabs -->
<div class="card" style="padding:12px 16px;">
    <div class="btn-group">
        <button class="btn btn-primary" onclick="loadReport('daily')">📅 يومي</button>
        <button class="btn btn-outline" onclick="loadReport('weekly')">📆 أسبوعي</button>
        <button class="btn btn-outline" onclick="loadReport('monthly')">🗓️ شهري</button>
        <button class="btn btn-outline" onclick="loadReport('topProducts')">🏆 الأكثر مبيعاً</button>
        <button class="btn btn-outline" onclick="loadReport('leastProducts')">📉 الأقل مبيعاً</button>
        <button class="btn btn-outline" onclick="loadReport('lowStock')">⚠️ المخزون المنخفض</button>
        <button class="btn btn-outline" onclick="loadReport('suppliers')">🏭 المشتريات حسب المورد</button>
    </div>
</div>

<!-- Date Range Picker (for range reports) -->
<div class="card" id="dateRangeCard" style="padding:14px 20px;display:none;">
    <div class="d-flex align-center gap-2">
        <label class="text-muted">من:</label>
        <input type="date" id="reportDateFrom" class="form-control" style="width:160px;">
        <label class="text-muted">إلى:</label>
        <input type="date" id="reportDateTo" class="form-control" style="width:160px;">
        <button class="btn btn-primary btn-sm" onclick="loadRangeReport()">عرض</button>
    </div>
</div>

<!-- Report Content Area -->
<div class="card" id="reportContent">
    <div class="empty-state">
        <div class="icon">📈</div>
        <p>اختر نوع التقرير لعرض البيانات</p>
    </div>
</div>

<script>
let currentReport = '';

async function loadReport(type) {
    currentReport = type;
    const content = document.getElementById('reportContent');
    const dateCard = document.getElementById('dateRangeCard');
    content.innerHTML = '<div class="spinner"></div>';

    // Update active button
    document.querySelectorAll('.btn-group .btn').forEach(b => {
        b.className = b.className.replace('btn-primary', 'btn-outline');
    });
    event.target.className = event.target.className.replace('btn-outline', 'btn-primary');

    let url = '';
    switch (type) {
        case 'daily':
            dateCard.style.display = 'none';
            url = '?page=reports&action=daily&date=' + (new Date().toISOString().split('T')[0]);
            const dailyRes = await apiRequest(url);
            if (dailyRes.success) renderDailyReport(dailyRes.data);
            break;

        case 'weekly':
            dateCard.style.display = 'none';
            const weekFrom = new Date(Date.now() - 7 * 86400000).toISOString().split('T')[0];
            const weekTo = new Date().toISOString().split('T')[0];
            url = `?page=reports&action=range&from=${weekFrom}&to=${weekTo}`;
            const weeklyRes = await apiRequest(url);
            if (weeklyRes.success) renderRangeReport(weeklyRes.data, 'التقرير الأسبوعي');
            break;

        case 'monthly':
            dateCard.style.display = 'none';
            const monthFrom = new Date().toISOString().slice(0, 8) + '01';
            const monthTo = new Date().toISOString().split('T')[0];
            url = `?page=reports&action=range&from=${monthFrom}&to=${monthTo}`;
            const monthlyRes = await apiRequest(url);
            if (monthlyRes.success) renderRangeReport(monthlyRes.data, 'التقرير الشهري');
            break;

        case 'topProducts':
            dateCard.style.display = 'none';
            const topRes = await apiRequest('?page=reports&action=topProducts');
            if (topRes.success) renderProductReport(topRes.data, '🏆 الأكثر مبيعاً');
            break;

        case 'leastProducts':
            dateCard.style.display = 'none';
            const leastRes = await apiRequest('?page=reports&action=leastProducts');
            if (leastRes.success) renderLeastReport(leastRes.data);
            break;

        case 'lowStock':
            dateCard.style.display = 'none';
            const lowRes = await apiRequest('?page=reports&action=lowStock');
            if (lowRes.success) renderLowStockReport(lowRes.data);
            break;

        case 'suppliers':
            dateCard.style.display = 'none';
            const supRes = await apiRequest('?page=reports&action=supplierPurchases');
            if (supRes.success) renderSupplierReport(supRes.data);
            break;
    }
}

function renderDailyReport(data) {
    const content = document.getElementById('reportContent');
    const s = data.summary;
    content.innerHTML = `
        <div class="card-header"><h3>📅 تقرير يومي - ${data.date}</h3></div>
        <div class="stats-grid" style="margin-bottom:16px;">
            <div class="stat-card"><div class="stat-icon green">💵</div><div class="stat-info"><h4>${parseFloat(s.total).toFixed(2)}</h4><p>إجمالي المبيعات</p></div></div>
            <div class="stat-card"><div class="stat-icon blue">🧾</div><div class="stat-info"><h4>${s.count}</h4><p>عدد الفواتير</p></div></div>
            <div class="stat-card"><div class="stat-icon orange">🏷️</div><div class="stat-info"><h4>${parseFloat(s.discount || 0).toFixed(2)}</h4><p>إجمالي الخصومات</p></div></div>
        </div>
        ${data.sales.length ? `
        <div class="table-wrapper">
            <table>
                <thead><tr><th>#</th><th>الوقت</th><th>عدد الأصناف</th><th>الدفع</th><th>الإجمالي</th></tr></thead>
                <tbody>
                    ${data.sales.map(s => `
                        <tr>
                            <td>#${s.sale_number || s.id}</td>
                            <td class="fs-sm">${s.datetime}</td>
                            <td>${s.item_count}</td>
                            <td><span class="badge badge-success">${s.payment_method === 'cash' ? 'نقدي' : s.payment_method}</span></td>
                            <td class="fw-bold text-accent">${parseFloat(s.total).toFixed(2)}</td>
                        </tr>`).join('')}
                </tbody>
            </table>
        </div>` : '<div class="empty-state"><p>لا توجد مبيعات في هذا اليوم</p></div>'}
    `;
}

function renderRangeReport(data, title) {
    const content = document.getElementById('reportContent');
    const t = data.totals;
    content.innerHTML = `
        <div class="card-header"><h3>${title}</h3></div>
        <div class="stats-grid" style="margin-bottom:16px;">
            <div class="stat-card"><div class="stat-icon green">💵</div><div class="stat-info"><h4>${parseFloat(t.total).toFixed(2)}</h4><p>إجمالي المبيعات</p></div></div>
            <div class="stat-card"><div class="stat-icon blue">🧾</div><div class="stat-info"><h4>${t.count}</h4><p>عدد الفواتير</p></div></div>
        </div>
        ${data.daily.length ? `
        <div class="table-wrapper">
            <table>
                <thead><tr><th>التاريخ</th><th>عدد الفواتير</th><th>الإجمالي</th></tr></thead>
                <tbody>
                    ${data.daily.map(d => `
                        <tr>
                            <td class="fw-bold">${d.sale_date}</td>
                            <td>${d.count}</td>
                            <td class="fw-bold text-accent">${parseFloat(d.total).toFixed(2)}</td>
                        </tr>`).join('')}
                </tbody>
            </table>
        </div>` : '<div class="empty-state"><p>لا توجد بيانات</p></div>'}
    `;
}

function renderProductReport(data, title) {
    const content = document.getElementById('reportContent');
    content.innerHTML = `
        <div class="card-header"><h3>${title}</h3></div>
        ${data.length ? `
        <div class="table-wrapper">
            <table>
                <thead><tr><th>#</th><th>المنتج</th><th>الكمية المباعة</th><th>عدد الفواتير</th><th>إجمالي الإيرادات</th></tr></thead>
                <tbody>
                    ${data.map((p, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td class="fw-bold">${p.product_name}</td>
                            <td>${parseFloat(p.total_qty).toFixed(2)}</td>
                            <td>${p.sale_count}</td>
                            <td class="fw-bold text-accent">${parseFloat(p.total_revenue).toFixed(2)}</td>
                        </tr>`).join('')}
                </tbody>
            </table>
        </div>` : '<div class="empty-state"><p>لا توجد بيانات</p></div>'}
    `;
}

function renderLeastReport(data) {
    const content = document.getElementById('reportContent');
    content.innerHTML = `
        <div class="card-header"><h3>📉 الأقل مبيعاً</h3></div>
        ${data.length ? `
        <div class="table-wrapper">
            <table>
                <thead><tr><th>#</th><th>المنتج</th><th>المخزون</th><th>إجمالي المبيعات</th></tr></thead>
                <tbody>
                    ${data.map((p, i) => `
                        <tr>
                            <td>${i + 1}</td>
                            <td class="fw-bold">${p.name}</td>
                            <td>${parseFloat(p.stock_quantity).toFixed(2)}</td>
                            <td class="text-warning">${parseFloat(p.total_sold).toFixed(2)}</td>
                        </tr>`).join('')}
                </tbody>
            </table>
        </div>` : '<div class="empty-state"><p>لا توجد بيانات</p></div>'}
    `;
}

function renderLowStockReport(data) {
    const content = document.getElementById('reportContent');
    content.innerHTML = `
        <div class="card-header"><h3>⚠️ المخزون المنخفض (${data.length})</h3></div>
        ${data.length ? `
        <div class="table-wrapper">
            <table>
                <thead><tr><th>المنتج</th><th>النوع</th><th>المخزون</th><th>الحد الأدنى</th><th>القسم</th></tr></thead>
                <tbody>
                    ${data.map(p => `
                        <tr>
                            <td class="fw-bold">${p.name}</td>
                            <td><span class="badge badge-info">${p.type}</span></td>
                            <td><span class="badge badge-danger">${p.stock_quantity}</span></td>
                            <td>${p.min_stock}</td>
                            <td class="text-muted">${p.category || '—'}</td>
                        </tr>`).join('')}
                </tbody>
            </table>
        </div>` : '<div class="empty-state"><p>✅ جميع المنتجات في مستوى آمن</p></div>'}
    `;
}

function renderSupplierReport(data) {
    const content = document.getElementById('reportContent');
    content.innerHTML = `
        <div class="card-header"><h3>🏭 المشتريات حسب المورد</h3></div>
        ${data.length ? `
        <div class="table-wrapper">
            <table>
                <thead><tr><th>المورد</th><th>عدد الفواتير</th><th>إجمالي المشتريات</th></tr></thead>
                <tbody>
                    ${data.map(s => `
                        <tr>
                            <td class="fw-bold">${s.name}</td>
                            <td>${s.invoice_count}</td>
                            <td class="fw-bold text-accent">${parseFloat(s.total).toFixed(2)}</td>
                        </tr>`).join('')}
                </tbody>
            </table>
        </div>` : '<div class="empty-state"><p>لا توجد بيانات</p></div>'}
    `;
}

// Load daily report by default
document.addEventListener('DOMContentLoaded', function() {
    loadReport('daily');
});
</script>
