<?php
include "./function/connection.php";
include "./function/currency.php";
include "./function/language.php";

if (!hasPermission('purchase_management')) {
    header('Location: index.php?halaman=beranda');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $keterangan = mysqli_real_escape_string($connection, $_POST['keterangan'] ?? '');
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d H:i:s');
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
                // Insert pembelian
                $insert_pembelian = mysqli_query($connection, "
                    INSERT INTO pembelian (tanggal, total_harga, keterangan)
                    VALUES ('$tanggal', $total_harga, '$keterangan')
                ");

                if (!$insert_pembelian) {
                    throw new Exception(__('Gagal menyimpan data pembelian'));
                }

                $id_pembelian = mysqli_insert_id($connection);

                // Insert detail pembelian and update stock
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
                echo "<script>alert('" . __('Pembelian berhasil ditambahkan') . "'); window.location.href='index.php?halaman=pembelian';</script>";
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
                <h3><?php echo __('Tambah Pembelian'); ?></h3>
                <p class="text-subtitle text-muted"><?php echo __('Tambah data pembelian produk baru'); ?></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?halaman=beranda"><?php echo __('Dashboard'); ?></a></li>
                        <li class="breadcrumb-item"><a href="index.php?halaman=pembelian"><?php echo __('Pembelian'); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo __('Tambah'); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><?php echo __('Form Tambah Pembelian'); ?></h5>
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
                                       value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="keterangan" class="form-label"><?php echo __('Keterangan'); ?></label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan"
                                       placeholder="<?php echo __('Masukkan keterangan pembelian'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?php echo __('Detail Produk'); ?></label>
                        <div id="produkContainer">
                            <div class="produk-item border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-3">
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
                                        <button type="button" class="btn btn-danger btn-sm remove-produk mt-3">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
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
                                        <strong id="totalHarga">Rp 0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="index.php?halaman=pembelian" class="btn btn-secondary m-2">
                            <i class="bi bi-arrow-left"></i> <?php echo __('Kembali'); ?>
                        </a>
                        <button type="submit" class="btn btn-primary m-2">
                            <i class="bi bi-save"></i> <?php echo __('Simpan Pembelian'); ?>
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
                <div class="col-md-3">
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
});
</script>
