<?php
/**
 * Halaman Manajemen Harga Emas
 */

require_once __DIR__ . '/../../models/HargaEmasModel.php';
require_once __DIR__ . '/../../controllers/ScraperController.php';

$hargaEmasModel = new HargaEmasModel();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 30;
$offset = ($page - 1) * $perPage;

$conn = getDBConnection();

// Get total records
$stmt = $conn->query("SELECT COUNT(*) as total FROM harga_emas");
$totalRecords = $stmt->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Get harga emas
$stmt = $conn->prepare("SELECT * FROM harga_emas ORDER BY tanggal DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$hargaList = $result->fetch_all(MYSQLI_ASSOC);

// Get harga hari ini
$hargaHariIni = $hargaEmasModel->getHargaHariIni();
if (!$hargaHariIni) {
    $hargaHariIni = $hargaEmasModel->getHargaTerakhir();
}

// Get statistik harga
$stmt = $conn->query("
    SELECT 
        MIN(harga_24k) as min_24k,
        MAX(harga_24k) as max_24k,
        AVG(harga_24k) as avg_24k
    FROM harga_emas
");
$statistikHarga = $stmt->fetch_assoc();

$pageTitle = 'Manajemen Harga Emas';
ob_start();
?>
<div class="row mb-4">
    <!-- Harga Hari Ini -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Harga Emas Hari Ini</h5>
                <form method="POST" action="<?php echo BASE_URL; ?>backoffice.php?action=update_harga" class="d-inline">
                    <button type="submit" class="btn btn-gold btn-sm">
                        <i class="bi bi-arrow-clockwise"></i> Update Harga
                    </button>
                </form>
            </div>
            <div class="card-body">
                <?php if ($hargaHariIni): ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-2">24K</h6>
                                <h4 class="mb-0"><?php echo formatRupiah($hargaHariIni['harga_24k']); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-2">22K</h6>
                                <h4 class="mb-0"><?php echo formatRupiah($hargaHariIni['harga_22k']); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-2">18K</h6>
                                <h4 class="mb-0"><?php echo formatRupiah($hargaHariIni['harga_18k']); ?></h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3 border rounded">
                                <h6 class="text-muted mb-2">10K</h6>
                                <h4 class="mb-0"><?php echo formatRupiah($hargaHariIni['harga_10k']); ?></h4>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center text-muted">
                        <small>Terakhir update: <?php echo date('d M Y H:i:s', strtotime($hargaHariIni['updated_at'] ?? $hargaHariIni['created_at'])); ?></small>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> Belum ada data harga emas. Silakan update harga terlebih dahulu.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Statistik Harga -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Statistik Harga 24K</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h6 class="text-muted mb-2">Harga Terendah</h6>
                            <h4 class="text-danger mb-0"><?php echo formatRupiah($statistikHarga['min_24k'] ?? 0); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h6 class="text-muted mb-2">Harga Tertinggi</h6>
                            <h4 class="text-success mb-0"><?php echo formatRupiah($statistikHarga['max_24k'] ?? 0); ?></h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 border rounded">
                            <h6 class="text-muted mb-2">Rata-rata</h6>
                            <h4 class="text-primary mb-0"><?php echo formatRupiah($statistikHarga['avg_24k'] ?? 0); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Harga Emas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>24K</th>
                        <th>22K</th>
                        <th>18K</th>
                        <th>10K</th>
                        <th>Terakhir Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($hargaList)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada data harga emas</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($hargaList as $harga): ?>
                            <tr>
                                <td><strong><?php echo date('d M Y', strtotime($harga['tanggal'])); ?></strong></td>
                                <td><?php echo formatRupiah($harga['harga_24k']); ?></td>
                                <td><?php echo formatRupiah($harga['harga_22k']); ?></td>
                                <td><?php echo formatRupiah($harga['harga_18k']); ?></td>
                                <td><?php echo formatRupiah($harga['harga_10k']); ?></td>
                                <td><small class="text-muted"><?php echo date('H:i:s', strtotime($harga['updated_at'] ?? $harga['created_at'])); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=harga_emas&page=<?php echo $page - 1; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?action=harga_emas&page=<?php echo $i; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=harga_emas&page=<?php echo $page + 1; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
        
        <div class="text-center text-muted mt-3">
            Menampilkan <?php echo count($hargaList); ?> dari <?php echo number_format($totalRecords); ?> record
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

