<?php
include "./function/connection.php";
include "./function/language.php";

$query = mysqli_query($connection, "
    SELECT k.*,
           COUNT(p.id_produk) as jumlah_produk,
           COALESCE(SUM(p.harga_jual * p.stok), 0) as total_nilai_stok,
           COALESCE(SUM(p.harga_modal * p.stok), 0) as total_modal_stok,
           (COALESCE(SUM(p.harga_jual * p.stok), 0) - COALESCE(SUM(p.harga_modal * p.stok), 0)) as estimasi_profit
    FROM kategori k
    LEFT JOIN produk p ON k.id_kategori = p.id_kategori
    GROUP BY k.id_kategori
    ORDER BY k.id_kategori
");
?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><?= __('Categories') ?></h3>
                <p class="text-subtitle text-muted">
                    <?= __('Categories Data Page') ?>
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
         
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=kategori"><?= __('Categories') ?></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= __('View Categories Data') ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <a href="index.php?halaman=tambah_kategori" class="btn btn-primary btn-sm mb-3"><?= __('Add Data') ?></a>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= __('Category Name') ?></th>
                                <th>Jumlah Produk</th>
                                <th>Total Nilai Stok</th>
                                <th>Total Modal Stok</th>
                                <th>Estimasi Profit</th>
                                <th><?= __('Description') ?></th>
                                <th><?= __('Action') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query->num_rows > 0) : ?>
                                <?php
                                $i = 1;
                                while ($data = mysqli_fetch_assoc($query)) : ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= htmlspecialchars($data['nama_kategori']) ?></td>
                                        <td class="text-center"><?= $data['jumlah_produk'] ?></td>
                                        <td class="text-end">Rp <?= number_format($data['total_nilai_stok'], 0, ',', '.') ?></td>
                                        <td class="text-end">Rp <?= number_format($data['total_modal_stok'], 0, ',', '.') ?></td>
                                        <td class="text-end <?= $data['estimasi_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            Rp <?= number_format($data['estimasi_profit'], 0, ',', '.') ?>
                                        </td>
                                        <td><?= htmlspecialchars($data['deskripsi'] ?? '-') ?></td>
                                        <td>
                                            <a class="btn btn-primary btn-sm" href="index.php?halaman=ubah_kategori&id=<?= $data['id_kategori'] ?>"><?= __('Edit') ?></a>
                                            <a class="btn btn-danger btn-sm" id="btn-hapus" href="index.php?halaman=hapus_kategori&id=<?= $data['id_kategori'] ?>" onclick="confirmModal(event)"><?= __('Delete') ?></a>
                                        </td>
                                    </tr>
                                <?php endwhile ?>
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