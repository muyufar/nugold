<?php

/**
 * Halaman Riwayat Harga Emas
 */

require_once __DIR__ . '/../models/HargaEmasModel.php';
require_once __DIR__ . '/../controllers/ScraperController.php';

$hargaEmasModel = new HargaEmasModel();
$scraper = new ScraperController();

// Update harga emas terbaru dari website (jika refresh atau belum ada data hari ini)
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] == '1';
$hargaHariIni = $hargaEmasModel->getHargaHariIni();

if ($forceRefresh || !$hargaHariIni) {
    $scraper->updateHargaEmas();
    if ($forceRefresh) {
        $_SESSION['success_message'] = 'Data harga berhasil diupdate dari website!';
    }
}

// Ambil periode yang dipilih
$periode = intval($_GET['periode'] ?? 7);

// Ambil riwayat harga dari database
$riwayatHarga = $hargaEmasModel->getRiwayatHarga($periode);

$pageTitle = 'Riwayat Harga';
ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Riwayat Harga Emas</h5>
                <div class="d-flex gap-2 align-items-center">
                    <div class="btn-group" role="group">
                        <a href="?action=riwayat_harga&periode=7" class="btn btn-sm btn-outline-<?php echo $periode === 7 ? 'primary' : 'secondary'; ?>">7 Hari</a>
                        <a href="?action=riwayat_harga&periode=30" class="btn btn-sm btn-outline-<?php echo $periode === 30 ? 'primary' : 'secondary'; ?>">30 Hari</a>
                        <a href="?action=riwayat_harga&periode=90" class="btn btn-sm btn-outline-<?php echo $periode === 90 ? 'primary' : 'secondary'; ?>">90 Hari</a>
                    </div>
                    <a href="?action=riwayat_harga&periode=<?php echo $periode; ?>&refresh=1" class="btn btn-sm btn-gold" title="Refresh Data dari Website">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (empty($riwayatHarga)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">Belum ada data riwayat harga</p>
                        <p class="text-muted small">Klik tombol "Refresh" untuk mengambil data terbaru dari website</p>
                    </div>
                <?php else: ?>
                    <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                        <canvas id="riwayatChart"></canvas>
                    </div>

                    <div class="table-responsive mt-4">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Harga 24K (Rp/gram)</th>
                                    <th>Harga 22K (Rp/gram)</th>
                                    <th>Harga 18K (Rp/gram)</th>
                                    <th>Harga 10K (Rp/gram)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_reverse($riwayatHarga) as $harga): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($harga['tanggal'])); ?></td>
                                        <td><strong><?php echo formatRupiah($harga['harga_24k']); ?></strong></td>
                                        <td><?php echo formatRupiah($harga['harga_22k']); ?></td>
                                        <td><?php echo formatRupiah($harga['harga_18k']); ?></td>
                                        <td><?php echo formatRupiah($harga['harga_10k'] ?? 0); ?></td>
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

<script>
    <?php if (!empty($riwayatHarga)): ?>
        const ctx = document.getElementById('riwayatChart');
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
                            fill: true,
                            pointRadius: window.innerWidth <= 768 ? 3 : 4,
                            pointHoverRadius: window.innerWidth <= 768 ? 4 : 5
                        },
                        {
                            label: '22K',
                            data: harga22k,
                            borderColor: '#FFD700',
                            backgroundColor: 'rgba(255, 215, 0, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: window.innerWidth <= 768 ? 3 : 4,
                            pointHoverRadius: window.innerWidth <= 768 ? 4 : 5
                        },
                        {
                            label: '18K',
                            data: harga18k,
                            borderColor: '#FFA500',
                            backgroundColor: 'rgba(255, 165, 0, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: window.innerWidth <= 768 ? 3 : 4,
                            pointHoverRadius: window.innerWidth <= 768 ? 4 : 5
                        },
                        {
                            label: '10K',
                            data: harga10k,
                            borderColor: '#CD7F32',
                            backgroundColor: 'rgba(205, 127, 50, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointRadius: window.innerWidth <= 768 ? 3 : 4,
                            pointHoverRadius: window.innerWidth <= 768 ? 4 : 5
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