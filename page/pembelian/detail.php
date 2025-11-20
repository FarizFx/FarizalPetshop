<?php
include "./function/connection.php";
include "./function/currency.php";
include "./function/language.php";

if (!hasPermission('purchase_management')) {
    header('Location: index.php?halaman=beranda');
    exit;
}

$id_pembelian = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pembelian <= 0) {
    header('Location: index.php?halaman=pembelian');
    exit;
}

// Get pembelian data
$pembelian_query = mysqli_query($connection, "
    SELECT * FROM pembelian WHERE id_pembelian = $id_pembelian
");
$pembelian = mysqli_fetch_assoc($pembelian_query);

if (!$pembelian) {
    header('Location: index.php?halaman=pembelian');
    exit;
}

// Get detail pembelian
$detail_query = mysqli_query($connection, "
    SELECT dp.*, p.nama_produk
    FROM detail_pembelian dp
    JOIN produk p ON dp.id_produk = p.id_produk
    WHERE dp.id_pembelian = $id_pembelian
    ORDER BY dp.id_detail
");
$detail_items = [];
while ($row = mysqli_fetch_assoc($detail_query)) {
    $detail_items[] = $row;
}
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><?php echo __('Detail Pembelian'); ?></h3>
                <p class="text-subtitle text-muted"><?php echo __('Detail pembelian produk'); ?> #<?php echo $pembelian['id_pembelian']; ?></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?halaman=beranda"><?php echo __('Dashboard'); ?></a></li>
                        <li class="breadcrumb-item"><a href="index.php?halaman=pembelian"><?php echo __('Pembelian'); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo __('Detail'); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <!-- Purchase Information -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo __('Informasi Pembelian'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('ID Pembelian'); ?>:</label>
                            <p><?php echo $pembelian['id_pembelian']; ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('Tanggal'); ?>:</label>
                            <p><?php echo date('d/m/Y H:i:s', strtotime($pembelian['tanggal'])); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold"><?php echo __('Total Harga'); ?>:</label>
                            <p class="fw-bold text-primary"><?php echo formatRupiah($pembelian['total_harga']); ?></p>
                        </div>
                        <?php if (!empty($pembelian['keterangan'])): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold"><?php echo __('Keterangan'); ?>:</label>
                                <p><?php echo htmlspecialchars($pembelian['keterangan']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Purchase Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo __('Detail Produk'); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><?php echo __('No'); ?></th>
                                        <th><?php echo __('Nama Produk'); ?></th>
                                        <th><?php echo __('Qty'); ?></th>
                                        <th><?php echo __('Satuan'); ?></th>
                                        <th><?php echo __('Harga Satuan'); ?></th>
                                        <th><?php echo __('Subtotal'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; foreach ($detail_items as $item): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                            <td><?php echo $item['qty']; ?></td>
                                            <td><?php echo htmlspecialchars($item['satuan'] ?? 'pcs'); ?></td>
                                            <td><?php echo formatRupiah($item['harga']); ?></td>
                                            <td><?php echo formatRupiah($item['subtotal']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-end"><?php echo __('Total'); ?>:</th>
                                        <th><?php echo formatRupiah($pembelian['total_harga']); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="index.php?halaman=pembelian" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> <?php echo __('Kembali ke Daftar Pembelian'); ?>
                    </a>
                    <div>
                        <a href="index.php?halaman=ubah_pembelian&id=<?php echo $pembelian['id_pembelian']; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> <?php echo __('Edit Pembelian'); ?>
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $pembelian['id_pembelian']; ?>)">
                            <i class="bi bi-trash"></i> <?php echo __('Hapus Pembelian'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function confirmDelete(id) {
    if (confirm('<?php echo __('Apakah Anda yakin ingin menghapus pembelian ini? Semua data terkait akan dihapus.'); ?>')) {
        window.location.href = 'index.php?halaman=pembelian&action=delete&id=' + id;
    }
}
</script>
