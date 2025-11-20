<?php
include "./function/connection.php";

// Handle AJAX requests for stock updates first, before any output
if (isset($_POST['action']) && $_POST['action'] == 'update_stock') {
    // Suppress warnings and clean output buffer for pure JSON response
    error_reporting(0);
    ob_clean();
    header('Content-Type: application/json');

    $id_produk = $_POST['id_produk'];
    $new_stock = (int)$_POST['new_stock'];

    if ($new_stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Stok tidak boleh negatif']);
        exit;
    }

    $query = mysqli_query($connection, "UPDATE produk SET stok = $new_stock WHERE id_produk = '$id_produk'");

    if ($query) {
        echo json_encode(['success' => true, 'message' => 'Stok berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui stok: ' . mysqli_error($connection)]);
    }
    exit;
}

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

$query = mysqli_query($connection, "
    SELECT p.*, k.nama_kategori
    FROM produk p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    ORDER BY p.nama_produk ASC
");
?>
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Manajemen Stok</h3>
                <p class="text-subtitle text-muted">
                    Halaman Tampil Data Stok Produk
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.php?halaman=stok">Stok</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Daftar Stok
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="section">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Harga Jual</th>
                                <th>Harga Modal</th>
                                <th>Profit per Unit</th>
                                <th>Stok Saat Ini</th>
                                <th>Total Nilai Stok</th>
                                <th>Total Modal Stok</th>
                                <th>Estimasi Profit Stok</th>
                                <th>Status Stok</th>
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
                                        <td>Rp <?= number_format($data['harga_jual'], 0, ',', '.') ?></td>
                                        <td>Rp <?= number_format($data['harga_modal'], 0, ',', '.') ?></td>
                                        <td class="<?= $profit_per_unit >= 0 ? 'text-success' : 'text-danger' ?>">
                                            Rp <?= number_format($profit_per_unit, 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <button class="btn btn-outline-secondary btn-sm decrease-stock" type="button"
                                                        data-id="<?= $data['id_produk'] ?>" data-current="<?= $data['stok'] ?>">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" class="form-control text-center stock-input"
                                                       value="<?= $data['stok'] ?>" min="0"
                                                       data-id="<?= $data['id_produk'] ?>" data-original="<?= $data['stok'] ?>">
                                                <button class="btn btn-outline-secondary btn-sm increase-stock" type="button"
                                                        data-id="<?= $data['id_produk'] ?>" data-current="<?= $data['stok'] ?>">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-end">Rp <?= number_format($total_nilai_stok, 0, ',', '.') ?></td>
                                        <td class="text-end">Rp <?= number_format($total_modal_stok, 0, ',', '.') ?></td>
                                        <td class="text-end <?= $estimasi_profit_stok >= 0 ? 'text-success' : 'text-danger' ?>">
                                            Rp <?= number_format($estimasi_profit_stok, 0, ',', '.') ?>
                                        </td>
                                        <td>
                                            <?php if ($data['stok'] == 0): ?>
                                                <span class="badge bg-danger">Habis</span>
                                            <?php elseif ($data['stok'] <= 10): ?>
                                                <span class="badge bg-warning text-dark">Sedikit</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-success btn-sm save-stock" data-id="<?= $data['id_produk'] ?>" style="display: none;">
                                                <i class="bi bi-check"></i> Simpan
                                            </button>
                                            <button class="btn btn-secondary btn-sm cancel-stock" data-id="<?= $data['id_produk'] ?>" style="display: none;">
                                                <i class="bi bi-x"></i> Batal
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="12" class="text-center">Tidak ada data produk</td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle increase stock button
    document.querySelectorAll('.increase-stock').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const input = document.querySelector(`.stock-input[data-id="${id}"]`);
            const saveBtn = document.querySelector(`.save-stock[data-id="${id}"]`);
            const cancelBtn = document.querySelector(`.cancel-stock[data-id="${id}"]`);

            let currentValue = parseInt(input.value) || 0;
            input.value = currentValue + 1;

            // Show save/cancel buttons
            saveBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'inline-block';

            updateStatusBadge(id, currentValue + 1);
        });
    });

    // Handle decrease stock button
    document.querySelectorAll('.decrease-stock').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const input = document.querySelector(`.stock-input[data-id="${id}"]`);
            const saveBtn = document.querySelector(`.save-stock[data-id="${id}"]`);
            const cancelBtn = document.querySelector(`.cancel-stock[data-id="${id}"]`);

            let currentValue = parseInt(input.value) || 0;
            if (currentValue > 0) {
                input.value = currentValue - 1;

                // Show save/cancel buttons
                saveBtn.style.display = 'inline-block';
                cancelBtn.style.display = 'inline-block';

                updateStatusBadge(id, currentValue - 1);
            }
        });
    });

    // Handle manual input change
    document.querySelectorAll('.stock-input').forEach(input => {
        input.addEventListener('input', function() {
            const id = this.getAttribute('data-id');
            const saveBtn = document.querySelector(`.save-stock[data-id="${id}"]`);
            const cancelBtn = document.querySelector(`.cancel-stock[data-id="${id}"]`);

            // Show save/cancel buttons
            saveBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'inline-block';

            const newValue = parseInt(this.value) || 0;
            updateStatusBadge(id, newValue);
        });
    });

    // Handle save button
    document.querySelectorAll('.save-stock').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const input = document.querySelector(`.stock-input[data-id="${id}"]`);
            const saveBtn = this;
            const cancelBtn = document.querySelector(`.cancel-stock[data-id="${id}"]`);

            const newStock = parseInt(input.value) || 0;

            // Send AJAX request to update stock
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_stock&id_produk=${id}&new_stock=${newStock}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update original value
                    input.setAttribute('data-original', newStock);

                    // Hide save/cancel buttons
                    saveBtn.style.display = 'none';
                    cancelBtn.style.display = 'none';

                    // Show success message
                    Swal.fire({
                        title: 'Berhasil',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message,
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Terjadi kesalahan saat memperbarui stok',
                    icon: 'error'
                });
            });
        });
    });

    // Handle cancel button
    document.querySelectorAll('.cancel-stock').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const input = document.querySelector(`.stock-input[data-id="${id}"]`);
            const saveBtn = document.querySelector(`.save-stock[data-id="${id}"]`);
            const cancelBtn = this;

            // Reset to original value
            const originalValue = input.getAttribute('data-original');
            input.value = originalValue;

            // Hide save/cancel buttons
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';

            updateStatusBadge(id, parseInt(originalValue));
        });
    });

    function updateStatusBadge(productId, stockValue) {
        const row = document.querySelector(`.stock-input[data-id="${productId}"]`).closest('tr');
        const statusCell = row.querySelector('td:nth-child(11)'); // Status column (adjusted for new columns)

        let badgeClass = 'bg-success';
        let badgeText = 'Tersedia';

        if (stockValue == 0) {
            badgeClass = 'bg-danger';
            badgeText = 'Habis';
        } else if (stockValue <= 10) {
            badgeClass = 'bg-warning text-dark';
            badgeText = 'Sedikit';
        }

        statusCell.innerHTML = `<span class="badge ${badgeClass}">${badgeText}</span>`;
    }
});
</script>
