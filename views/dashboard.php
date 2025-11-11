<?php

/**
 * Halaman Dashboard
 * Menampilkan ringkasan aset emas user
 */

require_once __DIR__ . '/../models/EmasModel.php';
require_once __DIR__ . '/../models/HargaEmasModel.php';
require_once __DIR__ . '/../models/AlertModel.php';
require_once __DIR__ . '/../controllers/ScraperController.php';

$userId = getUserId();
$emasModel = new EmasModel();
$hargaEmasModel = new HargaEmasModel();
$alertModel = new AlertModel();

// Update harga emas jika perlu
$scraper = new ScraperController();
$scraper->updateHargaEmas();

// Ambil data emas user
$listEmas = $emasModel->getAllByUserId($userId);
$totalBerat = $emasModel->getTotalBerat($userId);
$totalNilaiBeli = $emasModel->getTotalNilaiBeli($userId);
$totalBeratByKadar = $emasModel->getTotalBeratByKadar($userId);

// Ambil harga emas hari ini
$hargaHariIni = $hargaEmasModel->getHargaHariIni();
if (!$hargaHariIni) {
    // Jika belum ada, gunakan harga terakhir
    $hargaHariIni = $hargaEmasModel->getHargaTerakhir();
}

// Hitung nilai jual saat ini
$totalNilaiJual = 0;
$detailNilaiJual = [];
foreach ($totalBeratByKadar as $item) {
    $kadar = $item['kadar_emas'];
    $berat = floatval($item['total_berat']);

    $hargaJual = 0;
    switch ($kadar) {
        case '24K':
            $hargaJual = floatval($hargaHariIni['harga_24k'] ?? 0);
            break;
        case '22K':
            $hargaJual = floatval($hargaHariIni['harga_22k'] ?? 0);
            break;
        case '18K':
            $hargaJual = floatval($hargaHariIni['harga_18k'] ?? 0);
            break;
        case '10K':
            $hargaJual = floatval($hargaHariIni['harga_10k'] ?? 0);
            break;
    }

    $nilaiJual = $berat * $hargaJual;
    $totalNilaiJual += $nilaiJual;
    $detailNilaiJual[$kadar] = $nilaiJual;
}

// Hitung perubahan harga
$perubahan = $totalNilaiJual - $totalNilaiBeli;
$persentasePerubahan = $totalNilaiBeli > 0 ? ($perubahan / $totalNilaiBeli) * 100 : 0;

// Cek alert
$alertStatus = null;
if ($hargaHariIni) {
    $hargaRataRata = ($hargaHariIni['harga_24k'] + $hargaHariIni['harga_22k'] + $hargaHariIni['harga_18k']) / 3;
    $alertStatus = $alertModel->checkAlert($userId, $hargaRataRata);
}

// Ambil riwayat harga untuk grafik
$riwayatHarga = $hargaEmasModel->getRiwayatHarga(7);

$pageTitle = 'Dashboard';
ob_start();
?>

<!-- Alert Harga -->
<?php if ($alertStatus && $alertStatus['triggered']): ?>
    <div class="alert alert-<?php echo $alertStatus['type'] === 'max' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $alertStatus['type'] === 'max' ? 'arrow-up-circle' : 'arrow-down-circle'; ?>"></i>
        <strong><?php echo $alertStatus['type'] === 'max' ? 'Harga Naik!' : 'Harga Turun!'; ?></strong>
        <?php echo htmlspecialchars($alertStatus['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Card Total Aset -->
<div class="gold-card">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="mb-2"><i class="bi bi-wallet2"></i> Total Aset Emas</h5>
            <h2 class="mb-0"><?php echo formatAngka($totalBerat, 3); ?> gram</h2>
            <p class="mb-0 mt-2">
                <small>Nilai Beli: <?php echo formatRupiah($totalNilaiBeli); ?></small>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <i class="bi bi-gem" style="font-size: 4rem; opacity: 0.3;"></i>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row">
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted mb-2"><i class="bi bi-cash-stack"></i> Nilai Beli Total</h6>
            <h4 class="mb-0"><?php echo formatRupiah($totalNilaiBeli); ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted mb-2"><i class="bi bi-currency-exchange"></i> Nilai Jual Saat Ini</h6>
            <h4 class="mb-0"><?php echo formatRupiah($totalNilaiJual); ?></h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h6 class="text-muted mb-2">
                <i class="bi bi-<?php echo $perubahan >= 0 ? 'arrow-up' : 'arrow-down'; ?>-circle"></i>
                Perubahan Harga
            </h6>
            <h4 class="mb-0 <?php echo $perubahan >= 0 ? 'text-success' : 'text-danger'; ?>">
                <?php echo formatRupiah($perubahan); ?>
                <small class="d-block">(<?php echo formatAngka($persentasePerubahan, 2); ?>%)</small>
            </h4>
        </div>
    </div>
</div>

<!-- Grafik Harga 7 Hari Terakhir -->
<?php if (!empty($riwayatHarga)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-graph-up"></i> Grafik Harga Emas 7 Hari Terakhir</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                        <canvas id="hargaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Daftar Emas User -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Aset Emas</h5>
                <a href="<?php echo BASE_URL; ?>index.php?action=tambah_emas" class="btn btn-sm btn-gold">
                    <i class="bi bi-plus-circle"></i> Tambah Emas
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($listEmas)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">Belum ada aset emas. Tambahkan emas pertama Anda!</p>
                        <a href="<?php echo BASE_URL; ?>index.php?action=tambah_emas" class="btn btn-gold">
                            <i class="bi bi-plus-circle"></i> Tambah Emas
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Mobile Card View -->
                    <div class="mobile-card-view">
                        <?php foreach ($listEmas as $emas):
                            $hargaJual = 0;
                            switch ($emas['kadar_emas']) {
                                case '24K':
                                    $hargaJual = floatval($hargaHariIni['harga_24k'] ?? 0);
                                    break;
                                case '22K':
                                    $hargaJual = floatval($hargaHariIni['harga_22k'] ?? 0);
                                    break;
                                case '18K':
                                    $hargaJual = floatval($hargaHariIni['harga_18k'] ?? 0);
                                    break;
                                case '10K':
                                    $hargaJual = floatval($hargaHariIni['harga_10k'] ?? 0);
                                    break;
                            }
                            $nilaiBeli = floatval($emas['berat_emas']) * floatval($emas['harga_beli']);
                            $nilaiJual = floatval($emas['berat_emas']) * $hargaJual;
                        ?>
                            <div class="mobile-card">
                                <div class="card-row">
                                    <span class="card-label">Tanggal Beli</span>
                                    <span class="card-value" style="font-size: 1rem;"><?php echo date('d/m/Y', strtotime($emas['tanggal_beli'])); ?></span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Kadar</span>
                                    <span class="card-value"><span class="badge bg-warning text-dark" style="font-size: 0.95rem; padding: 0.35rem 0.65rem;"><?php echo htmlspecialchars($emas['kadar_emas']); ?></span></span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Berat</span>
                                    <span class="card-value" style="font-size: 1.05rem; font-weight: 600;"><?php echo formatAngka($emas['berat_emas'], 3); ?> gram</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Harga Beli/gram</span>
                                    <span class="card-value" style="font-size: 1rem;"><?php echo formatRupiah($emas['harga_beli']); ?></span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Total Nilai Beli</span>
                                    <span class="card-value" style="font-size: 1.05rem; font-weight: 600;"><?php echo formatRupiah($nilaiBeli); ?></span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Nilai Jual Saat Ini</span>
                                    <span class="card-value" style="font-size: 1.05rem; font-weight: 600; color: var(--gold-color);"><?php echo formatRupiah($nilaiJual); ?></span>
                                </div>
                                <div class="card-actions">
                                    <a href="<?php echo BASE_URL; ?>index.php?action=edit_emas&id=<?php echo $emas['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>index.php?action=hapus_emas&id=<?php echo $emas['id']; ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Yakin ingin menghapus?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal Beli</th>
                                    <th>Kadar</th>
                                    <th>Berat (gram)</th>
                                    <th>Harga Beli/gram</th>
                                    <th>Total Nilai Beli</th>
                                    <th>Nilai Jual Saat Ini</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($listEmas as $emas):
                                    $hargaJual = 0;
                                    switch ($emas['kadar_emas']) {
                                        case '24K':
                                            $hargaJual = floatval($hargaHariIni['harga_24k'] ?? 0);
                                            break;
                                        case '22K':
                                            $hargaJual = floatval($hargaHariIni['harga_22k'] ?? 0);
                                            break;
                                        case '18K':
                                            $hargaJual = floatval($hargaHariIni['harga_18k'] ?? 0);
                                            break;
                                        case '10K':
                                            $hargaJual = floatval($hargaHariIni['harga_10k'] ?? 0);
                                            break;
                                    }
                                    $nilaiBeli = floatval($emas['berat_emas']) * floatval($emas['harga_beli']);
                                    $nilaiJual = floatval($emas['berat_emas']) * $hargaJual;
                                ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($emas['tanggal_beli'])); ?></td>
                                        <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($emas['kadar_emas']); ?></span></td>
                                        <td><?php echo formatAngka($emas['berat_emas'], 3); ?></td>
                                        <td><?php echo formatRupiah($emas['harga_beli']); ?></td>
                                        <td><?php echo formatRupiah($nilaiBeli); ?></td>
                                        <td>
                                            <?php echo formatRupiah($nilaiJual); ?>
                                            <small class="d-block text-<?php echo ($nilaiJual - $nilaiBeli) >= 0 ? 'success' : 'danger'; ?>">
                                                <?php echo ($nilaiJual - $nilaiBeli) >= 0 ? '+' : ''; ?><?php echo formatRupiah($nilaiJual - $nilaiBeli); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>index.php?action=edit_emas&id=<?php echo $emas['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>index.php?action=hapus_emas&id=<?php echo $emas['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Simulasi Penjualan -->
<?php if ($totalNilaiJual > 0): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Simulasi Penjualan</h5>
                </div>
                <div class="card-body">
                    <p style="font-size: 1rem; margin-bottom: 1rem;">Jika Anda menjual semua emas hari ini, estimasi uang yang akan diterima:</p>
                    <h3 class="text-warning" style="font-size: 1.75rem; font-weight: bold; margin-bottom: 1rem;"><?php echo formatRupiah($totalNilaiJual); ?></h3>
                    <p class="text-muted mb-0" style="font-size: 0.95rem;">
                        Keuntungan/Rugi:
                        <span class="<?php echo $perubahan >= 0 ? 'text-success' : 'text-danger'; ?>" style="font-weight: 600;">
                            <?php echo formatRupiah($perubahan); ?> (<?php echo formatAngka($persentasePerubahan, 2); ?>%)
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    // Grafik Harga Emas
    <?php if (!empty($riwayatHarga)): ?>
        const ctx = document.getElementById('hargaChart');
        if (ctx) {
            const labels = <?php echo json_encode(array_column($riwayatHarga, 'tanggal')); ?>;
            const harga24k = <?php echo json_encode(array_column($riwayatHarga, 'harga_24k')); ?>;
            const harga22k = <?php echo json_encode(array_column($riwayatHarga, 'harga_22k')); ?>;
            const harga18k = <?php echo json_encode(array_column($riwayatHarga, 'harga_18k')); ?>;
            const harga10k = <?php echo json_encode(array_column($riwayatHarga, 'harga_10k')); ?>;

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                            label: '24K',
                            data: harga24k,
                            borderColor: '#ffc700',
                            backgroundColor: 'rgba(255, 199, 0, 0.1)',
                            tension: 0.4,
                            pointRadius: window.innerWidth <= 768 ? 3 : 4,
                            pointHoverRadius: window.innerWidth <= 768 ? 4 : 5
                        },
                        {
                            label: '22K',
                            data: harga22k,
                            borderColor: '#FFD700',
                            backgroundColor: 'rgba(255, 215, 0, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: '18K',
                            data: harga18k,
                            borderColor: '#FFA500',
                            backgroundColor: 'rgba(255, 165, 0, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: '10K',
                            data: harga10k,
                            borderColor: '#CD7F32',
                            backgroundColor: 'rgba(205, 127, 50, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    aspectRatio: window.innerWidth <= 768 ? 1.5 : 2,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: window.innerWidth <= 768 ? 10 : 12,
                                font: {
                                    size: window.innerWidth <= 768 ? 10 : 12
                                },
                                padding: window.innerWidth <= 768 ? 8 : 10
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                            },
                            titleFont: {
                                size: window.innerWidth <= 768 ? 11 : 13
                            },
                            bodyFont: {
                                size: window.innerWidth <= 768 ? 10 : 12
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                font: {
                                    size: window.innerWidth <= 768 ? 9 : 11
                                },
                                maxRotation: window.innerWidth <= 768 ? 45 : 0,
                                minRotation: window.innerWidth <= 768 ? 45 : 0
                            }
                        },
                        y: {
                            beginAtZero: false,
                            ticks: {
                                font: {
                                    size: window.innerWidth <= 768 ? 9 : 11
                                },
                                callback: function(value) {
                                    if (window.innerWidth <= 768) {
                                        // Short format for mobile
                                        if (value >= 1000000) {
                                            return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                        } else if (value >= 1000) {
                                            return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                        }
                                    }
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        }
    <?php endif; ?>
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>