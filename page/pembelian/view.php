<?php
include "./function/connection.php";
include "./function/currency.php";
include "./function/language.php";

if (!hasPermission('purchase_management')) {
    header('Location: index.php?halaman=beranda');
    exit;
}

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_pembelian = (int)$_GET['id'];

    // Start transaction
    mysqli_begin_transaction($connection);

    try {
        // Delete detail pembelian first
        $delete_detail = mysqli_query($connection, "DELETE FROM detail_pembelian WHERE id_pembelian = $id_pembelian");

        // Delete pembelian
        $delete_pembelian = mysqli_query($connection, "DELETE FROM pembelian WHERE id_pembelian = $id_pembelian");

        if ($delete_detail && $delete_pembelian) {
            mysqli_commit($connection);
            echo "<script>alert('Pembelian berhasil dihapus'); window.location.href='index.php?halaman=pembelian';</script>";
        } else {
            mysqli_rollback($connection);
            echo "<script>alert('Gagal menghapus pembelian');</script>";
        }
    } catch (Exception $e) {
        mysqli_rollback($connection);
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

// Get pembelian data with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$where_clause = '';
if (!empty($search)) {
    $where_clause = "WHERE p.id_pembelian LIKE '%$search%' OR p.keterangan LIKE '%$search%'";
}

// Get total records
$total_query = mysqli_query($connection, "SELECT COUNT(*) as total FROM pembelian p $where_clause");
$total_records = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_records / $limit);

// Get pembelian data
$query = "
    SELECT p.*, COUNT(dp.id_detail) as jumlah_item
    FROM pembelian p
    LEFT JOIN detail_pembelian dp ON p.id_pembelian = dp.id_pembelian
    $where_clause
    GROUP BY p.id_pembelian
    ORDER BY p.tanggal DESC
    LIMIT $offset, $limit
";
$result = mysqli_query($connection, $query);
?>

<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3><?php echo __('Daftar Pembelian'); ?></h3>
                <p class="text-subtitle text-muted"><?php echo __('Kelola data pembelian produk'); ?></p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?halaman=beranda"><?php echo __('Dashboard'); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo __('Pembelian'); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title"><?php echo __('Data Pembelian'); ?></h5>
                    <a href="index.php?halaman=tambah_pembelian" class="btn btn-primary">
                        <i class="bi bi-plus"></i> <?php echo __('Tambah Pembelian'); ?>
                    </a>
                </div>
            </div>

            <!-- Search Form -->
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <input type="hidden" name="halaman" value="pembelian">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" placeholder="<?php echo __('Cari berdasarkan ID atau keterangan...'); ?>" value="<?php echo htmlspecialchars($search); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><?php echo __('ID Pembelian'); ?></th>
                                <th><?php echo __('Tanggal'); ?></th>
                                <th><?php echo __('Jumlah Item'); ?></th>
                                <th><?php echo __('Total Harga'); ?></th>
                                <th><?php echo __('Keterangan'); ?></th>
                                <th><?php echo __('Aksi'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $row['id_pembelian']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                                        <td><?php echo $row['jumlah_item']; ?> item</td>
                                        <td><?php echo formatRupiah($row['total_harga']); ?></td>
                                        <td><?php echo htmlspecialchars($row['keterangan'] ?? '-'); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="index.php?halaman=detail_pembelian&id=<?php echo $row['id_pembelian']; ?>" class="btn btn-sm btn-info mx-1 rounded" title="Detail">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="index.php?halaman=ubah_pembelian&id=<?php echo $row['id_pembelian']; ?>" class="btn btn-sm btn-warning mx-1 rounded" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id_pembelian']; ?>)" class="btn btn-sm btn-danger mx-1 rounded" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center"><?php echo __('Tidak ada data pembelian'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?halaman=pembelian&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?halaman=pembelian&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?halaman=pembelian&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<script>
function confirmDelete(id) {
    if (confirm('<?php echo __('Apakah Anda yakin ingin menghapus pembelian ini?'); ?>')) {
        window.location.href = 'index.php?halaman=pembelian&action=delete&id=' + id;
    }
}
</script>
