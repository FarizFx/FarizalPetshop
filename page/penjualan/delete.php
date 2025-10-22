<?php
include "./function/connection.php";
include "./function/language.php";

try {
    $message = "";
    $success = FALSE;
    $error = FALSE;

    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        $select = mysqli_query($connection, "SELECT tanggal FROM penjualan WHERE id_penjualan = '$id'");
        $data = mysqli_fetch_assoc($select);

        if (!$data) {
            header('Location: index.php?halaman=penjualan');
        }

        $query = mysqli_query($connection, "DELETE FROM penjualan WHERE id_penjualan = '$id'");

        if ($query == TRUE) {
            $message = __('Data deleted successfully');
            echo "
        <script>
        Swal.fire({
            title: '" . __('Success') . "',
            text: '$message',
            icon: 'success',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = 'index.php?halaman=penjualan';
        })
        </script>
        ";
        } else {
            $message = __('Failed to delete data');
            echo "
        <script>
        Swal.fire({
            title: '" . __('Failed') . "',
            text: '$message',
            icon: 'error',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
        }).then(() => {
            window.location.href = 'index.php?halaman=penjualan';
        })
        </script>
        ";
        }
    } else {
        $message = "ID tidak ditemukan";
    }
} catch (\Throwable $th) {
    echo "
    <script>
    Swal.fire({
        title: 'Gagal',
        text: 'Server error!',
        icon: 'error',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    }).then(() => {
        window.location.href = 'index.php?halaman=penjualan';
    })
    </script>
    ";
}
