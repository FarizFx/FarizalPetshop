<?php
include "./function/connection.php";
include "./function/language.php";

$query = mysqli_query($connection, "SELECT * FROM kategori");
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
                                        <td><?= $data['nama_kategori'] ?></td>
                                        <td><?= $data['deskripsi'] ?></td>
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