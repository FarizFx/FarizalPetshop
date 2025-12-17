<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


require_once 'function/connection.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$query = "SELECT * FROM user WHERE id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);

    // Validasi
    if (empty($nama) || empty($email) || empty($username)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {

        // Check if email already exists (except current user)
        $check_email = $connection->prepare("SELECT id FROM user WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $user_id);
        $check_email->execute();
        
        if ($check_email->get_result()->num_rows > 0) {
            $error = 'Email sudah digunakan!';
        } else {
            // Handle file upload
            $foto_profil = $user['foto_profil'];
            if (!empty($_FILES['foto_profil']['name'])) {
                $target_dir = "./assets/uploads/profiles/";
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION);
                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                $target_file = $target_dir . $new_filename;
                
                // Check file type
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array(strtolower($file_extension), $allowed_types)) {
                    $error = 'Hanya file JPG, JPEG, PNG, dan GIF yang diizinkan!';
                } elseif ($_FILES['foto_profil']['size'] > 2000000) {
                    $error = 'Ukuran file maksimal 2MB!';
                } else {
                    if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
                        // Delete old photo if exists
                        if (!empty($foto_profil) && file_exists($target_dir . $foto_profil)) {
                            unlink($target_dir . $foto_profil);
                        }
                        $foto_profil = $new_filename;
                    } else {
                        $error = 'Gagal mengupload foto!';
                    }
                }
            }

            if (empty($error)) {

                // Update user data
                $update_query = "UPDATE user SET nama = ?, email = ?, username = ?, foto_profil = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $connection->prepare($update_query);
                $stmt->bind_param("ssssi", $nama, $email, $username, $foto_profil, $user_id);
                
                if ($stmt->execute()) {
                    // Update session
                    $_SESSION['nama'] = $nama;
                    $_SESSION['email'] = $email;
                    $_SESSION['foto_profil'] = $foto_profil;
                    
                    $success = 'Profil berhasil diperbarui!';
                    // Refresh user data
                    $user = array_merge($user, [
                        'nama' => $nama,
                        'email' => $email,
                        'username' => $username,
                        'foto_profil' => $foto_profil
                    ]);
                } else {
                    $error = 'Gagal memperbarui profil!';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pencil-square me-2"></i>Edit Profil
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    <div class="mb-3">
                                        <?php if (!empty($user['foto_profil'])): ?>
                                            <img src="./assets/uploads/profiles/<?= $user['foto_profil'] ?>" 
                                                 alt="Foto Profil" class="img-fluid rounded-circle" 
                                                 style="width: 150px; height: 150px; object-fit: cover;" id="previewFoto">
                                        <?php else: ?>
                                            <img src="./assets/compiled/jpg/1.jpg" 
                                                 alt="Default Profile" class="img-fluid rounded-circle" 
                                                 style="width: 150px; height: 150px; object-fit: cover;" id="previewFoto">
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="foto_profil" class="form-label">Ubah Foto Profil</label>
                                        <input type="file" class="form-control" id="foto_profil" name="foto_profil" 
                                               accept="image/*" onchange="previewImage(this)">
                                        <div class="form-text">Max 2MB. Format: JPG, PNG, GIF</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="nama" class="form-label">Nama Lengkap *</label>
                                        <input type="text" class="form-control" id="nama" name="nama" 
                                               value="<?= htmlspecialchars($user['nama']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?= htmlspecialchars($user['username']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" 
                                               value="<?= ucfirst($user['role']) ?>" disabled>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Simpan Perubahan
                                </button>
                                <a href="index.php?halaman=profile" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Kembali
                                </a>
                                <a href="index.php?halaman=change-password" class="btn btn-warning ms-auto">
                                    <i class="bi bi-shield-lock me-1"></i>Ubah Password
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function previewImage(input) {
        const preview = document.getElementById('previewFoto');
        const file = input.files[0];
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>