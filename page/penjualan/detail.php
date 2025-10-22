<?php
if(!defined('MODAL_REQUEST')) die('Akses langsung dilarang');
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

if(isset($_GET['id'])) {
    $id_penjualan = $_GET['id'];
    
    // Query data penjualan - TIDAK DIUBAH
    $query_penjualan = mysqli_query($connection, "
        SELECT p.*, DATE_FORMAT(p.tanggal, '%d/%m/%Y %H:%i') as tanggal_format
        FROM penjualan p
        WHERE p.id_penjualan = '$id_penjualan'
    ");
    $penjualan = mysqli_fetch_assoc($query_penjualan);
    
    // Query detail penjualan - TIDAK DIUBAH
    $query_detail = mysqli_query($connection, "
        SELECT d.*, pr.nama_produk, pr.harga as harga_satuan
        FROM detail_penjualan d
        JOIN produk pr ON d.id_produk = pr.id_produk
        WHERE d.id_penjualan = '$id_penjualan'
    ");
    
    // Hitung total item - TIDAK DIUBAH
    $total_item = mysqli_num_rows($query_detail);
    ?>
    
    <!-- Hanya tambahkan div wrapper untuk spacing -->
    <div class="modal-content p-3"> <!-- Tambah class ini saja -->
    
    <!-- Kode Anda yang sudah ada tetap UTUH di bawah ini -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h6><?= __('Transaction Information') ?></h6>
            <table class="table table-sm table-bordered">
                <tr>
                    <th width="40%"><?= __('Sales ID') ?></th>
                    <td><?= $penjualan['id_penjualan'] ?></td>
                </tr>
                <tr>
                    <th><?= __('Date') ?></th>
                    <td><?= $penjualan['tanggal_format'] ?></td>
                </tr>
                <tr>
                    <th><?= __('Total Items') ?></th>
                    <td><?= $total_item ?></td>
                </tr>
                <tr>
                    <th><?= __('Total Price') ?></th>
                    <td>
                        <?php if ($currency == 'USD'): ?>
                            $ <?= number_format($penjualan['total_harga'] * getExchangeRate('IDR', 'USD'), 2, '.', ',') ?>
                        <?php else: ?>
                            Rp <?= number_format($penjualan['total_harga'], 0, ',', '.') ?>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <h6><?= __('Product List') ?></h6>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= __('Product Name') ?></th>
                    <th><?= __('Unit Price') ?></th>
                    <th><?= __('Quantity') ?></th>
                    <th><?= __('Subtotal') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($detail = mysqli_fetch_assoc($query_detail)): 
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($detail['nama_produk']) ?></td>
                        <td>Rp <?= number_format($detail['harga_satuan'], 0, ',', '.') ?></td>
                        <td><?= $detail['qty'] ?></td>
                        <td>Rp <?= number_format($detail['subtotal'], 0, ',', '.') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    </div> <!-- Tutup div wrapper tambahan -->
    
    <?php
} else {
    echo '<div class="alert alert-danger">' . __('Invalid sales ID') . '</div>';
}