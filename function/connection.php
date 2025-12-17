<?php

$hostname = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: 'fariz';
$database = getenv('DB_NAME') ?: 'login_petshop';

$connection = mysqli_connect($hostname, $username, $password, $database);

if (!$connection) {
    // errlog console
    error_log("Koneksi database gagal: " . mysqli_connect_error());
    // Beri pesan umum kepada pengguna
    die("Sistem sedang dalam perbaikan. Mohon maaf atas ketidaknyamanan ini.");
}


?>
