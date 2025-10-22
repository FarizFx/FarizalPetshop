<?php
// Include configuration file
include "config.php";

$connection = mysqli_connect($hostname, $username, $password, $database);

if (!$connection) {
    // Catat kesalahan ke log server, bukan ke output browser
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    // Beri pesan umum kepada pengguna
    die("Sistem sedang dalam perbaikan. Mohon maaf atas ketidaknyamanan ini.");
}

// Tidak ada lagi logika login/register di file ini
?>
