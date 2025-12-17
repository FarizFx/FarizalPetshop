<?php
// Aktifkan laporan error PHP untuk debugging. Hapus ini saat production.
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// ----------------------------------------------------
// 1. Inisialisasi Sesi & Proteksi Akses
// ----------------------------------------------------
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah pengguna sudah login
if (!isset($_SESSION['nama'])) {
    header('Location: login.php');
    exit();
}

// ----------------------------------------------------
// 2. Load Dependensi
// ----------------------------------------------------
require_once './function/connection.php'; 
include './function/language.php';      

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= __('Change Password') ?> - Farizal Petshop</title>
    <link rel="stylesheet" href="../assets/compiled/css/app.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white p-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-lock me-2"></i><?= __('Change Password') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <form method="POST" action="page/process_change_password.php">
                                    <div class="mb-3 mt-4">
                                        <label for="current_password" class="form-label"><?= __('Current Password') ?> *</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="<?= __('Current password') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label"><?= __('New Password') ?> *</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="<?= __('New password') ?>">
                                    </div>
                                    <div class="mb-3 pb-3">
                                        <label for="confirm_password" class="form-label"><?= __('Confirm New Password') ?> *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="<?= __('Confirm password') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="showPassword">
                                            <label class="form-check-label" for="showPassword">
                                                <?= __('Show Password') ?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 pt-5 mt-5">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-circle me-1"></i><?= __('Change Password') ?>
                                        </button>
                                        <a href="index.php?halaman=profile" class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i><?= __('Back') ?>
                                        </a>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex flex-column justify-content-center h-100 mt-3">
                                    <div class="text-center">
                                        <i class="bi bi-shield-check text-primary" style="font-size: 4rem;"></i>
                                        <h5 class="mt-3"><?= __('Password Security Tips') ?></h5>
                                        <ul class="list-unstyled text-start mt-3">
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Minimum 8 characters') ?></li>
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Combination of uppercase & lowercase letters') ?></li>
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Include numbers & symbols') ?></li>
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Do not use personal information') ?></li>
                                            <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i><?= __('Change password regularly') ?></li>
                                        </ul>
                                        <div class="alert alert-warning mt-4">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong><?= __('Important!') ?></strong> <?= __('Do not share your password with anyone.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('showPassword').addEventListener('change', function() {
            const passwordFields = ['current_password', 'new_password', 'confirm_password'];
            const type = this.checked ? 'text' : 'password';

            passwordFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.type = type;
                }
            });
        });
    </script>
</body>
</html>
