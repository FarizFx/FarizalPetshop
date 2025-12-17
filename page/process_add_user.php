<?php
// Prevent direct access
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?halaman=users');
    exit();
}

session_start();
include "../function/connection.php";
include "../function/language.php";
include_once "../function/role_manager.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing User Addition</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php

// Inisialisasi role manager
$role_manager = new RoleManager($connection);

// Cek permission untuk menambah user
if (!hasPermission('user_management')) {
    echo "
    <script>
    Swal.fire({
        title: '" . __('Access Denied') . "',
        text: '" . __('You do not have permission to add users.') . "',
        icon: 'error',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = '../index.php?halaman=users';
    })
    </script>
    ";
    exit();
}

if (isset($_POST['nama']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['role'])) {
    $nama = htmlspecialchars(trim($_POST['nama']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $password = htmlspecialchars($_POST['password']);
    $role = $_POST['role'];

    // Validasi input
    if (empty($nama) || empty($username) || empty($password) || empty($role)) {
        echo "
        <script>
        Swal.fire({
            title: '" . __('Validation Error') . "',
            text: '" . __('Please fill in all required fields.') . "',
            icon: 'error',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = '../index.php?halaman=users';
        })
        </script>
        ";
        exit();
    }

    // Validasi role
    if (!$role_manager->roleExists($role)) {
        echo "
        <script>
        Swal.fire({
            title: '" . __('Invalid Role') . "',
            text: '" . __('Selected role is not valid.') . "',
            icon: 'error',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = '../index.php?halaman=users';
        })
        </script>
        ";
        exit();
    }

    // Validasi permission untuk assign role ini
    $current_user_role = getUserRole();
    if (!$role_manager->canChangeRole($current_user_role, $role)) {
        echo "
        <script>
        Swal.fire({
            title: '" . __('Permission Denied') . "',
            text: '" . __('You do not have permission to assign this role.') . "',
            icon: 'error',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = '../index.php?halaman=users';
        })
        </script>
        ";
        exit();
    }

    // Hash password
    $hashPassword = password_hash($password, PASSWORD_DEFAULT);

    // Cek apakah username sudah ada
    $stmt = $connection->prepare("SELECT id FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "
        <script>
        Swal.fire({
            title: '" . __('Username Exists') . "',
            text: '" . __('Username is already taken. Please choose another one.') . "',
            icon: 'error',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = '../index.php?halaman=users';
        })
        </script>
        ";
        exit();
    }
    $stmt->close();

    // Insert user baru
    $stmt = $connection->prepare("INSERT INTO user (nama, username, email, role, password, foto_profil) VALUES (?, ?, ?, ?, ?, NULL)");

    if ($stmt === false) {
        error_log("Add user prepare statement failed: " . $connection->error);
        echo "
        <script>
        Swal.fire({
            title: '" . __('Database Error') . "',
            text: '" . __('A system error occurred. Please try again later.') . "',
            icon: 'error',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = '../index.php?halaman=users';
        })
        </script>
        ";
        exit();
    }

    $stmt->bind_param("sssss", $nama, $username, $email, $role, $hashPassword);

    if ($stmt->execute()) {
        $new_user_id = $stmt->insert_id;
        $stmt->close();

        // Log aksi
        $role_manager->logRoleAction('CREATE_USER', "New User ID: $new_user_id, Role: $role");

        echo "
        <script>
        Swal.fire({
            title: '" . __('Success') . "',
            text: '" . __('New user has been created successfully!') . "',
            icon: 'success',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = '../index.php?halaman=users';
        })
        </script>
        ";
        exit();
    } else {
        error_log("Add user execute statement failed: " . $stmt->error);
        echo "
        <script>
        Swal.fire({
            title: '" . __('Database Error') . "',
            text: '" . __('Failed to create user. Please try again later.') . "',
            icon: 'error',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = '../index.php?halaman=users';
        })
        </script>
        ";
        exit();
    }
} else {
    echo "
    <script>
    Swal.fire({
        title: '" . __('Invalid Request') . "',
        text: '" . __('Invalid request parameters.') . "',
        icon: 'error',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = '../index.php?halaman=users';
    })
    </script>
    ";
    exit();
}
?>
