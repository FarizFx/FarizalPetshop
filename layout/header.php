<?php include "function/language.php"; ?>
    <header>
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="dropdown ms-auto">
            <a href="#" id="profileDropdown" class="d-flex align-items-center gap-2" style="text-decoration:none; cursor:pointer;">
                <div class="user-menu d-flex">
                    <div class="user-name text-end me-3">
                        <h6 class="mb-0 text-gray-600">
                            <?= !isset($_SESSION['nama']) ? 'Guest' : $_SESSION['nama'] ?></h6>
                        <p class="mb-0 text-sm text-gray-600">
                            <?= !isset($_SESSION['role']) ? 'Guest' : ucfirst($_SESSION['role']) ?>
                        </p>
                    </div>
                    <div class="user-img d-flex align-items-center">
                        <div class="avatar avatar-md">
                            <?php if (isset($_SESSION['foto_profil']) && !empty($_SESSION['foto_profil'])): ?>
                                <img src="./assets/uploads/profiles/<?= $_SESSION['foto_profil'] ?>" alt="Profile" />
                            <?php else: ?>
                                <img src="./assets/compiled/jpg/1.jpg" alt="Default Profile" />
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </a>
                     <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown" style="min-width: 14rem">
                         <li class="px-3 py-2">
                             <div class="d-flex align-items-center">
                                 <div class="avatar avatar-md me-3">
                                     <?php if (isset($_SESSION['foto_profil']) && !empty($_SESSION['foto_profil'])): ?>
                                         <img src="./assets/uploads/profiles/<?= $_SESSION['foto_profil'] ?>" alt="Profile" class="rounded-circle" />
                                     <?php else: ?>
                                         <img src="./assets/compiled/jpg/1.jpg" alt="Default Profile" class="rounded-circle" />
                                     <?php endif; ?>
                                 </div>
                                 <div>
                                     <h6 class="mb-0 text-dark"><?= !isset($_SESSION['nama']) ? 'Guest' : $_SESSION['nama'] ?></h6>
                                     <small class="text-muted"><?= !isset($_SESSION['email']) ? '' : $_SESSION['email'] ?></small>
                                 </div>
                             </div>
                         </li>
                         <li><hr class="dropdown-divider" /></li>
                         
                         <?php if (isset($_SESSION['nama'])) : ?>
                             <!-- Menu untuk user yang sudah login -->
                             <li>
                                 <a class="dropdown-item" href="index.php?halaman=profile">
                                     <?= __('Profile') ?>
                                 </a>
                             </li>
                             <li>
                                 <a class="dropdown-item" href="index.php?halaman=edit-profile">
                                     <?= __('Edit Profile') ?>
                                 </a>
                             </li>
                             <li>
                                 <a class="dropdown-item" href="index.php?halaman=change-password">
                                     <?= __('Change Password') ?>
                                 </a>
                             </li>
                             <li><hr class="dropdown-divider" /></li>
                             <li>
                                 <a class="dropdown-item" href="index.php?halaman=settings">
                                     <?= __('Settings') ?>
                                 </a>
                             </li>
                             <li><hr class="dropdown-divider" /></li>
                             <li>
                                 <a class="dropdown-item text-danger" href="index.php?halaman=logout" onclick="confirmLogout(event)">
                                     <?= __('Logout') ?>
                                 </a>
                             </li>
                         <?php else : ?>
                             <!-- Menu untuk guest -->
                             <li>
                                 <a class="dropdown-item" href="login.php">
                                     <?= __('Login') ?>
                                 </a>
                             </li>
                             <li>
                                 <a class="dropdown-item" href="register.php">
                                     <?= __('Register') ?>
                                 </a>
                             </li>
                         <?php endif ?>
                     </ul>
                 </div>
             </div>
         </div>
     </nav>
  </header>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" />

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="layout/header-dropdown.js"></script>

<script>
function confirmLogout(event) {
    event.preventDefault();
    if (confirm('<?= __('Are you sure you want to logout?') ?>')) {
        window.location.href = event.currentTarget.href;
    }
}
</script>
