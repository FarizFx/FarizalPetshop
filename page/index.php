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

    $bulan_ini = date('Y-m');
    $total_pendapatan_bulan = 0;
    $query_bulanan = mysqli_query($connection, "
        SELECT COALESCE(SUM(total_harga), 0) as total 
        FROM penjualan 
        WHERE DATE_FORMAT(tanggal, '%Y-%m') = '$bulan_ini'
    ");
    if($query_bulanan) {
        $total_pendapatan_bulan = mysqli_fetch_assoc($query_bulanan)['total'];
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
    <section class="row">
        <div class="col-12 col-lg-12">
            <div class="row">
                <div class="column-gap-3 gap-4 d-flex flex-column flex-md-row">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon blue mb-2">
                                        <i class="iconly-boldProfile"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Jumlah Kategori</h6>
                                    <h6 class="font-extrabold mb-0"><?= $kategori ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon blue mb-2">
                                        <i class="iconly-boldProfile"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Jumlah Produk</h6>
                                    <h6 class="font-extrabold mb-0"><?= $produk ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon blue mb-2">
                                        <i class="iconly-boldProfile"></i>
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Jumlah Pendapatan</h6>
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
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-lg-12 col-xl-10 col-xxl-5 d-flex justify-content-start">
                                    <div class="stats-icon blue mb-2">
                                        <i class="iconly-boldProfile"></i>
                                    </div>
                                </div>
                                <div class="col-md-1 col-lg-12 col-xl-12 col-xxl-7">
                                    <h6 class="text-muted font-semibold">Jumlah Pendapatan Bulanan</h6>
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
                </div>
            </div>
        </div>
    </section>
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6">
            <div class="card card-info">
              <div class="card-header"> Line Chart
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
              <div class="card-body">
                <div class="chart">
                  <canvas id="lineChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

           

            <!-- DONUT CHART -->
            <div class="card card-danger">
              <div class="card-header"> Donut Chart
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
              <div class="card-body">
                <canvas id="donutChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
        </div>
    </section>
</div>
          





