<?php
include "./function/connection.php";
include "./function/chart.php";

try {
    $query1 = mysqli_query($connection, "SELECT id_kategori FROM kategori");
    $data1 = mysqli_fetch_all($query1);
    $kategori = count($data1);
    
 
    $query2 = mysqli_query($connection, "SELECT id_produk FROM produk"); 
    $data2 = mysqli_fetch_all($query2);
    $produk = count($data2);
    $tanggal_hari_ini = date('Y-m-d');
    $total_pendapatan = 0;
    $total_modal_harian = 0;
    $total_profit_harian = 0;

    $query_pendapatan = mysqli_query($connection, "
        SELECT COALESCE(SUM(total_harga), 0) as total
        FROM penjualan
        WHERE DATE(tanggal) = '$tanggal_hari_ini'
    ");

    if(!$query_pendapatan) {
        error_log("Error pendapatan: " . mysqli_error($connection));
    } else {
        $result = mysqli_fetch_assoc($query_pendapatan);
        $total_pendapatan = $result['total'] ?? 0;
    }

    // Hitung modal harian menggunakan harga_modal tersimpan
    $query_modal_harian = mysqli_query($connection, "
        SELECT COALESCE(SUM(dp.qty * dp.harga_modal), 0) as total_modal
        FROM detail_penjualan dp
        JOIN penjualan p ON dp.id_penjualan = p.id_penjualan
        WHERE DATE(p.tanggal) = '$tanggal_hari_ini'
    ");

    if($query_modal_harian) {
        $result_modal = mysqli_fetch_assoc($query_modal_harian);
        $total_modal_harian = $result_modal['total_modal'] ?? 0;
        $total_profit_harian = $total_pendapatan - $total_modal_harian;
    }

    $bulan_ini = date('Y-m');
    $total_pendapatan_bulan = 0;
    $total_modal_bulan = 0;
    $total_profit_bulan = 0;

    $query_bulanan = mysqli_query($connection, "
        SELECT COALESCE(SUM(total_harga), 0) as total
        FROM penjualan
        WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'
    ");
    if($query_bulanan) {
        $total_pendapatan_bulan = mysqli_fetch_assoc($query_bulanan)['total'];
    }

    // Hitung modal bulanan menggunakan harga_modal tersimpan
    $query_modal_bulan = mysqli_query($connection, "
        SELECT COALESCE(SUM(dp.qty * dp.harga_modal), 0) as total_modal
        FROM detail_penjualan dp
        JOIN penjualan p ON dp.id_penjualan = p.id_penjualan
        WHERE DATE_FORMAT(p.tanggal, '%Y-%m') = '$bulan_ini'
    ");

    if($query_modal_bulan) {
        $result_modal_bulan = mysqli_fetch_assoc($query_modal_bulan);
        $total_modal_bulan = $result_modal_bulan['total_modal'] ?? 0;
        $total_profit_bulan = $total_pendapatan_bulan - $total_modal_bulan;
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    echo "
    <script>
    Swal.fire({
        title: 'Gagal',
        text: 'Terjadi kesalahan sistem',
        icon: 'error',
        showConfirmButton: false,
        timer: 2000
    })
    </script>
    ";
}
?>



<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Dashboard</h3>
                <p class="text-subtitle text-muted">
                    Halaman Dashboard
                </p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="index.html">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            Home
                        </li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <section class="row py-0 mt-0 mb-0">
        <div class="col-16">
            <div class="row">
                <div class="row gx-2 ">
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card my-2 mx-auto">
                            <div class="card-body d-flex align-items-center mb-0">
                                <div class="stats-icon blue me-3">
                                    <i class="iconly-boldCategory"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold">Jumlah Kategori</h6>
                                    <h6 class="font-extrabold mb-0"><?= $kategori ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-0">
                        <div class="card my-2">
                            <div class="card-body d-flex align-items-center mb-0">
                                <div class="stats-icon blue me-3">
                                    <i class="iconly-boldBuy"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold">Jumlah Produk</h6>
                                    <h6 class="font-extrabold mb-0"><?= $produk ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-0">
                        <div class="card my-2">
                            <div class="card-body d-flex align-items-center mb-0">
                                <div class="stats-icon blue me-3">
                                    <i class="iconly-boldWallet"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold">Pendapatan Hari Ini</h6>
                                    <h6 class="font-extrabold mb-0">
                                     <?php
                                     if(isset($total_pendapatan)) {
                                         echo 'Rp ' . number_format($total_pendapatan, 0, ',', '.');
                                     } else {
                                         echo '<span class="text-danger">Error</span>';
                                     }
                                     ?>
                                 </h6>
                                 <?php if(isset($e)): ?>
                                 <small class="text-danger">Data mungkin tidak terupdate</small>
                                 <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-0">
                        <div class="card my-2">
                            <div class="card-body d-flex align-items-center mb-0">
                                <div class="stats-icon red me-3">
                                    <i class="iconly-boldBuy"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold">Modal Hari Ini</h6>
                                    <h6 class="font-extrabold mb-0">
                                     <?php
                                     if(isset($total_modal_harian)) {
                                         echo 'Rp ' . number_format($total_modal_harian, 0, ',', '.');
                                     } else {
                                         echo '<span class="text-danger">Error</span>';
                                     }
                                     ?>
                                 </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-0">
                        <div class="card my-2">
                            <div class="card-body d-flex align-items-center mb-0">
                                <div class="stats-icon green me-3">
                                    <i class="iconly-boldChart"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold">Profit Hari Ini</h6>
                                    <h6 class="font-extrabold mb-0">
                                     <?php
                                     if(isset($total_profit_harian)) {
                                         $color_class = $total_profit_harian >= 0 ? 'text-success' : 'text-danger';
                                         echo '<span class="' . $color_class . '">Rp ' . number_format($total_profit_harian, 0, ',', '.') . '</span>';
                                     } else {
                                         echo '<span class="text-danger">Error</span>';
                                     }
                                     ?>
                                 </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-0">
                        <div class="card my-2">
                            <div class="card-body d-flex align-items-center mb-0">
                                <div class="stats-icon blue me-3">
                                    <i class="iconly-boldWallet"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold">Pendapatan Bulanan</h6>
                                    <h6 class="font-extrabold mb-0">
                                     <?php
                                     if(isset($total_pendapatan_bulan)) {
                                         echo 'Rp ' . number_format($total_pendapatan_bulan, 0, ',', '.');
                                     } else {
                                         echo '<span class="text-danger">Error</span>';
                                     }
                                     ?>
                                 </h6>
                                 <?php if(isset($e)): ?>
                                 <small class="text-danger">Data mungkin tidak terupdate</small>
                                 <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-0">
                        <div class="card my-2">
                            <div class="card-body d-flex align-items-center mb-0">
                                <div class="stats-icon red me-3">
                                    <i class="iconly-boldBuy"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold">Modal Bulanan</h6>
                                    <h6 class="font-extrabold mb-0">
                                     <?php
                                     if(isset($total_modal_bulan)) {
                                         echo 'Rp ' . number_format($total_modal_bulan, 0, ',', '.');
                                     } else {
                                         echo '<span class="text-danger">Error</span>';
                                     }
                                     ?>
                                 </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-0">
                        <div class="card my-2">
                            <div class="card-body d-flex align-items-center mb-0">
                                <div class="stats-icon green me-3">
                                    <i class="iconly-boldChart"></i>
                                </div>
                                <div>
                                    <h6 class="text-muted font-semibold">Profit Bulanan</h6>
                                    <h6 class="font-extrabold mb-0">
                                     <?php
                                     if(isset($total_profit_bulan)) {
                                         $color_class = $total_profit_bulan >= 0 ? 'text-success' : 'text-danger';
                                         echo '<span class="' . $color_class . '">Rp ' . number_format($total_profit_bulan, 0, ',', '.') . '</span>';
                                     } else {
                                         echo '<span class="text-danger">Error</span>';
                                     }
                                     ?>
                                 </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
      <div class="container-fluid mt-4">
        <div class="row">
          <div class="col-sm-6 p-0 mx-auto">
            <div class="card card-info">
              <div class="card-header pb-0"> Line Chart
                <h3 class="card-title">Penjualan Perbulan</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body mb-4">
                <div class="chart">
                  <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <div class="col-sm-6 pl-2">
            <!-- DONUT CHART -->
            <div class="card card-danger">
              <div class="card-header pb-0"> Donut Chart
                <h3 class="card-title">Penjualan Per Kategori</h3>

                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-tool" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <div class="card-body mb-4">
                <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
        </div>
    </section>
</div>
          





