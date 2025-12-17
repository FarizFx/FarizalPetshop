<?php
include "./function/connection.php";

// Check if user has product_management permission
if (!hasPermission('product_management')) {
    echo "
    <script>
    Swal.fire({
        title: 'Akses Ditolak',
        text: 'Anda tidak memiliki izin untuk mengelola produk',
        icon: 'error',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = 'index.php?halaman=produk';
    })
    </script>
    ";
    exit();
}

try {
    $message = "";
    $success = false;
    $error = false;

    if (isset($_POST['submit'])) {
        $nama = isset($_POST['nama_produk']) ? trim($_POST['nama_produk']) : '';
        $kategori_id = isset($_POST['id_kategori']) ? (int)$_POST['id_kategori'] : 0;
        $merk = isset($_POST['merk']) ? trim($_POST['merk']) : '';
        $harga = isset($_POST['harga']) ? (float)str_replace('.', '', $_POST['harga']) : 0;
        $harga_modal = isset($_POST['harga_modal']) ? (float)str_replace('.', '', $_POST['harga_modal']) : 0;
        $stok = isset($_POST['stok']) ? (int)$_POST['stok'] : 0;
        $deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';

        // Validasi input
        if (empty($nama) || empty($kategori_id) || empty($harga) || empty($stok)) {
            throw new Exception("Data tidak lengkap. Semua field wajib diisi.");
        }

        // Insert data
        $query = "INSERT INTO produk (nama_produk, id_kategori, merk, harga_jual, harga_modal, stok, deskripsi) 
                  VALUES ('$nama', '$kategori_id', '$merk', '$harga', '$harga_modal', '$stok', '$deskripsi')";
        
        if (mysqli_query($connection, $query)) {
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
            throw new Exception("Gagal menambahkan produk: " . mysqli_error($connection));
        }
    }
} catch (Exception $th) {
    $error_message = addslashes($th->getMessage());
    echo "
    <script>
    Swal.fire({
        title: 'Gagal',
        text: '$error_message',
        icon: 'error',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = 'index.php?halaman=tambah_produk';
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
                            $kategori = mysqli_query($connection, "SELECT id_kategori, nama_kategori FROM kategori");
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
                        <input type="text" class="form-control" id="harga_modal" placeholder="Harga Modal" name="harga_modal" required
                               oninput="formatCurrency(this)">
                        <label for="harga_modal">Harga Modal</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="harga" placeholder="Harga Jual" name="harga" required
                               oninput="formatCurrency(this)">
                        <label for="harga">Harga Jual</label>
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