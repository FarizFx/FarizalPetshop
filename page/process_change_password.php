<?php
// Prevent direct access without POST
if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?halaman=change-password');
    exit();
}

session_start();
include "../function/connection.php";
include "../function/language.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Password Change</title>
    <!-- Include SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Include CSS for dark mode support if needed by SweetAlert styles -->
    <link rel="stylesheet" href="../assets/compiled/css/app-dark.css" />
</head>
<body>
<?php

// Check login
if (!isset($_SESSION['nama']) || !isset($_SESSION['user_id'])) {
    echo "
    <script>
    Swal.fire({
        title: '" . __('Access Denied') . "',
        text: '" . __('Please log in first.') . "',
        icon: 'error',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = '../login.php';
    })
    </script>
    ";
    exit();
}

$user_id = $_SESSION['user_id'];
$min_password_length = 8;

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo "
    <script>
    Swal.fire({
        title: '" . __('Validation Error') . "',
        text: '" . __('All fields must be filled.') . "',
        icon: 'error',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = '../index.php?halaman=change-password';
    })
    </script>
    ";
    exit();
}

if ($new_password !== $confirm_password) {
    echo "
    <script>
    Swal.fire({
        title: '" . __('Validation Error') . "',
        text: '" . __('New password and confirmation password do not match.') . "',
        icon: 'error',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = '../index.php?halaman=change-password';
    })
    </script>
    ";
    exit();
}

if (strlen($new_password) < $min_password_length) {
    $msg = sprintf(__('Password must be at least %d characters long.'), $min_password_length);
    echo "
    <script>
    Swal.fire({
        title: '" . __('Validation Error') . "',
        text: '" . $msg . "',
        icon: 'error',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = '../index.php?halaman=change-password';
    })
    </script>
    ";
    exit();
}

// Database Operations
// 1. Fetch current password
$stmt = $connection->prepare("SELECT password FROM user WHERE id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $connection->error);
    echo "
    <script>
    Swal.fire({
        title: '" . __('System Error') . "',
        text: '" . __('Database error occurred.') . "',
        icon: 'error',
        showConfirmButton: false,
        timer: 3000,
    }).then(() => {
        window.location.href = '../index.php?halaman=change-password';
    })
    </script>
    ";
    exit();
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($hashed_password);

if ($stmt->fetch()) {
    $stmt->close(); // Close fetch statement

    // Verify current password
    if (password_verify($current_password, $hashed_password)) {
        
        // Hash new password
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password
        $update_stmt = $connection->prepare("UPDATE user SET password = ? WHERE id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("si", $new_hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $update_stmt->close();
                echo "
                <script>
                Swal.fire({
                    title: '" . __('Success') . "',
                    text: '" . __('Password successfully changed.') . "',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                }).then(() => {
                    window.location.href = '../index.php?halaman=profile';
                })
                </script>
                ";
                exit();
            } else {
                error_log("Update failed: " . $update_stmt->error);
                echo "
                <script>
                Swal.fire({
                    title: '" . __('Failed') . "',
                    text: '" . __('Failed to update password.') . "',
                    icon: 'error',
                    showConfirmButton: true,
                }).then(() => {
                    window.location.href = '../index.php?halaman=change-password';
                })
                </script>
                ";
                exit();
            }
        } else {
             error_log("Prepare update failed: " . $connection->error);
             echo "
            <script>
            Swal.fire({
                title: '" . __('System Error') . "',
                text: '" . __('Database error occurred.') . "',
                icon: 'error',
                showConfirmButton: true,
            }).then(() => {
                window.location.href = '../index.php?halaman=change-password';
            })
            </script>
            ";
            exit();
        }

    } else {
        echo "
        <script>
        Swal.fire({
            title: '" . __('Failed') . "',
            text: '" . __('Current password is wrong.') . "',
            icon: 'error',
            showConfirmButton: true,
            timer: 3000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = '../index.php?halaman=change-password';
        })
        </script>
        ";
        exit();
    }
} else {
    $stmt->close();
    echo "
    <script>
    Swal.fire({
        title: '" . __('Error') . "',
        text: '" . __('User not found.') . "',
        icon: 'error',
        showConfirmButton: true,
    }).then(() => {
        window.location.href = '../index.php?halaman=change-password';
    })
    </script>
    ";
    exit();
}
?>
</body>
</html>

