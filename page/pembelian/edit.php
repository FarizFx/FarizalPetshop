<?php
include "./function/connection.php";
include "./function/currency.php";
include "./function/language.php";

if (!hasPermission('purchase_management')) {
    header('Location: index.php?halaman=beranda');
    exit;
}

$id_pembelian = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_pembelian <= 0) {
    header('Location: index.php?halaman=pembelian');
    exit;
}

// Get pembelian data
$pembelian_query = mysqli_query($connection, "
    SELECT * FROM pembelian WHERE id_pembelian = $id_pembelian
");
$pembelian = mysqli_fetch_assoc($pembelian_query);

if (!$pembelian) {
    header('Location: index.php?halaman=pembelian');
    exit;
}

// Get detail pembelian
$detail_query = mysqli_query($connection, "
    SELECT dp.*, p.nama_produk, p.harga_modal
    FROM detail_pembelian dp
    JOIN produk p ON dp.id_produk = p.id_produk
    WHERE dp.id_pembelian = $id_pembelian
");
$detail_items = [];
while ($row = mysqli_fetch_assoc($detail_query)) {
    $detail_items[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keterangan = mysqli_real_escape_string($connection, $_POST['keterangan'] ?? '');
    $tanggal = $_POST['tanggal'] ?? $pembelian['tanggal'];
    $produk_ids = $_POST['produk_id'] ?? [];
    $qtys = $_POST['qty'] ?? [];
    $hargas = $_POST['harga'] ?? [];
    $satuans = $_POST['satuan'] ?? [];

    // Validate input
    if (empty($produk_ids) || empty($qtys) || empty($hargas)) {
        $error = __('Harap isi semua field yang diperlukan');
    } else {
        // Calculate total
        $total_harga = 0;
        $valid_items = [];

        for ($i = 0; $i < count($produk_ids); $i++) {
            $produk_id = (int)$produk_ids[$i];
            $qty = (int)$qtys[$i];
            $harga = (float)$hargas[$i];

            if ($produk_id > 0 && $qty > 0 && $harga > 0) {
                $subtotal = $qty * $harga;
                $total_harga += $subtotal;
                $valid_items[] = [
                    'produk_id' => $produk_id,
                    'qty' => $qty,
                    'harga' => $harga,
                    'subtotal' => $subtotal
                ];
            }
        }

        if (empty($valid_items)) {
            $error = __('Tidak ada item valid untuk disimpan');
        } else {
            // Start transaction
            mysqli_begin_transaction($connection);

            try {
                // Get old stock changes to revert
                $old_detail_query = mysqli_query($connection, "
                    SELECT id_produk, qty FROM detail_pembelian WHERE id_pembelian = $id_pembelian
                ");
                $old_stocks = [];
                while ($old_row = mysqli_fetch_assoc($old_detail_query)) {
                    $old_stocks[$old_row['id_produk']] = $old_row['qty'];
                }

                // Revert old stock changes
                foreach ($old_stocks as $produk_id => $qty) {
                    mysqli_query($connection, "
                        UPDATE produk SET stok = stok - $qty WHERE id_produk = $produk_id
                    ");
                }

                // Update pembelian
                $update_pembelian = mysqli_query($connection, "
                    UPDATE pembelian SET tanggal = '$tanggal', total_harga = $total_harga, keterangan = '$keterangan'
                    WHERE id_pembelian = $id_pembelian
                ");

                if (!$update_pembelian) {
                    throw new Exception(__('Gagal mengupdate data pembelian'));
                }

                // Delete old detail
                mysqli_query($connection, "DELETE FROM detail_pembelian WHERE id_pembelian = $id_pembelian");

                // Insert new detail and update stock
                foreach ($valid_items as $index => $item) {
                    $satuan = mysqli_real_escape_string($connection, $satuans[$index] ?? 'pcs');
                    $insert_detail = mysqli_query($connection, "
                        INSERT INTO detail_pembelian (id_pembelian, id_produk, qty, harga, subtotal, satuan)
                        VALUES ($id_pembelian, {$item['produk_id']}, {$item['qty']}, {$item['harga']}, {$item['subtotal']}, '$satuan')
                    ");

                    if (!$insert_detail) {
                        throw new Exception(__('Gagal menyimpan detail pembelian'));
                    }

                    // Update stock
                    $update_stock = mysqli_query($connection, "
                        UPDATE produk SET stok = stok + {$item['qty']} WHERE id_produk = {$item['produk_id']}
                    ");

                    if (!$update_stock) {
                        throw new Exception(__('Gagal mengupdate stok produk'));
                    }
                }

                mysqli_commit($connection);
                echo "<script>alert('" . __('Pembelian berhasil diupdate') . "'); window.location.href='index.php?halaman=pembelian';</script>";
                exit;

            } catch (Exception $e) {
                mysqli_rollback($connection);
                $error = $e->getMessage();
            }
        }
    }
}

// Get products for dropdown
$produk_query = mysqli_query($connection, "SELECT id_produk, nama_produk, harga_modal FROM produk ORDER BY nama_produk");
$produk_list = [];
while ($produk = mysqli_fetch_assoc($produk_query)) {
    $produk_list[] = $produk;
}
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><?php echo __('Edit Pembelian'); ?></h3>
                <p class="text-subtitle text-muted"><?php echo __('Edit data pembelian produk'); ?></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?halaman=beranda"><?php echo __('Dashboard'); ?></a></li>
                        <li class="breadcrumb-item"><a href="index.php?halaman=pembelian"><?php echo __('Pembelian'); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo __('Edit'); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?php echo __('Form Edit Pembelian'); ?> #<?php echo $pembelian['id_pembelian']; ?></h5>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" id="pembelianForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tanggal" class="form-label"><?php echo __('Tanggal Pembelian'); ?></label>
                                <input type="datetime-local" class="form-control" id="tanggal" name="tanggal"
                                       value="<?php echo date('Y-m-d\TH:i', strtotime($pembelian['tanggal'])); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="keterangan" class="form-label"><?php echo __('Keterangan'); ?></label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan"
                                       value="<?php echo htmlspecialchars($pembelian['keterangan'] ?? ''); ?>"
                                       placeholder="<?php echo __('Masukkan keterangan pembelian'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo __('Detail Produk'); ?></label>
                        <div id="produkContainer">
                            <?php foreach ($detail_items as $index => $item): ?>
                                <div class="produk-item border rounded p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label"><?php echo __('Produk'); ?></label>
                                            <select class="form-select produk-select" name="produk_id[]" required>
                                                <option value=""><?php echo __('Pilih Produk'); ?></option>
                                                <?php foreach ($produk_list as $produk): ?>
                                                    <option value="<?php echo $produk['id_produk']; ?>"
                                                            data-harga="<?php echo $produk['harga_modal']; ?>"
                                                            <?php echo $produk['id_produk'] == $item['id_produk'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($produk['nama_produk']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label"><?php echo __('Qty'); ?></label>
                                            <input type="number" class="form-control qty-input" name="qty[]"
                                                   value="<?php echo $item['qty']; ?>" min="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label"><?php echo __('Satuan'); ?></label>
                                            <select class="form-select satuan-select" name="satuan[]" required>
                                                <option value="pcs" <?php echo ($item['satuan'] ?? 'pcs') == 'pcs' ? 'selected' : ''; ?>>pcs</option>
                                                <option value="dus" <?php echo ($item['satuan'] ?? 'pcs') == 'dus' ? 'selected' : ''; ?>>dus</option>
                                                <option value="pack" <?php echo ($item['satuan'] ?? 'pcs') == 'pack' ? 'selected' : ''; ?>>pack</option>
                                                <option value="box" <?php echo ($item['satuan'] ?? 'pcs') == 'box' ? 'selected' : ''; ?>>box</option>
                                                <option value="kg" <?php echo ($item['satuan'] ?? 'pcs') == 'kg' ? 'selected' : ''; ?>>kg</option>
                                                <option value="liter" <?php echo ($item['satuan'] ?? 'pcs') == 'liter' ? 'selected' : ''; ?>>liter</option>
                                                <option value="meter" <?php echo ($item['satuan'] ?? 'pcs') == 'meter' ? 'selected' : ''; ?>>meter</option>
                                                <option value="lembar" <?php echo ($item['satuan'] ?? 'pcs') == 'lembar' ? 'selected' : ''; ?>>lembar</option>
                                                <option value="buah" <?php echo ($item['satuan'] ?? 'pcs') == 'buah' ? 'selected' : ''; ?>>buah</option>
                                                <option value="botol" <?php echo ($item['satuan'] ?? 'pcs') == 'botol' ? 'selected' : ''; ?>>botol</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label"><?php echo __('Harga Satuan'); ?></label>
                                            <input type="number" class="form-control harga-input" name="harga[]"
                                                   value="<?php echo $item['harga']; ?>" step="0.01" min="0" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label"><?php echo __('Subtotal'); ?></label>
                                            <input type="text" class="form-control subtotal-display"
                                                   value="<?php echo formatRupiah($item['subtotal']); ?>" readonly>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm remove-produk mt-2">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="addProduk">
                            <i class="bi bi-plus"></i> <?php echo __('Tambah Produk'); ?>
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo __('Total Harga:'); ?></strong>
                                        <strong id="totalHarga"><?php echo formatRupiah($pembelian['total_harga']); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php?halaman=pembelian" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> <?php echo __('Kembali'); ?>
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> <?php echo __('Update Pembelian'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let produkTemplate = `
        <div class="produk-item border rounded p-3 mb-3">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label"><?php echo __('Produk'); ?></label>
                    <select class="form-select produk-select" name="produk_id[]" required>
                        <option value=""><?php echo __('Pilih Produk'); ?></option>
                        <?php foreach ($produk_list as $produk): ?>
                            <option value="<?php echo $produk['id_produk']; ?>" data-harga="<?php echo $produk['harga_modal']; ?>">
                                <?php echo htmlspecialchars($produk['nama_produk']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><?php echo __('Qty'); ?></label>
                    <input type="number" class="form-control qty-input" name="qty[]" min="1" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><?php echo __('Satuan'); ?></label>
                    <select class="form-select satuan-select" name="satuan[]" required>
                        <option value="pcs">pcs</option>
                        <option value="dus">dus</option>
                        <option value="pack">pack</option>
                        <option value="box">box</option>
                        <option value="kg">kg</option>
                        <option value="liter">liter</option>
                        <option value="meter">meter</option>
                        <option value="lembar">lembar</option>
                        <option value="buah">buah</option>
                        <option value="botol">botol</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><?php echo __('Harga Satuan'); ?></label>
                    <input type="number" class="form-control harga-input" name="harga[]" step="0.01" min="0" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label"><?php echo __('Subtotal'); ?></label>
                    <input type="text" class="form-control subtotal-display" readonly>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm remove-produk mt-2">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    // Add new product row
    document.getElementById('addProduk').addEventListener('click', function() {
        document.getElementById('produkContainer').insertAdjacentHTML('beforeend', produkTemplate);
    });

    // Remove product row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-produk') || e.target.parentElement.classList.contains('remove-produk')) {
            const produkItems = document.querySelectorAll('.produk-item');
            if (produkItems.length > 1) {
                e.target.closest('.produk-item').remove();
                calculateTotal();
            } else {
                alert('<?php echo __('Minimal harus ada 1 produk'); ?>');
            }
        }
    });

    // Calculate subtotal and total
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('harga-input')) {
            const row = e.target.closest('.produk-item');
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const harga = parseFloat(row.querySelector('.harga-input').value) || 0;
            const subtotal = qty * harga;

            row.querySelector('.subtotal-display').value = formatRupiah(subtotal);
            calculateTotal();
        }
    });

    // Auto-fill price when product selected
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('produk-select')) {
            const selectedOption = e.target.selectedOptions[0];
            const harga = selectedOption.getAttribute('data-harga') || 0;
            const row = e.target.closest('.produk-item');
            row.querySelector('.harga-input').value = harga;

            // Trigger calculation
            const qtyInput = row.querySelector('.qty-input');
            if (qtyInput.value) {
                qtyInput.dispatchEvent(new Event('input'));
            }
        }
    });

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal-display').forEach(function(el) {
            const value = el.value.replace(/[^\d]/g, '');
            total += parseFloat(value) || 0;
        });
        document.getElementById('totalHarga').textContent = formatRupiah(total);
    }

    function formatRupiah(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    }

    // Calculate initial total
    calculateTotal();
});
</script>
