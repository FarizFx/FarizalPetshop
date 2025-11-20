<?php
if(isset($_GET['modal']) && $_GET['modal'] == '1') {
    ob_start(); // Mulai output buffering
    if (!defined('MODAL_REQUEST')) {
        define('MODAL_REQUEST', true); // Flag khusus modal
    }
}

if (isset($_GET['halaman'])) {
    $halaman = $_GET['halaman'];

    // Handle request modal
    if(defined('MODAL_REQUEST')) {
        if($halaman == 'detail_penjualan' && isset($_GET['id'])) {
            include "page/penjualan/detail.php";
            $content = ob_get_clean(); // Ambil konten dan bersihkan buffer
            echo $content;
            exit;
        }
        // Jika bukan modal valid
        header('HTTP/1.0 400 Bad Request');
        echo 'Invalid modal request';
        exit;
    }


    // Main routing
    switch ($halaman) {
        case 'beranda':
            include "page/index.php";
            break;
        case 'logout':
            include "page/logout.php";
            break;
        case 'profile':
            include "page/profile.php";
            break;
        case 'edit-profile':
            include "page/edit-profile.php";
            break;
        case 'change-profile':
            include "page/change-profile.php";
            break;
        case 'change-password':
            include "page/change-password.php";
            break;
        case 'settings':
            include "settings.php";
            break;
        case 'logout':
            include "page/logout.php";
            break;
        case 'kategori':
            include "page/kategori/view.php";
            break;
        case 'tambah_kategori':
            include "page/kategori/add.php";
            break;
        case 'ubah_kategori':
            include "page/kategori/edit.php";
            break;
        case 'hapus_kategori':
            include "page/kategori/delete.php";
            break;
        case 'produk':
            include "page/produk/view.php";
            break;
        case 'tambah_produk':
            include "page/produk/add.php";
            break;
        case 'ubah_produk':
            include "page/produk/edit.php";
            break;
        case 'hapus_produk':
            include "page/produk/delete.php";
            break;
        case 'penjualan':
            include "page/penjualan/view.php";
            break;
        case 'hapus_penjualan':
            include "page/penjualan/delete.php";
            break;
        case 'tambah_penjualan':
            include "page/penjualan/add.php";
            break;
        case 'detail_penjualan':
            include "page/penjualan/detail.php";
            break;
        case 'cetak_struk':
            include "page/penjualan/cetak.php";
            break;
        case 'users':
            include "page/users.php";
            break;
        case 'roles':
            include "page/roles.php";
            break;
        case 'reports':
            include "page/reports.php";
            break;
        case 'stok':
            include "page/stok/view.php";
            break;
        case 'ubah_stok':
            include "page/stok/edit.php";
            break;
        case 'pembelian':
            include "page/pembelian/view.php";
            break;
        case 'tambah_pembelian':
            include "page/pembelian/add.php";
            break;
        case 'ubah_pembelian':
            include "page/pembelian/edit.php";
            break;
        case 'detail_pembelian':
            include "page/pembelian/detail.php";
            break;
        default:
            include "page/error.php";
    }
} else {
    include "page/index.php";
}
?>
