<?php
include "./function/connection.php";

$query = mysqli_query($connection, "
    SELECT p.*, k.nama_kategori 
    FROM produk p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    ORDER BY p.id_produk DESC
");
?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><?= __('Product Data') ?></h3>
                <p class="text-subtitle text-muted">
                    <?= __('Product Data Display Page') ?>
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=produk">Produk</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Daftar Produk
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <a href="index.php?halaman=tambah_produk" class="btn btn-primary btn-sm mb-3">Tambah Data</a>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Merk</th>
                                <th>Satuan</th>
                                <th>Harga Modal</th>
                                <th>Harga Jual</th>
                                <th>Profit per Unit</th>
                                <th>Stok</th>
                                <th>Total Nilai Stok</th>
                                <th>Total Modal Stok</th>
                                <th>Estimasi Profit Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query) > 0) : ?>
                                <?php
                                $i = 1;
                                while ($data = mysqli_fetch_assoc($query)) :
                                    $profit_per_unit = $data['harga_jual'] - $data['harga_modal'];
                                    $total_nilai_stok = $data['harga_jual'] * $data['stok'];
                                    $total_modal_stok = $data['harga_modal'] * $data['stok'];
                                    $estimasi_profit_stok = $profit_per_unit * $data['stok'];
                                ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($data['nama_produk']) ?></td>
                                        <td><?= htmlspecialchars($data['nama_kategori'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($data['merk'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($data['satuan'] ?? 'pcs') ?></td>
                                        <td>Rp <?= number_format($data['harga_modal'], 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($data['harga_jual'], 0, ',', '.') ?></td>
                                        <td class="<?= $profit_per_unit >= 0 ? 'text-success' : 'text-danger' ?>">
                                            Rp <?= number_format($profit_per_unit, 0, ',', '.') ?>
                                        </td>
                                        <td><?= $data['stok'] ?></td>
                                        <td class="text-end">Rp <?= number_format($total_nilai_stok, 0, ',', '.') ?></td>
                                        <td class="text-end">Rp <?= number_format($total_modal_stok, 0, ',', '.') ?></td>
                                        <td class="text-end <?= $estimasi_profit_stok >= 0 ? 'text-success' : 'text-danger' ?>">
                                            Rp <?= number_format($estimasi_profit_stok, 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <a class="btn btn-primary btn-sm" href="index.php?halaman=ubah_produk&id=<?= $data['id_produk'] ?>">Ubah</a>
                                            <a class="btn btn-danger btn-sm" id="btn-hapus"
                                               href="index.php?halaman=hapus_produk&id=<?= $data['id_produk'] ?>"
                                               onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="13" class="text-center">Tidak ada data produk</td>
                                </tr>
                            <?php endif ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
<script src="./assets/extensions/simple-datatables/umd/simple-datatables.js"></script>
<script src="./assets/static/js/pages/simple-datatables.js"></script>