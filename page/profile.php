
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . "/../function/connection.php";
include __DIR__ . "/../function/language.php";

$user = null;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $stmt = $connection->prepare("SELECT nama, email, username, role, foto_profil, created_at, updated_at FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('Profile') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    

    <div class="container mt-6">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person me-2"></i><?= __('My Profile') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4 mt-5 text-center">
                                <?php if (!empty($user['foto_profil'])): ?>
                                    <img src="./assets/uploads/profiles/<?= htmlspecialchars($user['foto_profil']) ?>"
                                         alt="Foto Profil" class="img-fluid rounded-circle mb-4" style="width: 150px; height: 150px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="./assets/compiled/jpg/1.jpg"
                                         alt="Default Profile" class="img-fluid rounded-circle mb-4" style="width: 150px; height: 150px; object-fit: cover;">
                                <?php endif; ?>

                            </div>
                            <div class="col-md-8 mt-4">
                                <div class="row mb-2">
                                    <div class="col-sm-4 fw-bold"><?= __('Full Name:') ?></div>
                                    <div class="col-sm-8"><?= htmlspecialchars($_SESSION['nama'] ?? '') ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 fw-bold"><?= __('Email:') ?></div>
                                    <div class="col-sm-8"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 fw-bold"><?= __('Username:') ?></div>
                                    <div class="col-sm-8"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 fw-bold"><?= __('Role:') ?></div>
                                    <div class="col-sm-8">
                                        <span class="badge bg-info"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? '')) ?></span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 fw-bold"><?= __('Join Date:') ?></div>
                                    <div class="col-sm-8"><?= !empty($user['created_at']) ? date('d F Y', strtotime($user['created_at'])) : '-' ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-4 fw-bold"><?= __('Last Updated:') ?></div>
                                    <div class="col-sm-8"><?= !empty($user['updated_at']) ? date('d F Y H:i', strtotime($user['updated_at'])) : '-' ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="index.php?halaman=edit-profile" class="btn btn-primary">
                                <i class="bi bi-pencil me-1"></i><?= __('Edit Profile') ?>
                            </a>
                            <a href="index.php?halaman=change-password" class="btn btn-warning">
                                <i class="bi bi-shield-lock me-1"></i><?= __('Change Password') ?>
                            </a>
                            <a href="dashboard.php" class="btn btn-secondary ms-auto">
                                <i class="bi bi-arrow-left me-1"></i><?= __('Back') ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
