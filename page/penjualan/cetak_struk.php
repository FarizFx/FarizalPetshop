<?php
// Prevent output buffering that might include sidebar
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['nama'])) {
    header('Location: ././login.php');
    exit();
}

// Include dependencies with correct paths
include "././function/connection.php";
include "././function/currency.php";
include "././function/language.php";

// Get receipt ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    http_response_code(400);
    die(__('Invalid request: missing id.'));
}

// Load store settings
$settings = [];
$stmt = $connection->prepare("SELECT * FROM settings WHERE id=1");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $settings = $res->fetch_assoc();
    }
    $stmt->close();
}

$currency = $settings['currency'] ?? 'IDR';

// Safe currency formatter
if (!function_exists('fmt')) {
    function fmt($amount, $currency = 'IDR') {
        $amount = (float)$amount;
        if ($currency === 'USD') {
            if (function_exists('getExchangeRate')) {
                $rate = (float)getExchangeRate('IDR', 'USD');
                return '$ ' . number_format($amount * $rate, 2, '.', ',');
            }
            return '$ ' . number_format($amount, 2, '.', ',');
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}

// Fetch sale header
$sale = null;
$stmt = $connection->prepare("
    SELECT p.id_penjualan, p.tanggal, p.total_harga,
           COALESCE(p.keterangan, '') AS keterangan
    FROM penjualan p
    WHERE p.id_penjualan = ?
    LIMIT 1
");
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $sale = $res->fetch_assoc();
    $stmt->close();
}

if (!$sale) {
    http_response_code(404);
    die(__('Sale not found.'));
}

// Fetch sale items
$items = [];
$stmt = $connection->prepare("
    SELECT dp.id_detail, dp.id_produk, dp.qty, dp.harga_jual, dp.subtotal,
           COALESCE(pr.nama_produk, '') AS nama_produk,
           COALESCE(dp.harga_modal, 0) AS harga_modal
    FROM detail_penjualan dp
    LEFT JOIN produk pr ON dp.id_produk = pr.id_produk
    WHERE dp.id_penjualan = ?
    ORDER BY dp.id_detail ASC
");
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $items[] = $r;
    }
    $stmt->close();
}

// Calculate totals
$total_qty = 0;
$total_revenue = 0;
$total_cost = 0;
$total_profit = 0;

foreach ($items as &$it) {
    $qty = (int)($it['qty'] ?? 0);
    $unit = (float)($it['harga_jual'] ?? 0);
    $cost_unit = (float)($it['harga_modal'] ?? 0);
    $subtotal = isset($it['subtotal']) && $it['subtotal'] !== null ? (float)$it['subtotal'] : ($qty * $unit);
    $cost = $qty * $cost_unit;
    $profit = $subtotal - $cost;

    $it['calc_subtotal'] = $subtotal;
    $it['calc_cost'] = $cost;
    $it['calc_profit'] = $profit;

    $total_qty += $qty;
    $total_revenue += $subtotal;
    $total_cost += $cost;
    $total_profit += $profit;
}
unset($it);

// Fallback to DB total if needed
if ((float)$sale['total_harga'] > 0 && ($total_revenue == 0 || abs($total_revenue - (float)$sale['total_harga']) > 0.001)) {
    $total_revenue = (float)$sale['total_harga'];
    $total_profit = $total_revenue - $total_cost;
}

$profit_margin = ($total_revenue > 0) ? ($total_profit / $total_revenue) * 100 : 0;

// Get current language from session
$current_language = $_SESSION['language'] ?? 'id';

// Clear output buffer
ob_end_clean();

// Set content type header
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="<?= htmlspecialchars($current_language) ?>">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title><?= __('Receipt') ?> - #<?= htmlspecialchars($sale['id_penjualan']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box;
        }
        
        html, body { 
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333; 
            background: #f5f5f5;
            padding: 20px;
        }
        
        .receipt-container { 
            max-width: 1000px; 
            margin: 0 auto; 
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .store-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }
        
        .store-info {
            font-size: 13px;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .receipt-body {
            padding: 30px 20px;
        }
        
        .receipt-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .meta-value {
            font-size: 16px;
            font-weight: 700;
            color: #333;
        }
        
        .items-table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        .items-table thead {
            background: #f8f9fa;
            border-top: 2px solid #667eea;
            border-bottom: 2px solid #667eea;
        }
        
        .items-table th {
            padding: 12px 8px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
            padding-right: 10px;
        }
        
        .items-table th:first-child,
        .items-table td:first-child {
            padding-left: 10px;
        }
        
        .items-table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }
        
        .items-table tbody tr:hover {
            background: #fafafa;
        }
        
        .items-table td {
            padding: 10px 8px;
        }
        
        .product-name {
            font-weight: 600;
            color: #333;
        }
        
        .product-unit {
            font-size: 11px;
            color: #999;
            display: block;
            margin-top: 2px;
        }
        
        .price-cell {
            font-weight: 600;
            color: #333;
        }
        
        .cost-cell {
            color: #d9534f;
            font-size: 12px;
        }
        
        .profit-cell {
            color: #5cb85c;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .summary-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            font-size: 14px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            color: #666;
            font-weight: 500;
        }
        
        .summary-value {
            font-weight: 600;
            color: #333;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .summary-card {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }
        
        .summary-card.cost {
            border-left-color: #d9534f;
        }
        
        .summary-card.revenue {
            border-left-color: #5cb85c;
        }
        
        .summary-card.profit {
            border-left-color: #f0ad4e;
        }
        
        .card-label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .card-value {
            font-size: 16px;
            font-weight: 700;
            color: #333;
        }
        
        .notes {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #856404;
        }
        
        .footer-message {
            text-align: center;
            padding: 20px;
            color: #999;
            font-size: 13px;
            border-top: 1px solid #f0f0f0;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-print {
            background: #667eea;
            color: white;
        }
        
        .btn-print:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
        }
        
        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        }
        
        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
            
            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                background: white !important;
            }
            
            body {
                padding: 0;
                margin: 0;
            }
            
            .receipt-container {
                box-shadow: none;
                max-width: 100%;
                border-radius: 0;
                margin: 0;
                page-break-after: avoid;
                background: white;
            }
            
            .receipt-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
                page-break-after: avoid;
            }
            
            .receipt-body {
                page-break-inside: avoid;
            }
            
            .items-table thead {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                page-break-inside: avoid;
            }
            
            .items-table tbody tr {
                page-break-inside: avoid;
            }
            
            .summary-section {
                background: #f8f9fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                page-break-inside: avoid;
            }
            
            .summary-card {
                background: white !important;
                border: 1px solid #ddd !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .summary-card.cost {
                border-left: 4px solid #d9534f !important;
            }
            
            .summary-card.revenue {
                border-left: 4px solid #5cb85c !important;
            }
            
            .summary-card.profit {
                border-left: 4px solid #f0ad4e !important;
            }
            
            .notes {
                background: #fff3cd !important;
                border-left: 4px solid #ffc107 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .footer-message {
                page-break-inside: avoid;
            }
            
            .action-buttons {
                display: none !important;
            }
            
            .btn {
                display: none !important;
            }
        }
        
        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
            
            .items-table {
                font-size: 12px;
            }
            
            .items-table th,
            .items-table td {
                padding: 8px 5px;
            }
        }
        
        @media (max-width: 600px) {
            .receipt-body {
                padding: 20px 15px;
            }
            
            .receipt-meta {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .store-name {
                font-size: 22px;
            }
            
            .items-table th,
            .items-table td {
                padding: 8px 4px;
                font-size: 11px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <div class="store-name">
                <i class="bi bi-shop" style="margin-right: 10px;"></i>
                <?= htmlspecialchars($settings['store_name'] ?? 'Farizal Petshop') ?>
            </div>
            <div class="store-info">
                <?php if (!empty($settings['store_address'])): ?>
                    <div><?= htmlspecialchars($settings['store_address']) ?></div>
                <?php endif; ?>
                <?php if (!empty($settings['store_phone']) || !empty($settings['store_email'])): ?>
                    <div style="margin-top: 8px;">
                        <?php if (!empty($settings['store_phone'])): ?>
                            <i class="bi bi-telephone"></i> <?= htmlspecialchars($settings['store_phone']) ?>
                        <?php endif; ?>
                        <?php if (!empty($settings['store_phone']) && !empty($settings['store_email'])): ?>
                            &nbsp;|&nbsp;
                        <?php endif; ?>
                        <?php if (!empty($settings['store_email'])): ?>
                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($settings['store_email']) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Body -->
        <div class="receipt-body">
            <!-- Meta Info -->
            <div class="receipt-meta">
                <div class="meta-item">
                    <div class="meta-label"><?= __('Receipt Number') ?></div>
                    <div class="meta-value">#<?= str_pad($sale['id_penjualan'], 6, '0', STR_PAD_LEFT) ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label"><?= __('Date & Time') ?></div>
                    <div class="meta-value"><?= date('d/m/Y H:i', strtotime($sale['tanggal'])) ?></div>
                </div>
            </div>

            <!-- Notes -->
            <?php if (!empty($sale['keterangan'])): ?>
                <div class="notes">
                    <i class="bi bi-info-circle"></i>
                    <strong><?= __('Note') ?>:</strong> <?= htmlspecialchars($sale['keterangan']) ?>
                </div>
            <?php endif; ?>

            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 25%;"><?= __('Product Name') ?></th>
                        <th style="width: 8%;"><?= __('Qty') ?></th>
                        <th style="width: 12%;"><?= __('Cost Price') ?></th>
                        <th style="width: 12%;"><?= __('Sell Price') ?></th>
                        <th style="width: 15%;"><?= __('Subtotal') ?></th>
                        <th style="width: 12%;"><?= __('Total Cost') ?></th>
                        <th style="width: 16%;"><?= __('Profit') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td>
                                    <div class="product-name"><?= htmlspecialchars($it['nama_produk'] ?: ($it['id_produk'] ?? '-')) ?></div>
                                    <span class="product-unit"><?= htmlspecialchars($it['satuan'] ?? 'pcs') ?></span>
                                </td>
                                <td><?= number_format($it['qty']) ?></td>
                                <td class="cost-cell"><?= fmt($it['harga_modal'], $currency) ?></td>
                                <td class="price-cell"><?= fmt($it['harga_jual'], $currency) ?></td>
                                <td class="price-cell"><?= fmt($it['calc_subtotal'], $currency) ?></td>
                                <td class="cost-cell"><?= fmt($it['calc_cost'], $currency) ?></td>
                                <td class="profit-cell"><?= fmt($it['calc_profit'], $currency) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-state"><?= __('No items found') ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Summary Section -->
            <div class="summary-section">
                <div class="summary-row">
                    <span class="summary-label"><?= __('Total Items') ?></span>
                    <span class="summary-value"><?= number_format($total_qty) ?> <?= __('pcs') ?></span>
                </div>

                <div class="summary-grid">
                    <div class="summary-card cost">
                        <div class="card-label"><i class="bi bi-box"></i> <?= __('Total Cost') ?></div>
                        <div class="card-value"><?= fmt($total_cost, $currency) ?></div>
                    </div>
                    <div class="summary-card revenue">
                        <div class="card-label"><i class="bi bi-cash"></i> <?= __('Total Revenue') ?></div>
                        <div class="card-value"><?= fmt($total_revenue, $currency) ?></div>
                    </div>
                    <div class="summary-card profit">
                        <div class="card-label"><i class="bi bi-graph-up"></i> <?= __('Total Profit') ?></div>
                        <div class="card-value"><?= fmt($total_profit, $currency) ?></div>
                    </div>
                </div>

                <div class="summary-row profit" style="margin-top: 15px;">
                    <span class="summary-label">
                        <?= __('Profit Margin') ?>
                    </span>
                    <span class="summary-value"><?= number_format($profit_margin, 2) ?>%</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-message">
            <i class="bi bi-heart"></i> Made with Love by FarizalPetshop
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-print" onclick="window.print();">
                <i class="bi bi-printer"></i> <?= __('Print') ?>
            </button>
            <a href="javascript:window.close();" class="btn btn-back">
                <i class="bi bi-x-circle"></i> <?= __('Close') ?>
            </a>
        </div>
    </div>

    <script>
        // Auto-print when opened with auto_print parameter
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_print') === '1') {
            window.addEventListener('load', function() {
                setTimeout(function() { window.print(); }, 500);
            });
        }
    </script>
</body>
</html>