<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['nama'])) {
    header('Location: login.php');
    exit();
}

include __DIR__ . "/function/connection.php";
include __DIR__ . "/function/language.php";

$message = '';
$active_tab = $_GET['tab'] ?? 'store';

// Load existing settings
$settings = [];
$stmt = $connection->prepare("SELECT * FROM settings WHERE id=1");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $settings = $result->fetch_assoc();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submissions
    if (isset($_POST['update_store'])) {
        // Validate store settings
        $store_name = trim($_POST['store_name'] ?? '');
        $store_address = trim($_POST['store_address'] ?? '');
        $store_phone = trim($_POST['store_phone'] ?? '');
        $store_email = trim($_POST['store_email'] ?? '');
        $store_description = trim($_POST['store_description'] ?? '');

        $errors = [];

        if (empty($store_name)) {
            $errors[] = __('Store name cannot be empty.');
        }

        if (!empty($store_email) && !filter_var($store_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = __('Invalid email format.');
        }

        if (!empty($store_phone) && !preg_match('/^[0-9+\-\s()]+$/', $store_phone)) {
            $errors[] = __('Invalid phone number format.');
        }

        if (empty($errors)) {
            // Save to database
            $stmt = $connection->prepare("UPDATE settings SET store_name=?, store_address=?, store_phone=?, store_email=?, store_description=? WHERE id=1");
            if ($stmt) {
                $stmt->bind_param("sssss", $store_name, $store_address, $store_phone, $store_email, $store_description);
                if ($stmt->execute()) {
                    $message = __('Store settings saved successfully.');
                } else {
                    $message = __('Failed to save store settings.');
                }
                $stmt->close();
            } else {
                $message = __('Failed to save store settings.');
            }
        } else {
            $message = implode('<br>', $errors);
        }
    } elseif (isset($_POST['update_preferences'])) {
        // Update user preferences
        $theme = $_POST['theme'] ?? 'light';
        $language = $_POST['language'] ?? 'id';
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;

        $valid_themes = ['light', 'dark', 'auto'];
        $valid_languages = ['id', 'en'];

        if (!in_array($theme, $valid_themes)) {
            $theme = 'light';
        }

        if (!in_array($language, $valid_languages)) {
            $language = 'id';
        }

        // Save to database
        $stmt = $connection->prepare("UPDATE settings SET theme=?, language=?, email_notifications=? WHERE id=1");
        if ($stmt) {
            $stmt->bind_param("ssi", $theme, $language, $email_notifications);
            if ($stmt->execute()) {
                // Set the new language in session
                set_language($language);
                $message = __('Settings saved successfully');
                // Also update localStorage and apply theme immediately
                echo "<script>
                    localStorage.setItem('theme', '$theme');
                    localStorage.setItem('language', '$language');
                    // Apply theme immediately
                    document.body.classList.remove('light', 'dark');
                    document.body.classList.add('$theme');
                    document.documentElement.setAttribute('data-bs-theme', '$theme');
                    // Update sidebar toggle if it exists
                    const toggle = document.getElementById('toggle-dark');
                    if (toggle) {
                        toggle.checked = '$theme' === 'dark';
                    }
                </script>";
            } else {
                $message = __('Failed to save settings');
            }
            $stmt->close();
        } else {
            $message = __('Failed to save settings');
        }
    } elseif (isset($_POST['update_system'])) {
        // Update system settings
        $currency = $_POST['currency'] ?? 'IDR';
        $date_format = $_POST['date_format'] ?? 'd/m/Y';
        $timezone = $_POST['timezone'] ?? 'Asia/Jakarta';

        $valid_currencies = ['IDR', 'USD'];
        $valid_date_formats = ['d/m/Y', 'm/d/Y', 'Y-m-d'];
        $valid_timezones = ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'];

        if (!in_array($currency, $valid_currencies)) {
            $currency = 'IDR';
        }

        if (!in_array($date_format, $valid_date_formats)) {
            $date_format = 'd/m/Y';
        }

        if (!in_array($timezone, $valid_timezones)) {
            $timezone = 'Asia/Jakarta';
        }

        // Save to database
        $stmt = $connection->prepare("UPDATE settings SET currency=?, date_format=?, timezone=? WHERE id=1");
        if ($stmt) {
            $stmt->bind_param("sss", $currency, $date_format, $timezone);
            if ($stmt->execute()) {
                $message = __('System settings saved successfully.');
            } else {
                $message = __('Failed to save system settings.');
            }
            $stmt->close();
        } else {
            $message = __('Failed to save system settings.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?= get_current_language() ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= __('System Settings') ?> - Farizal Petshop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2 mb-4"></i><?= __('System Settings') ?>
                        </h5>
                    </div>
                    <div class="card-body mt-2MN">
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>

                        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $active_tab == 'store' ? 'active' : '' ?>" id="store-tab" data-bs-toggle="tab" data-bs-target="#store" type="button" role="tab">
                                    <i class="bi bi-shop me-1"></i><?= __('Store Information') ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $active_tab == 'preferences' ? 'active' : '' ?>" id="preferences-tab" data-bs-toggle="tab" data-bs-target="#preferences" type="button" role="tab">
                                    <i class="bi bi-person-gear me-1"></i><?= __('Preferences') ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $active_tab == 'system' ? 'active' : '' ?>" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">
                                    <i class="bi bi-tools me-1"></i><?= __('System') ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $active_tab == 'security' ? 'active' : '' ?>" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                                    <i class="bi bi-shield-lock me-1"></i><?= __('Security') ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?= $active_tab == 'backup' ? 'active' : '' ?>" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                                    <i class="bi bi-database me-1"></i><?= __('Backup') ?>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-4" id="settingsTabContent">
                            <!-- Informasi Toko Tab -->
                            <div class="tab-pane fade <?= $active_tab == 'store' ? 'show active' : '' ?>" id="store" role="tabpanel">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="store_name" class="form-label"><?= __('Store Name') ?> *</label>
                                                <input type="text" class="form-control" id="store_name" name="store_name" value="<?= htmlspecialchars($settings['store_name'] ?? '') ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="store_phone" class="form-label"><?= __('Phone Number') ?></label>
                                                <input type="tel" class="form-control" id="store_phone" name="store_phone" value="<?= htmlspecialchars($settings['store_phone'] ?? '') ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="store_email" class="form-label"><?= __('Store Email') ?></label>
                                                <input type="email" class="form-control" id="store_email" name="store_email" value="<?= htmlspecialchars($settings['store_email'] ?? '') ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="store_address" class="form-label"><?= __('Store Address') ?></label>
                                                <textarea class="form-control" id="store_address" name="store_address" rows="3"><?= htmlspecialchars($settings['store_address'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="store_description" class="form-label"><?= __('Store Description') ?></label>
                                        <textarea class="form-control" id="store_description" name="store_description" rows="3"><?= htmlspecialchars($settings['store_description'] ?? '') ?></textarea>
                                    </div>
                                    <button type="submit" name="update_store" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i><?= __('Save Store Settings') ?>
                                    </button>
                                </form>
                            </div>

                            <!-- Preferensi Tab -->
                            <div class="tab-pane fade <?= $active_tab == 'preferences' ? 'show active' : '' ?>" id="preferences" role="tabpanel">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="theme" class="form-label"><?= __('Application Theme') ?></label>
                                                <select class="form-select" id="theme" name="theme">
                                                    <option value="light" <?= ($settings['theme'] ?? 'light') == 'light' ? 'selected' : '' ?>>Light</option>
                                                    <option value="dark" <?= ($settings['theme'] ?? 'light') == 'dark' ? 'selected' : '' ?>>Dark</option>
                                                    <option value="auto" <?= ($settings['theme'] ?? 'light') == 'auto' ? 'selected' : '' ?>>Auto</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="language" class="form-label"><?= __('Language') ?></label>
                                                <select class="form-select" id="language" name="language">
                                                    <option value="id" <?= ($settings['language'] ?? 'id') == 'id' ? 'selected' : '' ?>>Bahasa Indonesia</option>
                                                    <option value="en" <?= ($settings['language'] ?? 'id') == 'en' ? 'selected' : '' ?>>English</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?= ($settings['email_notifications'] ?? 0) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="email_notifications">
                                                        <?= __('Enable Email Notifications') ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" name="update_preferences" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i><?= __('Save Preferences') ?>
                                    </button>
                                </form>
                            </div>

                            <!-- Sistem Tab -->
                            <div class="tab-pane fade <?= $active_tab == 'system' ? 'show active' : '' ?>" id="system" role="tabpanel">
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="currency" class="form-label"><?= __('Default Currency') ?></label>
                                                <select class="form-select" id="currency" name="currency">
                                                    <option value="IDR" <?= ($settings['currency'] ?? 'IDR') == 'IDR' ? 'selected' : '' ?>>Rupiah (IDR)</option>
                                                    <option value="USD" <?= ($settings['currency'] ?? 'IDR') == 'USD' ? 'selected' : '' ?>>US Dollar (USD)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="date_format" class="form-label"><?= __('Date Format') ?></label>
                                                <select class="form-select" id="date_format" name="date_format">
                                                    <option value="d/m/Y" <?= ($settings['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' ?>>DD/MM/YYYY</option>
                                                    <option value="m/d/Y" <?= ($settings['date_format'] ?? 'd/m/Y') == 'm/d/Y' ? 'selected' : '' ?>>MM/DD/YYYY</option>
                                                    <option value="Y-m-d" <?= ($settings['date_format'] ?? 'd/m/Y') == 'Y-m-d' ? 'selected' : '' ?>>YYYY-MM-DD</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="timezone" class="form-label"><?= __('Timezone') ?></label>
                                                <select class="form-select" id="timezone" name="timezone">
                                                    <option value="Asia/Jakarta" <?= ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : '' ?>>WIB (Jakarta)</option>
                                                    <option value="Asia/Makassar" <?= ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Makassar' ? 'selected' : '' ?>>WITA (Makassar)</option>
                                                    <option value="Asia/Jayapura" <?= ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jayapura' ? 'selected' : '' ?>>WIT (Jayapura)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" name="update_system" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i><?= __('Save System Settings') ?>
                                    </button>
                                </form>
                            </div>

                            <!-- Keamanan Tab -->
                            <div class="tab-pane fade <?= $active_tab == 'security' ? 'show active' : '' ?>" id="security" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><?= __('Password') ?></h6>
                                        <p><?= __('Manage your account password for better security.') ?></p>
                                        <a href="index.php?halaman=change-password" class="btn btn-warning">
                                            <i class="bi bi-shield-lock me-1"></i><?= __('Change Password') ?>
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?= __('Two-Factor Authentication') ?></h6>
                                        <p><?= __('Add an extra layer of security with 2FA.') ?></p>
                                        <button class="btn btn-outline-primary" disabled>
                                            <i class="bi bi-shield-check me-1"></i><?= __('Enable 2FA (Coming Soon)') ?>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Backup Tab -->
                            <div class="tab-pane fade <?= $active_tab == 'backup' ? 'show active' : '' ?>" id="backup" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><?= __('Backup Database') ?></h6>
                                        <p><?= __('Create a copy of data for security.') ?></p>
                                        <button class="btn btn-success" disabled>
                                            <i class="bi bi-download me-1"></i><?= __('Create Backup') ?>
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?= __('Restore Database') ?></h6>
                                        <p><?= __('Restore data from a backup file.') ?></p>
                                        <button class="btn btn-danger" disabled>
                                            <i class="bi bi-upload me-1"></i><?= __('Restore Backup') ?>
                                        </button>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-4">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <strong><?= __('Note') ?>:</strong> <?= __('Backup and restore features will be implemented in future versions.') ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <a href="index.php?halaman=profile" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-1"></i><?= __('Back to Profile') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/static/js/components/dark.js"></script>

    <script>
    // Initialize theme on settings page load
    document.addEventListener('DOMContentLoaded', function() {
        const storedTheme = localStorage.getItem('theme') || 'light';
        const actualTheme = storedTheme === 'auto' ?
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') :
            storedTheme;

        // Apply theme
        document.body.classList.remove('light', 'dark');
        document.body.classList.add(actualTheme);
        document.documentElement.setAttribute('data-bs-theme', actualTheme);

        // Update sidebar toggle if it exists
        const toggle = document.getElementById('toggle-dark');
        if (toggle) {
            toggle.checked = actualTheme === 'dark';
        }
    });
    </script>
</body>
</html>
