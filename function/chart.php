<?php
// Data untuk Line Chart (Penjualan per bulan)
$lineChartData = [
    'labels' => [],
    'datasets' => [
        [
            'label' => 'Makanan Kucing',
            'data' => [],
            'borderColor' => 'rgba(60,141,188,0.8)',
            'backgroundColor' => 'rgba(60,141,188,0.1)'
        ],
        [
            'label' => 'Pasir Kucing',
            'data' => [],
            'borderColor' => 'rgba(210, 214, 222, 1)',
            'backgroundColor' => 'rgba(210, 214, 222, 0.1)'
        ]
    ]
];

// Data untuk Donut Chart (Penjualan per kategori)
$donutChartData = [
    'labels' => [],
    'datasets' => [
        [
            'data' => [],
            'backgroundColor' => ['#f56954', '#00a65a', '#f39c12', '#00c0ef', '#3c8dbc', '#d2d6de']
        ]
    ]
];

// Query untuk Line Chart (Penjualan per bulan)
$queryLine = mysqli_query($connection, "
    SELECT 
        DATE_FORMAT(p.tanggal, '%Y-%m') as bulan,
        SUM(CASE WHEN pr.id_kategori = 1 THEN dp.subtotal ELSE 0 END) as makanan_kucing,
        SUM(CASE WHEN pr.id_kategori = 2 THEN dp.subtotal ELSE 0 END) as pasir_kucing
    FROM penjualan p
    JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
    JOIN produk pr ON dp.id_produk = pr.id_produk
    GROUP BY DATE_FORMAT(p.tanggal, '%Y-%m')
    ORDER BY p.tanggal
");

if ($queryLine) {
    while ($row = mysqli_fetch_assoc($queryLine)) {
        $lineChartData['labels'][] = date('M Y', strtotime($row['bulan']));
        $lineChartData['datasets'][0]['data'][] = $row['makanan_kucing'];
        $lineChartData['datasets'][1]['data'][] = $row['pasir_kucing'];
    }
}

// Query untuk Donut Chart (Total penjualan per kategori)
$queryDonut = mysqli_query($connection, "
    SELECT 
        k.nama_kategori,
        SUM(dp.subtotal) as total
    FROM detail_penjualan dp
    JOIN produk pr ON dp.id_produk = pr.id_produk
    JOIN kategori k ON pr.id_kategori = k.id_kategori
    GROUP BY k.id_kategori
");

if ($queryDonut) {
    while ($row = mysqli_fetch_assoc($queryDonut)) {
        $donutChartData['labels'][] = $row['nama_kategori'];
        $donutChartData['datasets'][0]['data'][] = $row['total'];
    }
}

// Konversi data ke JSON untuk digunakan di JavaScript
$lineChartJson = json_encode($lineChartData);
$donutChartJson = json_encode($donutChartData);
?>

<!-- jQuery -->
<script src="https://adminlte.io/themes/v3/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="https://adminlte.io/themes/v3/plugins/chart.js/Chart.min.js"></script>
<!-- AdminLTE App -->
<script src="https://adminlte.io/themes/v3/dist/js/adminlte.min.js?v=3.2.0"></script>
<!-- AdminLTE for demo purposes -->
<script src="https://adminlte.io/themes/v3/dist/js/adminlte.min.js?v=3.2.0"></script>

<script>
    $(function () {
    // Parse data dari PHP ke JavaScript
    var lineChartData = <?php echo $lineChartJson; ?>;
    var donutChartData = <?php echo $donutChartJson; ?>;

    //-------------
    //- LINE CHART -
    //--------------
    var lineChartCanvas = $('#lineChart').get(0).getContext('2d');
    var lineChartOptions = {
      maintainAspectRatio: false,
      responsive: true,
      legend: {
        display: true
      },
      scales: {
        xAxes: [{
          gridLines: {
            display: true,
          }
        }],
        yAxes: [{
          gridLines: {
            display: true,
          },
          ticks: {
            beginAtZero: true,
            callback: function(value) {
              return 'Rp ' + value.toLocaleString();
            }
          }
        }]
      },
      tooltips: {
        callbacks: {
          label: function(tooltipItem, data) {
            var label = data.datasets[tooltipItem.datasetIndex].label || '';
            if (label) {
              label += ': ';
            }
            label += 'Rp ' + tooltipItem.yLabel.toLocaleString();
            return label;
          }
        }
      }
    };

    new Chart(lineChartCanvas, {
      type: 'line',
      data: lineChartData,
      options: lineChartOptions
    });

    //-------------
    //- DONUT CHART -
    //-------------
    var donutChartCanvas = $('#donutChart').get(0).getContext('2d');
    var donutOptions = {
      maintainAspectRatio: false,
      responsive: true,
      tooltips: {
        callbacks: {
          label: function(tooltipItem, data) {
            var label = data.labels[tooltipItem.index] || '';
            if (label) {
              label += ': ';
            }
            label += 'Rp ' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index].toLocaleString();
            return label;
          }
        }
      }
    };

    new Chart(donutChartCanvas, {
      type: 'doughnut',
      data: donutChartData,
      options: donutOptions
    });
  });
</script>

<script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015" integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ==" data-cf-beacon='{"rayId":"962ff110a8de831b","serverTiming":{"name":{"cfExtPri":true,"cfEdge":true,"cfOrigin":true,"cfL4":true,"cfSpeedBrain":true,"cfCacheStatus":true}},"version":"2025.7.0","token":"2437d112162f4ec4b63c3ca0eb38fb20"}' crossorigin="anonymous"></script>