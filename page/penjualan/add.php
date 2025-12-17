<?php
include "./function/connection.php";
include "./function/language.php";

try {
    $message = "";
    $success = FALSE;
    $error = FALSE;

    if(isset($_POST['submit'])) {
        $tanggal = date('Y-m-d H:i:s');
        $total_harga = 0;
    
        if(isset($_POST['produk'])) {
            foreach($_POST['produk'] as $id_produk => $item) {
                if(isset($item['selected']) && $item['selected'] == 'on') {
                    $total_harga += $item['harga'] * $item['qty'];
                }
            }
        }
    
        $query_penjualan = mysqli_query($connection, "
            INSERT INTO penjualan (tanggal, total_harga) 
            VALUES ('$tanggal', '$total_harga')
        ");

        if ($query_penjualan == TRUE) {
            $message = "Berhasil menambahkan data";
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
            window.location.href = 'index.php?halaman=penjualan';
        })
        </script>
        ";
        } else {
            $message = "Gagal menambahkan data";
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
            window.location.href = 'index.php?halaman=penjualan';
        })
        </script>
        ";
        }
    
        $id_penjualan = mysqli_insert_id($connection);
    
        if(isset($_POST['produk'])) {
            foreach($_POST['produk'] as $id_produk => $item) {
                if(isset($item['selected']) && $item['selected'] == 'on') {
                    $qty = $item['qty'];
                    $harga = $item['harga'];
                    $subtotal = $harga * $qty;

                    // Ambil harga_modal dari produk
                    $query_modal = mysqli_query($connection, "SELECT harga_modal FROM produk WHERE id_produk = '$id_produk'");
                    $produk_data = mysqli_fetch_assoc($query_modal);
                    $harga_modal = $produk_data['harga_modal'];

                    mysqli_query($connection, "
                        INSERT INTO detail_penjualan (id_penjualan, id_produk, qty, harga, harga_jual, harga_modal, subtotal) VALUES ('$id_penjualan', '$id_produk', '$qty', '$harga', '$harga', '$harga_modal', '$subtotal')
                    ");

                    mysqli_query($connection, "
                        UPDATE produk SET stok = stok - $qty
                        WHERE id_produk = '$id_produk'
                    ");
                }
            }
        }
    
        exit;
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
    })
    </script>
    ";
}

$produk = mysqli_query($connection, "
    SELECT p.id_produk, p.nama_produk, p.harga_jual, p.stok, k.nama_kategori
    FROM produk p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE p.stok > 0
");
?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><?= __('Add Sales') ?></h3>
                <p class="text-subtitle text-muted">
                    <?= __('Formulir Transaksi Penjualan Baru') ?>
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=penjualan">Penjualan</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Tambah Penjualan
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <form action="" method="post" id="form-penjualan">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= __('Date') ?></label>
                                <input type="text" class="form-control" value="<?= date('d/m/Y H:i:s') ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?= __('Total Price') ?></label>
                                <input type="text" class="form-control" id="display-total" value="Rp 0" readonly>
                                <input type="hidden" name="total_harga" id="total-harga" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="5%"><?= __('Add') ?></th>
                                    <th><?= __('Product Name') ?></th>
                                    <th><?= __('Category') ?></th>
                                    <th width="12%"><?= __('Unit Price') ?></th>
                                    <th width="10%"><?= __('Stock') ?></th>
                                    <th width="12%"><?= __('Quantity') ?></th>
                                    <th width="12%"><?= __('Subtotal') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($p = mysqli_fetch_assoc($produk)): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" name="produk[<?= $p['id_produk'] ?>][selected]" 
                                                   class="form-check-input produk-check">
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($p['nama_produk']) ?>
                                            <input type="hidden" name="produk[<?= $p['id_produk'] ?>][nama]" 
                                                   value="<?= htmlspecialchars($p['nama_produk']) ?>">
                                        </td>
                                        <td><?= htmlspecialchars($p['nama_kategori']) ?></td>
                                        <td>
                                            Rp <?= number_format($p['harga_jual'], 0, ',', '.') ?>
                                            <input type="hidden" name="produk[<?= $p['id_produk'] ?>][harga]"
                                                   value="<?= $p['harga_jual'] ?>" class="harga-produk">
                                        </td>
                                        <td><?= $p['stok'] ?></td>
                                        <td>
                                            <input type="number" name="produk[<?= $p['id_produk'] ?>][qty]" 
                                                   class="form-control qty" min="1" max="<?= $p['stok'] ?>" value="1" 
                                                   data-stok="<?= $p['stok'] ?>" disabled>
                                        </td>
                                        <td class="subtotal">
                                            Rp 0
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" name="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-save"></i> <?= __('Save') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function formatRupiah(angka) {
        return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function hitungTotal() {
        let total = 0;
        
        document.querySelectorAll('.produk-check:checked').forEach(function(checkbox) {
            const row = checkbox.closest('tr');
            const harga = parseFloat(row.querySelector('.harga-produk').value);
            const qty = parseInt(row.querySelector('.qty').value) || 0;
            const subtotal = harga * qty;
            
            row.querySelector('.subtotal').textContent = formatRupiah(subtotal);
            total += subtotal;
        });
        
        document.getElementById('display-total').value = formatRupiah(total);
        document.getElementById('total-harga').value = total;
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('produk-check')) {
            const qtyInput = e.target.closest('tr').querySelector('.qty');
            qtyInput.disabled = !e.target.checked;
            if (!e.target.checked) {
                qtyInput.value = 1;
            }
            hitungTotal();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty')) {
            const max = parseInt(e.target.dataset.stok);
            const value = parseInt(e.target.value) || 0;
            
            if (value > max) {
                e.target.value = max;
                alert('Jumlah melebihi stok yang tersedia');
            }
            
            hitungTotal();
        }
    });

    hitungTotal();
});
</script>