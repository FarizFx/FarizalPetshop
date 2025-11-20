<?php
include "./function/connection.php";
include "./function/currency.php";
include "./function/language.php";

// Load currency setting
$settings = [];
$stmt = $connection->prepare("SELECT currency FROM settings WHERE id=1");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $settings = $result->fetch_assoc();
    }
    $stmt->close();
}
$currency = $settings['currency'] ?? 'IDR';

// Handle filter dates
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month

// Validate dates
if (strtotime($start_date) > strtotime($end_date)) {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
}

// Sales Report Data menggunakan harga_modal tersimpan
$sales_query = "
    SELECT
        COUNT(DISTINCT p.id_penjualan) as total_transactions,
        SUM(p.total_harga) as total_revenue,
        AVG(p.total_harga) as avg_transaction,
        COUNT(dp.id_detail) as total_items_sold,
        COALESCE(SUM(dp.qty * dp.harga_modal), 0) as total_cost,
        (SUM(p.total_harga) - COALESCE(SUM(dp.qty * dp.harga_modal), 0)) as total_profit
    FROM penjualan p
    LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
    WHERE DATE(p.tanggal) BETWEEN ? AND ?
";

$sales_stmt = $connection->prepare($sales_query);
$sales_stmt->bind_param("ss", $start_date, $end_date);
$sales_stmt->execute();
$sales_data = $sales_stmt->get_result()->fetch_assoc();
$sales_stmt->close();

// Category Report Data
$category_query = "
    SELECT
        k.nama_kategori,
        COUNT(DISTINCT p.id_penjualan) as transactions,
        SUM(dp.qty) as items_sold,
        SUM(dp.subtotal) as revenue,
        COALESCE(SUM(dp.qty * dp.harga_modal), 0) as cost,
        (SUM(dp.subtotal) - COALESCE(SUM(dp.qty * dp.harga_modal), 0)) as profit
    FROM kategori k
    LEFT JOIN produk pr ON k.id_kategori = pr.id_kategori
    LEFT JOIN detail_penjualan dp ON pr.id_produk = dp.id_produk
    LEFT JOIN penjualan p ON dp.id_penjualan = p.id_penjualan
    WHERE DATE(p.tanggal) BETWEEN ? AND ?
    GROUP BY k.id_kategori, k.nama_kategori
    ORDER BY revenue DESC
";

$category_stmt = $connection->prepare($category_query);
$category_stmt->bind_param("ss", $start_date, $end_date);
$category_stmt->execute();
$category_data = $category_stmt->get_result();
$category_stmt->close();

// Product Report Data (Top 10 best selling products)
$product_query = "
    SELECT
        pr.nama_produk,
        pr.harga_jual,
        k.nama_kategori,
        SUM(dp.qty) as total_sold,
        SUM(dp.subtotal) as total_revenue,
        COALESCE(SUM(dp.qty * dp.harga_modal), 0) as total_cost,
        (SUM(dp.subtotal) - COALESCE(SUM(dp.qty * dp.harga_modal), 0)) as total_profit
    FROM produk pr
    LEFT JOIN kategori k ON pr.id_kategori = k.id_kategori
    LEFT JOIN detail_penjualan dp ON pr.id_produk = dp.id_produk
    LEFT JOIN penjualan p ON dp.id_penjualan = p.id_penjualan
    WHERE DATE(p.tanggal) BETWEEN ? AND ?
    GROUP BY pr.id_produk, pr.nama_produk, pr.harga_jual, k.nama_kategori
    ORDER BY total_sold DESC
    LIMIT 10
";

$product_stmt = $connection->prepare($product_query);
$product_stmt->bind_param("ss", $start_date, $end_date);
$product_stmt->execute();
$product_data = $product_stmt->get_result();
$product_stmt->close();

// Daily Sales Data for Chart
$daily_query = "
    SELECT
        DATE(p.tanggal) as date,
        COUNT(DISTINCT p.id_penjualan) as transactions,
        SUM(p.total_harga) as revenue,
        COALESCE(SUM(dp.qty * dp.harga_modal), 0) as cost,
        (SUM(p.total_harga) - COALESCE(SUM(dp.qty * dp.harga_modal), 0)) as profit
    FROM penjualan p
    LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
    WHERE DATE(p.tanggal) BETWEEN ? AND ?
    GROUP BY DATE(p.tanggal)
    ORDER BY DATE(p.tanggal)
";

$daily_stmt = $connection->prepare($daily_query);
$daily_stmt->bind_param("ss", $start_date, $end_date);
$daily_stmt->execute();
$daily_data = $daily_stmt->get_result();
$daily_stmt->close();

// Monthly Sales Data for Chart (last 12 months)
$monthly_query = "
    SELECT
        DATE_FORMAT(p.tanggal, '%Y-%m') as month,
        COUNT(DISTINCT p.id_penjualan) as transactions,
        SUM(p.total_harga) as revenue,
        COALESCE(SUM(dp.qty * dp.harga_modal), 0) as cost,
        (SUM(p.total_harga) - COALESCE(SUM(dp.qty * dp.harga_modal), 0)) as profit
    FROM penjualan p
    LEFT JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
    WHERE p.tanggal >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(p.tanggal, '%Y-%m')
    ORDER BY month
";

$monthly_data = $connection->query($monthly_query);

// Format currency function
function formatCurrency($amount, $currency_type) {
    if ($currency_type == 'USD') {
        return '$ ' . number_format($amount * getExchangeRate('IDR', 'USD'), 2, '.', ',');
    } else {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
?>

<style>
.reports-card {
    transition: transform 0.2s;
}
.reports-card:hover {
    transform: translateY(-2px);
}
.metric-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
.metric-card.success {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.metric-card.info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}
.metric-card.warning {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}
.table-responsive {
    border-radius: 0.375rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
.nav-tabs .nav-link.active {
    border-bottom: 3px solid #667eea;
    font-weight: 600;
}
</style>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><?= __('Reports') ?></h3>
                <p class="text-subtitle text-muted">
                    <?= __('Comprehensive business reports and analytics') ?>
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=dashboard">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= __('Reports') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <input type="hidden" name="halaman" value="reports">
                        <div class="col-md-4">
                            <label class="form-label"><?= __('Start Date') ?></label>
                            <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><?= __('End Date') ?></label>
                            <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-filter"></i> <?= __('Filter') ?>
                                </button>
                                <a href="index.php?halaman=reports" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> <?= __('Reset') ?>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card metric-card reports-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-white-50 mb-1"><?= __('Total Transactions') ?></h6>
                            <h4 class="mb-0 text-white"><?= number_format($sales_data['total_transactions'] ?? 0) ?></h4>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-receipt-cutoff" style="font-size: 2rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card metric-card success reports-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-white-50 mb-1"><?= __('Total Revenue') ?></h6>
                            <h4 class="mb-0 text-white"><?= formatCurrency($sales_data['total_revenue'] ?? 0, $currency) ?></h4>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-cash-stack" style="font-size: 2rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card metric-card info reports-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-white-50 mb-1"><?= __('Average Transaction') ?></h6>
                            <h4 class="mb-0 text-white"><?= formatCurrency($sales_data['avg_transaction'] ?? 0, $currency) ?></h4>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-graph-up" style="font-size: 2rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card metric-card warning reports-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-white-50 mb-1"><?= __('Items Sold') ?></h6>
                            <h4 class="mb-0 text-white"><?= number_format($sales_data['total_items_sold'] ?? 0) ?></h4>
                        </div>
                        <div class="ms-3">
                            <i class="bi bi-box-seam" style="font-size: 2rem; opacity: 0.8;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" id="reportsTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab">
                                <i class="bi bi-receipt"></i> <?= __('Sales Report') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="category-tab" data-bs-toggle="tab" data-bs-target="#category" type="button" role="tab">
                                <i class="bi bi-grid-3x3"></i> <?= __('Category Report') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="product-tab" data-bs-toggle="tab" data-bs-target="#product" type="button" role="tab">
                                <i class="bi bi-star"></i> <?= __('Top Products') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="charts-tab" data-bs-toggle="tab" data-bs-target="#charts" type="button" role="tab">
                                <i class="bi bi-bar-chart"></i> <?= __('Charts') ?>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-4" id="reportsTabContent">
                        <!-- Sales Report Tab -->
                        <div class="tab-pane fade show active" id="sales" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3"><?= __('Sales Summary') ?> (<?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?>)</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th><?= __('Metric') ?></th>
                                                    <th class="text-end"><?= __('Value') ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?= __('Total Transactions') ?></td>
                                                    <td class="text-end fw-bold"><?= number_format($sales_data['total_transactions'] ?? 0) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= __('Total Revenue') ?></td>
                                                    <td class="text-end fw-bold text-success"><?= formatCurrency($sales_data['total_revenue'] ?? 0, $currency) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= __('Average Transaction Value') ?></td>
                                                    <td class="text-end fw-bold text-info"><?= formatCurrency($sales_data['avg_transaction'] ?? 0, $currency) ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= __('Total Items Sold') ?></td>
                                                    <td class="text-end fw-bold text-warning"><?= number_format($sales_data['total_items_sold'] ?? 0) ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Modal</td>
                                                    <td class="text-end fw-bold text-danger">Rp <?= number_format($sales_data['total_cost'] ?? 0, 0, ',', '.') ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Profit</td>
                                                    <td class="text-end fw-bold text-success">Rp <?= number_format($sales_data['total_profit'] ?? 0, 0, ',', '.') ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Category Report Tab -->
                        <div class="tab-pane fade" id="category" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3"><?= __('Category Performance') ?></h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th><?= __('Category') ?></th>
                                                    <th class="text-end"><?= __('Transactions') ?></th>
                                                    <th class="text-end"><?= __('Items Sold') ?></th>
                                                    <th class="text-end"><?= __('Revenue') ?></th>
                                                    <th class="text-end">Cost</th>
                                                    <th class="text-end">Profit</th>
                                                    <th class="text-end"><?= __('Contribution') ?> (%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $total_revenue = $sales_data['total_revenue'] ?? 1; // Avoid division by zero
                                                while ($row = $category_data->fetch_assoc()):
                                                    $contribution = ($total_revenue > 0) ? ($row['revenue'] / $total_revenue) * 100 : 0;
                                                ?>
                                                    <tr>
                                                        <td class="fw-bold"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                                                        <td class="text-end"><?= number_format($row['transactions']) ?></td>
                                                        <td class="text-end"><?= number_format($row['items_sold']) ?></td>
                                                        <td class="text-end text-success fw-bold"><?= formatCurrency($row['revenue'], $currency) ?></td>
                                                        <td class="text-end text-danger fw-bold">Rp <?= number_format($row['cost'], 0, ',', '.') ?></td>
                                                        <td class="text-end text-success fw-bold">Rp <?= number_format($row['profit'], 0, ',', '.') ?></td>
                                                        <td class="text-end">
                                                            <span class="badge bg-primary"><?= number_format($contribution, 1) ?>%</span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Report Tab -->
                        <div class="tab-pane fade" id="product" role="tabpanel">
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3"><?= __('Top 10 Best Selling Products') ?></h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th><?= __('Product Name') ?></th>
                                                    <th><?= __('Category') ?></th>
                                                    <th class="text-end"><?= __('Unit Price') ?></th>
                                                    <th class="text-end"><?= __('Units Sold') ?></th>
                                                    <th class="text-end"><?= __('Total Revenue') ?></th>
                                                    <th class="text-end">Total Cost</th>
                                                    <th class="text-end">Total Profit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $rank = 1;
                                                while ($row = $product_data->fetch_assoc()):
                                                ?>
                                                    <tr>
                                                        <td class="fw-bold text-primary">#<?= $rank++ ?></td>
                                                        <td class="fw-bold"><?= htmlspecialchars($row['nama_produk']) ?></td>
                                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                                                        <td class="text-end"><?= formatCurrency($row['harga'], $currency) ?></td>
                                                        <td class="text-end fw-bold text-warning"><?= number_format($row['total_sold']) ?></td>
                                                        <td class="text-end fw-bold text-success"><?= formatCurrency($row['total_revenue'], $currency) ?></td>
                                                        <td class="text-end text-danger fw-bold">Rp <?= number_format($row['total_cost'], 0, ',', '.') ?></td>
                                                        <td class="text-end text-success fw-bold">Rp <?= number_format($row['total_profit'], 0, ',', '.') ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Tab -->
                        <div class="tab-pane fade" id="charts" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title"><?= __('Daily Sales') ?> (<?= date('M Y', strtotime($start_date)) ?>)</h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="dailySalesChart" style="max-height: 300px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title"><?= __('Monthly Sales') ?> (<?= __('Last 12 Months') ?>)</h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="monthlySalesChart" style="max-height: 300px;"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Sales Chart
<?php
$daily_labels = [];
$daily_revenue = [];
$daily_transactions = [];
$daily_profit = [];

while ($row = $daily_data->fetch_assoc()) {
    $daily_labels[] = date('d/m', strtotime($row['date']));
    $daily_revenue[] = $row['revenue'];
    $daily_transactions[] = $row['transactions'];
    $daily_profit[] = $row['profit'];
}
?>

const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($daily_labels) ?>,
        datasets: [{
            label: '<?= __('Revenue') ?>',
            data: <?= json_encode($daily_revenue) ?>,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            yAxisID: 'y'
        }, {
            label: '<?= __('Transactions') ?>',
            data: <?= json_encode($daily_transactions) ?>,
            borderColor: '#f093fb',
            backgroundColor: 'rgba(240, 147, 251, 0.1)',
            tension: 0.4,
            yAxisID: 'y1'
        }, {
            label: '<?= __('Profit') ?>',
            data: <?= json_encode($daily_profit) ?>,
            borderColor: '#43e97b',
            backgroundColor: 'rgba(67, 233, 123, 0.1)',
            tension: 0.4,
            yAxisID: 'y'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: '<?= __('Revenue/Profit (IDR)') ?>'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: '<?= __('Transactions') ?>'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Monthly Sales Chart
<?php
$monthly_labels = [];
$monthly_revenue = [];
$monthly_profit = [];

while ($row = $monthly_data->fetch_assoc()) {
    $monthly_labels[] = date('M Y', strtotime($row['month'] . '-01'));
    $monthly_revenue[] = $row['revenue'];
    $monthly_profit[] = $row['profit'];
}
?>

const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($monthly_labels) ?>,
        datasets: [{
            label: '<?= __('Monthly Revenue') ?>',
            data: <?= json_encode($monthly_revenue) ?>,
            backgroundColor: '#4facfe',
            borderColor: '#00f2fe',
            borderWidth: 1
        }, {
            label: '<?= __('Monthly Profit') ?>',
            data: <?= json_encode($monthly_profit) ?>,
            backgroundColor: '#43e97b',
            borderColor: '#38f9d7',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: '<?= __('Revenue/Profit (IDR)') ?>'
                }
            }
        }
    }
});
</script>
