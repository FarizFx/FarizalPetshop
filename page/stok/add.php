<?php
include "./function/connection.php";

try {
    $message = "";
    $success = false;
    $error = false;

    if (isset($_POST['submit'])) {
        $id_produk = $_POST['id_produk'];
        $jumlah_tambah = $_POST['jumlah_tambah'];
        $keterangan = htmlspecialchars($_POST['keterangan']);

        // Validasi input
        if (empty($id_produk) || empty($jumlah_tambah) || $jumlah_tambah <= 0) {
            $message = "Data tidak valid";
            $error = true;
        } else {
            // Update stok produk
            $query = mysqli_query($connection, "
                UPDATE produk SET stok = stok + $jumlah_tambah
                WHERE id_produk = '$id_produk'
            ");

            if ($query) {
                // Catat log penambahan stok (opsional, bisa ditambahkan tabel log_stok nanti)
                $message = "Stok berhasil ditambahkan";
                $success = true;
            } else {
                $message = "Gagal menambahkan stok: " . mysqli_error($connection);
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
                window.location.href = 'index.php?halaman=tambah_stok';
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
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Tambah Stok</h3>
                <p class="text-subtitle text-muted">
                    Formulir Penambahan Stok Produk
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=stok">Stok</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Tambah Stok
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
                                <option value="<?= $produk['id_produk'] ?>" data-stok-sekarang="<?= $produk['stok'] ?>">
                                    <?= htmlspecialchars($produk['nama_produk']) ?> -
                                    Kategori: <?= htmlspecialchars($produk['nama_kategori'] ?? '-') ?> -
                                    Stok Saat Ini: <?= $produk['stok'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="stok_sekarang" class="form-label">Stok Saat Ini</label>
                        <input type="text" class="form-control" id="stok_sekarang" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_tambah" class="form-label">Jumlah Penambahan</label>
                        <input type="number" class="form-control" id="jumlah_tambah" name="jumlah_tambah"
                               placeholder="Masukkan jumlah stok yang akan ditambahkan" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label for="stok_akhir" class="form-label">Stok Setelah Penambahan</label>
                        <input type="text" class="form-control" id="stok_akhir" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                  placeholder="Masukkan keterangan penambahan stok"></textarea>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-success" name="submit">
                            <i class="bi bi-plus-circle"></i> Tambah Stok
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
    const stokSekarangInput = document.getElementById('stok_sekarang');
    const jumlahTambahInput = document.getElementById('jumlah_tambah');
    const stokAkhirInput = document.getElementById('stok_akhir');

    function updateStokInfo() {
        const selectedOption = produkSelect.options[produkSelect.selectedIndex];
        if (selectedOption.value) {
            const stokSekarang = parseInt(selectedOption.getAttribute('data-stok-sekarang')) || 0;
            stokSekarangInput.value = stokSekarang;
            calculateStokAkhir();
        } else {
            stokSekarangInput.value = '';
            stokAkhirInput.value = '';
        }
    }

    function calculateStokAkhir() {
        const stokSekarang = parseInt(stokSekarangInput.value) || 0;
        const jumlahTambah = parseInt(jumlahTambahInput.value) || 0;
        const stokAkhir = stokSekarang + jumlahTambah;
        stokAkhirInput.value = stokAkhir;
    }

    produkSelect.addEventListener('change', updateStokInfo);
    jumlahTambahInput.addEventListener('input', calculateStokAkhir);

    // Initialize
    updateStokInfo();
});
</script>
