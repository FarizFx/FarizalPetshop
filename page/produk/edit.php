<?php
include "./function/connection.php";

$id_produk = $_GET['id'];
$query_produk = mysqli_query($connection, "SELECT * FROM produk WHERE id_produk = '$id_produk'");
$produk = mysqli_fetch_assoc($query_produk);

if(isset($_POST['submit'])) {
    $nama_produk = htmlspecialchars($_POST['nama_produk']);
    $id_kategori = $_POST['id_kategori'];
    $merk = htmlspecialchars($_POST['merk']);
    $harga = str_replace('.', '', $_POST['harga']);
    $harga_modal = str_replace('.', '', $_POST['harga_modal']);
    $stok = $_POST['stok'];
    $deskripsi = htmlspecialchars($_POST['deskripsi']);

    $query = mysqli_query($connection, "
        UPDATE produk SET
        nama_produk = '$nama_produk',
        id_kategori = '$id_kategori',
        merk = '$merk',
        harga = '$harga',
        harga_modal = '$harga_modal',
        stok = '$stok',
        deskripsi = '$deskripsi'
        WHERE id_produk = '$id_produk'
    ");

    if($query) {
        echo "
        <script>
        Swal.fire({
            title: 'Berhasil',
            text: 'Data produk berhasil diupdate',
            icon: 'success',
            showConfirmButton: false,
            timer: 2000
        }).then(() => {
            window.location.href = 'index.php?halaman=produk';
        });
        </script>
        ";
    } else {
        echo "
        <script>
        Swal.fire({
            title: 'Gagal',
            text: 'Gagal mengupdate data produk',
            icon: 'error',
            showConfirmButton: false,
            timer: 2000
        });
        </script>
        ";
    }
}

$kategori = mysqli_query($connection, "SELECT * FROM kategori");
?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Edit Produk</h3>
                <p class="text-subtitle text-muted">
                    Formulir Edit Data Produk
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=produk">Produk</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Edit Produk
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form action="" method="post">
                    <div class="form-group mb-3">
                        <label for="nama_produk">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                               value="<?= htmlspecialchars($produk['nama_produk']) ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label for="id_kategori">Kategori</label>
                        <select class="form-select" id="id_kategori" name="id_kategori" required>
                            <option value="">Pilih Kategori</option>
                            <?php while($k = mysqli_fetch_assoc($kategori)): ?>
                                <option value="<?= $k['id_kategori'] ?>" 
                                    <?= ($k['id_kategori'] == $produk['id_kategori']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="merk">Merk</label>
                        <input type="text" class="form-control" id="merk" name="merk" 
                               value="<?= htmlspecialchars($produk['merk']) ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label for="harga_modal">Harga Modal</label>
                        <input type="text" class="form-control" id="harga_modal" name="harga_modal"
                               value="<?= number_format($produk['harga_modal'] ?? 0, 0, ',', '.') ?>" required
                               oninput="formatCurrency(this)">
                    </div>

                    <div class="form-group mb-3">
                        <label for="harga">Harga Jual</label>
                        <input type="text" class="form-control" id="harga" name="harga"
                               value="<?= number_format($produk['harga'] ?? 0, 0, ',', '.') ?>" required
                               oninput="formatCurrency(this)">
                    </div>

                    <div class="form-group mb-3">
                        <label for="stok">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok"
                               value="<?= $produk['stok'] ?>" required min="0">
                    </div>

                    <div class="form-group mb-3">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= htmlspecialchars($produk['deskripsi']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" name="submit" class="btn btn-primary">Update Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    
    if(value.length > 0) {
        value = parseInt(value).toLocaleString('id-ID');
    }
    
    input.value = value;
}

document.addEventListener('DOMContentLoaded', function() {
    const hargaInput = document.getElementById('harga');
    if(hargaInput) {
        hargaInput.value = hargaInput.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
});
</script>