<?php
include "./function/connection.php";

// Check permission for stock management
if (!$role_manager->hasPermission('stock_management')) {
    echo "
    <script>
    Swal.fire({
        title: 'Akses Ditolak',
        text: 'Anda tidak memiliki izin untuk mengakses halaman ini',
        icon: 'error',
        showConfirmButton: false,
        timer: 2000
    }).then(() => {
        window.location.href = 'index.php?halaman=beranda';
    });
    </script>
    ";
    exit;
}

try {
    $message = "";
    $success = false;
    $error = false;
    $selected_produk = isset($_GET['id_produk']) ? $_GET['id_produk'] : '';

    if (isset($_POST['submit'])) {
        $id_produk = $_POST['id_produk'];
        $stok_baru = (int)$_POST['stok_baru'];

        // Validasi input
        if (empty($id_produk) || $stok_baru < 0) {
            $message = "Data tidak valid";
            $error = true;
        } else {
            // Update stok produk langsung
            $query = mysqli_query($connection, "
                UPDATE produk SET stok = $stok_baru
                WHERE id_produk = '$id_produk'
            ");

            if ($query) {
                $message = "Stok berhasil diperbarui";
                $success = true;
            } else {
                $message = "Gagal memperbarui stok: " . mysqli_error($connection);
                $error = true;
            }
        }

        if ($success) {
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
                window.location.href = 'index.php?halaman=stok';
            })
            </script>
            ";
        } elseif ($error) {
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
                window.location.href = 'index.php?halaman=ubah_stok';
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
        window.location.href = 'index.php?halaman=stok';
    })
    </script>
    ";
}

// Ambil data produk untuk dropdown
$produk_query = mysqli_query($connection, "
    SELECT p.id_produk, p.nama_produk, p.stok, k.nama_kategori
    FROM produk p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    ORDER BY p.nama_produk ASC
");

// Ambil data produk yang dipilih
$produk_terpilih = null;
if ($selected_produk) {
    $produk_detail = mysqli_query($connection, "
        SELECT p.*, k.nama_kategori
        FROM produk p
        LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
        WHERE p.id_produk = '$selected_produk'
    ");
    $produk_terpilih = mysqli_fetch_assoc($produk_detail);
}
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Ubah Stok</h3>
                <p class="text-subtitle text-muted">
                    Formulir Perubahan Stok Produk
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=stok">Stok</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Ubah Stok
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <a href="index.php?halaman=stok" class="btn btn-primary btn-sm mb-3">Kembali</a>
        <div class="card">
            <div class="card-body">
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="id_produk" class="form-label">Pilih Produk</label>
                        <select class="form-select" id="id_produk" name="id_produk" required>
                            <option value="">Pilih Produk</option>
                            <?php while($produk = mysqli_fetch_assoc($produk_query)): ?>
                                <option value="<?= $produk['id_produk'] ?>" data-stok-sekarang="<?= $produk['stok'] ?>" <?= ($selected_produk == $produk['id_produk']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($produk['nama_produk']) ?> -
                                    Kategori: <?= htmlspecialchars($produk['nama_kategori'] ?? '-') ?> -
                                    Stok Saat Ini: <?= $produk['stok'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stok_baru" class="form-label">Stok Baru</label>
                        <div class="input-group" style="width: 150px;">
                            <button class="btn btn-outline-secondary decrease-btn" type="button">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="stok_baru" name="stok_baru"
                                   value="<?= $produk_terpilih ? $produk_terpilih['stok'] : 0 ?>" min="0" required style="width: 60px;">
                            <button class="btn btn-outline-secondary increase-btn" type="button">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary" name="submit">
                            <i class="bi bi-check-circle"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const produkSelect = document.getElementById('id_produk');
    const stokBaruInput = document.getElementById('stok_baru');
    const decreaseBtn = document.querySelector('.decrease-btn');
    const increaseBtn = document.querySelector('.increase-btn');

    // Update stok ketika produk dipilih
    produkSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const stokSekarang = parseInt(selectedOption.getAttribute('data-stok-sekarang')) || 0;
            stokBaruInput.value = stokSekarang;
        }
    });

    // Tombol kurangi
    decreaseBtn.addEventListener('click', function() {
        let currentValue = parseInt(stokBaruInput.value) || 0;
        if (currentValue > 0) {
            stokBaruInput.value = currentValue - 1;
        }
    });

    // Tombol tambah
    increaseBtn.addEventListener('click', function() {
        let currentValue = parseInt(stokBaruInput.value) || 0;
        stokBaruInput.value = currentValue + 1;
    });

    // Initialize dengan produk yang dipilih
    if (produkSelect.value) {
        produkSelect.dispatchEvent(new Event('change'));
    }
});
</script>
