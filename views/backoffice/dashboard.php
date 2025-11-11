<?php
/**
 * Dashboard Backoffice
 * Menampilkan ringkasan statistik sistem
 */

require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/EmasModel.php';
require_once __DIR__ . '/../../models/HargaEmasModel.php';

$userModel = new UserModel();
$emasModel = new EmasModel();
$hargaEmasModel = new HargaEmasModel();

// Statistik User
$conn = getDBConnection();
$stmt = $conn->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $stmt->fetch_assoc()['total'];

// Statistik Emas
$stmt = $conn->query("SELECT COUNT(*) as total FROM emas_user");
$totalEmasRecords = $stmt->fetch_assoc()['total'];

$stmt = $conn->query("SELECT SUM(berat_emas) as total FROM emas_user");
$totalBeratEmas = floatval($stmt->fetch_assoc()['total'] ?? 0);

$stmt = $conn->query("SELECT SUM(berat_emas * harga_beli) as total FROM emas_user");
$totalNilaiBeli = floatval($stmt->fetch_assoc()['total'] ?? 0);

// Statistik Harga
$stmt = $conn->query("SELECT COUNT(*) as total FROM harga_emas");
$totalHargaRecords = $stmt->fetch_assoc()['total'];

$hargaHariIni = $hargaEmasModel->getHargaHariIni();
if (!$hargaHariIni) {
    $hargaHariIni = $hargaEmasModel->getHargaTerakhir();
}

// User baru hari ini
$stmt = $conn->query("SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = CURDATE()");
$userBaruHariIni = $stmt->fetch_assoc()['total'];

// Emas baru hari ini
$stmt = $conn->query("SELECT COUNT(*) as total FROM emas_user WHERE DATE(created_at) = CURDATE()");
$emasBaruHariIni = $stmt->fetch_assoc()['total'];

// Top 5 user dengan emas terbanyak
$stmt = $conn->query("
    SELECT u.id, u.name, u.email, SUM(e.berat_emas) as total_berat, COUNT(e.id) as jumlah_record
    FROM users u
    LEFT JOIN emas_user e ON u.id = e.user_id
    GROUP BY u.id
    ORDER BY total_berat DESC
    LIMIT 5
");
$topUsers = $stmt->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard';
ob_start();
?>
<div class="row">
    <!-- Statistik Cards -->
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6>Total User</h6>
                    <h3><?php echo number_format($totalUsers); ?></h3>
                    <small class="text-success">
                        <i class="bi bi-arrow-up"></i> +<?php echo $userBaruHariIni; ?> hari ini
                    </small>
                </div>
                <div class="icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6>Total Emas (Gram)</h6>
                    <h3><?php echo formatAngka($totalBeratEmas, 2); ?></h3>
                    <small class="text-muted">
                        <?php echo number_format($totalEmasRecords); ?> record
                    </small>
                </div>
                <div class="icon">
                    <i class="bi bi-gem"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6>Total Nilai Beli</h6>
                    <h3><?php echo formatRupiah($totalNilaiBeli); ?></h3>
                    <small class="text-muted">
                        Investasi total
                    </small>
                </div>
                <div class="icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6>Harga 24K Hari Ini</h6>
                    <h3><?php echo formatRupiah($hargaHariIni['harga_24k'] ?? 0); ?></h3>
                    <small class="text-muted">
                        <?php echo date('d M Y', strtotime($hargaHariIni['tanggal'] ?? 'now')); ?>
                    </small>
                </div>
                <div class="icon">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Users -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 5 User dengan Emas Terbanyak</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Total Berat (Gram)</th>
                                <th>Jumlah Record</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($topUsers) || (count($topUsers) === 1 && $topUsers[0]['total_berat'] === null)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data</td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($topUsers as $user): ?>
                                    <?php if ($user['total_berat'] !== null): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><strong><?php echo formatAngka($user['total_berat'], 2); ?></strong></td>
                                            <td><?php echo $user['jumlah_record']; ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>backoffice.php?action=users" class="btn btn-outline-primary">
                        <i class="bi bi-people"></i> Kelola User
                    </a>
                    <a href="<?php echo BASE_URL; ?>backoffice.php?action=harga_emas" class="btn btn-outline-success">
                        <i class="bi bi-graph-up"></i> Kelola Harga Emas
                    </a>
                    <a href="<?php echo BASE_URL; ?>backoffice.php?action=statistik" class="btn btn-outline-info">
                        <i class="bi bi-bar-chart"></i> Lihat Statistik
                    </a>
                    <form method="POST" action="<?php echo BASE_URL; ?>backoffice.php?action=update_harga" class="d-grid">
                        <button type="submit" class="btn btn-gold">
                            <i class="bi bi-arrow-clockwise"></i> Update Harga Emas
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Info Sistem -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Info Sistem</h5>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    <div class="mb-2">
                        <strong>Total Record Harga:</strong><br>
                        <?php echo number_format($totalHargaRecords); ?> data
                    </div>
                    <div class="mb-2">
                        <strong>Emas Baru Hari Ini:</strong><br>
                        <?php echo $emasBaruHariIni; ?> record
                    </div>
                    <div>
                        <strong>Server Time:</strong><br>
                        <?php echo date('d M Y H:i:s'); ?>
                    </div>
                </small>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

