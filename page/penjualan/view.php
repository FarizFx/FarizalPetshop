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

// Query untuk mendapatkan data penjualan tanpa customer
$query = mysqli_query($connection, "
    SELECT p.id_penjualan, p.tanggal, p.total_harga,
           COUNT(d.id_detail) as jumlah_item,
           SUM(d.qty * pr.harga_modal) as total_modal,
           SUM((d.qty * pr.harga_jual) - (d.qty * pr.harga_modal)) as total_profit
    FROM penjualan p
    LEFT JOIN detail_penjualan d ON p.id_penjualan = d.id_penjualan
    LEFT JOIN produk pr ON d.id_produk = pr.id_produk
    GROUP BY p.id_penjualan
    ORDER BY p.tanggal DESC
");
?>

<style>
/* Solusi khusus untuk modal vs sidebar */
#detailModal {
    z-index: 1060 !important;
}

.modal-backdrop {
    z-index: 1040 !important;
}

/* Perbaikan untuk sidebar */
body.modal-open .sidebar {
    position: relative;
    z-index: 1030 !important;
}

/* Responsive modal content */
#modalDetailContent {
    max-height: 65vh;
    overflow-y: auto;
    padding: 15px;
}

/* Loading spinner styling */
.modal-loading {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 200px;
}
</style>

<div class="page-heading">                                        
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><?= __('Sales Report') ?></h3>
                <p class="text-subtitle text-muted">
                    <?= __('List of Sales Transactions') ?>
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=dashboard">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= __('Sales Report') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <a href="index.php?halaman=tambah_penjualan" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> <?= __('Add Sales') ?>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <form method="post" class="row g-3">
                            <div class="col-md-5">
                                <input type="date" class="form-control" name="dari_tanggal">
                            </div>
                            <div class="col-md-5">
                                <input type="date" class="form-control" name="sampai_tanggal">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary"><?= __('Filter') ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                            <th><?= __('No.') ?></th>
                            <th><?= __('Date') ?></th>
                            <th><?= __('Quantity') ?></th>
                            <th><?= __('Total Price') ?></th>
                            <th>Total Modal</th>
                            <th>Total Profit</th>
                            <th><?= __('Action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query) > 0): ?>
                                <?php $no = 1; while($data = mysqli_fetch_assoc($query)): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($data['tanggal'])) ?></td>
                                        <td><?= $data['jumlah_item'] ?></td>
                                        <td>
                                            <?php if ($currency == 'USD'): ?>
                                                $ <?= number_format($data['total_harga'] * getExchangeRate('IDR', 'USD'), 2, '.', ',') ?>
                                            <?php else: ?>
                                                Rp <?= number_format($data['total_harga'], 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($currency == 'USD'): ?>
                                                $ <?= number_format(($data['total_modal'] ?? 0) * getExchangeRate('IDR', 'USD'), 2, '.', ',') ?>
                                            <?php else: ?>
                                                Rp <?= number_format($data['total_modal'] ?? 0, 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="<?= ($data['total_profit'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?php if ($currency == 'USD'): ?>
                                                $ <?= number_format(($data['total_profit'] ?? 0) * getExchangeRate('IDR', 'USD'), 2, '.', ',') ?>
                                            <?php else: ?>
                                                Rp <?= number_format($data['total_profit'] ?? 0, 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                          <button class="btn btn-sm btn-info btn-detail"
                                                 data-id="<?= $data['id_penjualan'] ?>">
                                             <i class="bi bi-eye"></i> <?= __('Detail') ?>
                                          </button>
                                          <a href="index.php?halaman=cetak_struk&id=<?= $data['id_penjualan'] ?>"
                                             target="_blank" class="btn btn-sm btn-success">
                                             <i class="bi bi-printer"></i> <?= __('Print') ?>
                                         </a>
                                         <a class="btn btn-danger btn-sm" id="btn-hapus" href="index.php?halaman=hapus_penjualan&id=<?= $data['id_penjualan'] ?>" onclick="confirmModal(event)"><?= __('Delete') ?></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center"><?= __('No sales data') ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal untuk detail penjualan -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fs-5">
                    <i class="bi bi-receipt"></i> <?= __('Transaction Details') ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="modalDetailContent">
                <div class="modal-loading text-primary">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden"><?= __('Loading...') ?></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> <?= __('Close') ?>
                </button>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-detail')) {
            const idPenjualan = e.target.getAttribute('data-id');
            const modal = new bootstrap.Modal(document.getElementById('detailModal'));

            // Tampilkan loading spinner
            document.getElementById('modalDetailContent').innerHTML = `
                <div class="text-center my-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;

            modal.show();

            // Load konten via AJAX dengan parameter modal=1
            fetch('index.php?halaman=detail_penjualan&id=' + idPenjualan + '&modal=1')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalDetailContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('modalDetailContent').innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            <?= __('Failed to load sales details') ?>
                        </div>
                    `;
                });
        }
    });
});
</script>
