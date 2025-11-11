<?php
/**
 * Halaman Statistik dan Laporan
 */

require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/EmasModel.php';
require_once __DIR__ . '/../../models/HargaEmasModel.php';

$conn = getDBConnection();
$hargaEmasModel = new HargaEmasModel();

// Statistik User
$stmt = $conn->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$user7hari = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
$user30hari = $stmt->fetch_assoc()['total'];

// Statistik Emas
$stmt = $conn->query("SELECT COUNT(*) as total FROM emas_user");
$totalEmasRecords = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT SUM(berat_emas) as total FROM emas_user");
$totalBeratEmas = floatval($stmt->fetch_assoc()['total'] ?? 0);

$stmt = $conn->query("SELECT SUM(berat_emas * harga_beli) as total FROM emas_user");
$totalNilaiBeli = floatval($stmt->fetch_assoc()['total'] ?? 0);

// Statistik per kadar
$stmt = $conn->query("
    SELECT 
        kadar_emas,
        COUNT(*) as jumlah,
        SUM(berat_emas) as total_berat,
        SUM(berat_emas * harga_beli) as total_nilai
    FROM emas_user
    GROUP BY kadar_emas
    ORDER BY total_berat DESC
");
$statistikKadar = $stmt->fetch_all(MYSQLI_ASSOC);

// Grafik harga 30 hari terakhir
$riwayatHarga = $hargaEmasModel->getRiwayatHarga(30);
$labels = [];
$data24k = [];
$data22k = [];
$data18k = [];

foreach ($riwayatHarga as $harga) {
    $labels[] = date('d M', strtotime($harga['tanggal']));
    $data24k[] = $harga['harga_24k'];
    $data22k[] = $harga['harga_22k'];
    $data18k[] = $harga['harga_18k'];
}

// User growth chart (30 hari terakhir)
$stmt = $conn->query("
    SELECT DATE(created_at) as tanggal, COUNT(*) as jumlah
    FROM users
    WHERE DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY tanggal ASC
");
$userGrowth = $stmt->fetch_all(MYSQLI_ASSOC);
$userLabels = [];
$userData = [];

foreach ($userGrowth as $growth) {
    $userLabels[] = date('d M', strtotime($growth['tanggal']));
    $userData[] = $growth['jumlah'];
}

$pageTitle = 'Statistik & Laporan';
ob_start();
?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Grafik Harga Emas (30 Hari Terakhir)</h5>
            </div>
            <div class="card-body">
                <canvas id="hargaChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Pertumbuhan User (30 Hari Terakhir)</h5>
            </div>
            <div class="card-body">
                <canvas id="userChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Statistik User -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-people"></i> Statistik User</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-muted">Total User</h6>
                    <h3><?php echo number_format($totalUsers); ?></h3>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted">User Baru (7 Hari)</h6>
                    <h4 class="text-primary"><?php echo number_format($user7hari); ?></h4>
                </div>
                <div>
                    <h6 class="text-muted">User Baru (30 Hari)</h6>
                    <h4 class="text-success"><?php echo number_format($user30hari); ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistik Emas -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-gem"></i> Statistik Emas</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-muted">Total Record</h6>
                    <h3><?php echo number_format($totalEmasRecords); ?></h3>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted">Total Berat</h6>
                    <h4 class="text-primary"><?php echo formatAngka($totalBeratEmas, 2); ?> gram</h4>
                </div>
                <div>
                    <h6 class="text-muted">Total Nilai Beli</h6>
                    <h4 class="text-success"><?php echo formatRupiah($totalNilaiBeli); ?></h4>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistik Per Kadar -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Statistik Per Kadar</h5>
            </div>
            <div class="card-body">
                <?php if (empty($statistikKadar)): ?>
                    <p class="text-muted text-center">Belum ada data</p>
                <?php else: ?>
                    <?php foreach ($statistikKadar as $stat): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($stat['kadar_emas']); ?></h6>
                                    <small class="text-muted"><?php echo $stat['jumlah']; ?> record</small>
                                </div>
                                <div class="text-end">
                                    <strong><?php echo formatAngka($stat['total_berat'], 2); ?> g</strong><br>
                                    <small class="text-muted"><?php echo formatRupiah($stat['total_nilai']); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Chart Harga Emas
const ctxHarga = document.getElementById('hargaChart').getContext('2d');
new Chart(ctxHarga, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [
            {
                label: '24K',
                data: <?php echo json_encode($data24k); ?>,
                borderColor: '#ffc700',
                backgroundColor: 'rgba(255, 199, 0, 0.1)',
                tension: 0.4
            },
            {
                label: '22K',
                data: <?php echo json_encode($data22k); ?>,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4
            },
            {
                label: '18K',
                data: <?php echo json_encode($data18k); ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Chart User Growth
const ctxUser = document.getElementById('userChart').getContext('2d');
new Chart(ctxUser, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($userLabels); ?>,
        datasets: [{
            label: 'User Baru',
            data: <?php echo json_encode($userData); ?>,
            backgroundColor: 'rgba(255, 199, 0, 0.8)',
            borderColor: '#ffc700',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

