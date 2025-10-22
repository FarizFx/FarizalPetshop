<?php
include "./function/connection.php";

try {
    $message = "";
    $success = false;
    $error = false;

    if (isset($_POST['submit'])) {
        $nama_produk = htmlspecialchars($_POST['nama_produk']);
        $id_kategori = $_POST['id_kategori'];
        $merk = htmlspecialchars($_POST['merk']);
        $harga = str_replace('.', '', $_POST['harga']);
        $stok = $_POST['stok'];
        $deskripsi = htmlspecialchars($_POST['deskripsi']);

        $query = mysqli_query($connection, "INSERT INTO produk (nama_produk, id_kategori, merk, harga, stok, deskripsi) VALUES ('$nama_produk', '$id_kategori', '$merk', '$harga', '$stok', '$deskripsi')");

        if ($query) {
            $message = "Produk berhasil ditambahkan";
            echo "
            <script>
            Swal.fire({
                title: 'Berhasil',
                text: '$message',
                icon: 'success',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            }).then(() => {
                window.location.href = 'index.php?halaman=produk';
            })
            </script>
            ";
        } else {
            $message = "Gagal menambahkan produk: " . mysqli_error($connection);
            echo "
            <script>
            Swal.fire({
                title: 'Gagal',
                text: '$message',
                icon: 'error',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            }).then(() => {
                window.location.href = 'index.php?halaman=tambah_produk';
            })
            </script>
            ";
        }
    }
} catch (\Throwable $th) {
    echo "
    <script>
    Swal.fire({
        title: 'Error',
        text: 'Terjadi kesalahan server',
        icon: 'error',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = 'index.php?halaman=produk';
    })
    </script>
    ";
}
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Tambah Produk</h3>
                <p class="text-subtitle text-muted">
                    Halaman untuk menambahkan produk baru
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=produk">Produk</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Tambah Produk
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <a href="index.php?halaman=produk" class="btn btn-primary btn-sm mb-3">Kembali</a>
        <div class="card">
            <div class="card-body">
                <form action="" method="post">
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="nama_produk" placeholder="Nama Produk" name="nama_produk" required>
                        <label for="nama_produk">Nama Produk</label>
                    </div>

                    <div class="mb-3">
                        <label for="id_kategori" class="form-label">Kategori</label>
                        <select class="form-select" id="id_kategori" name="id_kategori" required>
                            <option value="">Pilih Kategori</option>
                            <?php
                            $kategori = mysqli_query($connection, "SELECT * FROM kategori");
                            while($k = mysqli_fetch_assoc($kategori)) {
                                echo '<option value="'.$k['id_kategori'].'">'.$k['nama_kategori'].'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="merk" placeholder="Merk Produk" name="merk">
                        <label for="merk">Merk</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="harga" placeholder="Harga" name="harga" required 
                               oninput="formatCurrency(this)">
                        <label for="harga">Harga</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="stok" placeholder="Stok" name="stok" required min="0">
                        <label for="stok">Stok</label>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary" name="submit">Simpan Produk</button>
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
</script>