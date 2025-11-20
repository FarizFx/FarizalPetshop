<?php
include "config.php";

$connection = mysqli_connect($hostname, $username, $password, $database);

if (!$connection) {
    // errlog console
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    // Beri pesan umum kepada pengguna
    die("Sistem sedang dalam perbaikan. Mohon maaf atas ketidaknyamanan ini.");
}


?>
