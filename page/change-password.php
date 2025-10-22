<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['nama'])) {
    header('Location: login.php');
    exit();
}

require_once './function/connection.php'; // Correct DB connection file
include './function/language.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = __('All fields must be filled.');
    } elseif ($new_password !== $confirm_password) {
        $message = __('New password and confirmation password do not match.');
    } else {
        // Fetch current password hash from DB
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            $message = __('User not found.');
        } else {
            $stmt = $connection->prepare("SELECT password FROM user WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            if ($stmt->fetch()) {
                if (password_verify($current_password, $hashed_password)) {
                    // Update password
                    $stmt->close();
                    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $connection->prepare("UPDATE user SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                    if ($update_stmt->execute()) {
                        $message = __('Password successfully changed.');
                    } else {
                        $message = __('Failed to change password.');
                    }
                    $update_stmt->close();
                } else {
                    $message = __('Current password is wrong.');
                }
            } else {
                $message = __('User not found.');
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= __('Change Password') ?> - Farizal Petshop</title>
    <link rel="stylesheet" href="../assets/compiled/css/app.css" />
</head>
<body>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white p-3  ">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-lock me-2"></i><?= __('Change Password') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 ">
                                <form method="POST" action="change-password.php" enctype="multipart/form-data">
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
                document.getElementById(fieldId).type = type;
            });
        });
    </script>
</body>
</html>
