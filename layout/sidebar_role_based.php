<?php

include "function/language.php";

// Get allowed menu items based on user role (using global role_manager)
$allowed_menu_items = $GLOBALS['role_manager']->getAllowedMenuItems();

// Set active states
$beranda = false;
$kategori = false;
$produk = false;
$penjualan = false;
$tambah = false;
$ubah = false;
$users = false;
$roles = false;
$settings = false;
$profile = false;

if (isset($_GET['halaman'])) {
    $halaman = $_GET['halaman'];
    switch ($halaman) {
        case 'beranda':
            $beranda = true;
            break;
        case 'kategori':
            $kategori = true;
            break;
        case 'produk':
            $produk= true;
            break;
        case 'penjualan':
            $penjualan = true;
            break;
        case 'tambah_produk':
            $tambah_produk = true;
            break;
        case 'tambah_penjualan':
            $tambah_penjualan = true;
            break;
        case 'tambah_kategori':
            $tambah_kategori = true;
            break;
        case 'ubah_kategori':
            $ubah_kategori = true;
            break;
        case 'ubah_produk':
            $ubah_produk = true;
            break;
        case 'ubah_penjualan':
            $ubah_penjualan = true;
            break;
        case 'users':
            $users = true;
            break;
        case 'roles':
            $roles = true;
            break;
        case 'reports':
            $reports = true;
            break;
        case 'settings':
            $settings = true;
            break;
        case 'profile':
            $profile = true;
            break;
        default:
            $beranda = false;
            $kategori = false;
            $produk = false;
            $penjualan = false;
            $tambah = false;
            $ubah = false;
    }
} else {
    $beranda = true;
}

?>

<div id="sidebar">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header position-relative">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <a href="index.php?halaman=beranda"><img src="./assets/compiled/svg/logo.svg" alt="Logo" srcset="" /></a>
                </div>
                <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--system-uicons" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 21 21">
                        <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.5 14.5c2.219 0 4-1.763 4-3.982a4.003 4.003 0 0 0-4-4.018c-2.219 0-4 1.781-4 4c0 2.219 1.781 4 4 4zM4.136 4.136L5.55 5.55m9.9 9.9l1.414 1.414M1.5 10.5h2m14 0h2M4.135 16.863L5.55 15.45m9.899-9.9l1.414-1.415M10.5 19.5v-2m0-14v-2" opacity=".3"></path>
                            <g transform="translate(-210 -1)">
                                <path d="M220.5 2.5v2m6.5.5l-1.5 1.5"></path>
                                <circle cx="220.5" cy="11.5" r="4"></circle>
                                <path d="m214 5l1.5 1.5m5 14v-2m6.5-.5l-1.5-1.5M214 18l1.5-1.5m-4-5h2m14 0h2"></path>
                            </g>
                        </g>
                    </svg>
                    <div class="form-check form-switch fs-6">
                        <input class="form-check-input me-0" type="checkbox" id="toggle-dark" style="cursor: pointer" />
                        <label class="form-check-label"></label>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--mdi" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                        <path fill="currentColor" d="m17.75 4.09l-2.53 1.94l.91 3.06l-2.63-1.81l-2.63 1.81l.91-3.06l-2.53-1.94L12.44 4l1.06-3l1.06 3l3.19.09m3.5 6.91l-1.64 1.25l.59 1.98l-1.7-1.17l-1.7 1.17l.59-1.98L15.75 11l2.06-.05L18.5 9l.69 1.95l2.06.05m-2.28 4.95c.83-.08 1.72 1.1 1.19 1.85c-.32.45-.66.87-1.08 1.27C15.17 23 8.84 23 4.94 19.07c-3.91-3.9-3.91-10.24 0-14.14c.4-.4.82-.76 1.27-1.08c.75-.53 1.93.36 1.85 1.19c-.27 2.86.69 5.83 2.89 8.02a9.96 9.96 0 0 0 8.02 2.89m-1.64 2.02a12.08 12.08 0 0 1-7.8-3.47c-2.17-2.19-3.33-5-3.49-7.82c-2.81 3.14-2.7 7.96.31 10.98c3.02 3.01 7.84 3.12 10.98.31Z">
                        </path>
                    </svg>
                </div>
                <div class="sidebar-toggler x">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>
        <div class="sidebar-menu">
            <ul class="menu">
                <li class="sidebar-title"><?= __('Menu') ?></li>

                <!-- Dashboard -->
                <?php if (isset($allowed_menu_items['dashboard'])): ?>
                    <li class="sidebar-item <?= $beranda ? 'active' : '' ?>">
                        <a href="<?= $allowed_menu_items['dashboard']['url'] ?>" class="sidebar-link">
                            <i class="<?= $allowed_menu_items['dashboard']['icon'] ?>"></i>
                            <span><?= $allowed_menu_items['dashboard']['title'] ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Data Section -->
                <?php if (isset($allowed_menu_items['categories']) || isset($allowed_menu_items['products']) || isset($allowed_menu_items['sales'])): ?>
                    <li class="sidebar-title"><?= __('Data') ?></li>

                    <?php if (isset($allowed_menu_items['categories'])): ?>
                        <li class="sidebar-item <?= $kategori || $tambah_kategori || $ubah_kategori ? 'active' : '' ?>">
                            <a href="<?= $allowed_menu_items['categories']['url'] ?>" class="sidebar-link">
                                <i class="<?= $allowed_menu_items['categories']['icon'] ?>"></i>
                                <span><?= $allowed_menu_items['categories']['title'] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($allowed_menu_items['products'])): ?>
                        <li class="sidebar-item <?= $produk || $tambah_produk || $ubah_produk ? 'active' : '' ?>">
                            <a href="<?= $allowed_menu_items['products']['url'] ?>" class="sidebar-link">
                                <i class="<?= $allowed_menu_items['products']['icon'] ?>"></i>
                                <span><?= $allowed_menu_items['products']['title'] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($allowed_menu_items['sales'])): ?>
                        <li class="sidebar-item <?= $penjualan || $tambah_penjualan || $ubah_penjualan ? 'active' : '' ?>">
                            <a href="<?= $allowed_menu_items['sales']['url'] ?>" class="sidebar-link">
                                <i class="<?= $allowed_menu_items['sales']['icon'] ?>"></i>
                                <span><?= $allowed_menu_items['sales']['title'] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Management Section -->
                <?php if (isset($allowed_menu_items['users']) || isset($allowed_menu_items['roles'])): ?>
                    <li class="sidebar-title"><?= __('Management') ?></li>

                    <?php if (isset($allowed_menu_items['users'])): ?>
                        <li class="sidebar-item <?= $users ? 'active' : '' ?>">
                            <a href="<?= $allowed_menu_items['users']['url'] ?>" class="sidebar-link">
                                <i class="<?= $allowed_menu_items['users']['icon'] ?>"></i>
                                <span><?= $allowed_menu_items['users']['title'] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($allowed_menu_items['roles'])): ?>
                        <li class="sidebar-item <?= $roles ? 'active' : '' ?>">
                            <a href="<?= $allowed_menu_items['roles']['url'] ?>" class="sidebar-link">
                                <i class="<?= $allowed_menu_items['roles']['icon'] ?>"></i>
                                <span><?= $allowed_menu_items['roles']['title'] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- System Section -->
                <?php if (isset($allowed_menu_items['reports']) || isset($allowed_menu_items['settings'])): ?>
                    <li class="sidebar-title"><?= __('System') ?></li>

                    <?php if (isset($allowed_menu_items['reports'])): ?>
                        <li class="sidebar-item <?= $reports ? 'active' : '' ?>">
                            <a href="<?= $allowed_menu_items['reports']['url'] ?>" class="sidebar-link">
                                <i class="<?= $allowed_menu_items['reports']['icon'] ?>"></i>
                                <span><?= $allowed_menu_items['reports']['title'] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($allowed_menu_items['settings'])): ?>
                        <li class="sidebar-item <?= $settings ? 'active' : '' ?>">
                            <a href="<?= $allowed_menu_items['settings']['url'] ?>" class="sidebar-link">
                                <i class="<?= $allowed_menu_items['settings']['icon'] ?>"></i>
                                <span><?= $allowed_menu_items['settings']['title'] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- User Section -->
                <?php if (isset($allowed_menu_items['profile'])): ?>
                    <li class="sidebar-title"><?= __('Account') ?></li>

                    <li class="sidebar-item <?= $profile ? 'active' : '' ?>">
                        <a href="<?= $allowed_menu_items['profile']['url'] ?>" class="sidebar-link">
                            <i class="<?= $allowed_menu_items['profile']['icon'] ?>"></i>
                            <span><?= $allowed_menu_items['profile']['title'] ?></span>
                        </a>
                    </li>
                <?php endif; ?>

            </ul>
        </div>
    </div>
</div>
