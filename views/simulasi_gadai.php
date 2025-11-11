<?php

/**
 * Halaman Simulasi Gadai
 * Menghitung taksiran maksimal 85% dari nilai emas
 */

require_once __DIR__ . '/../models/EmasModel.php';
require_once __DIR__ . '/../models/HargaEmasModel.php';
require_once __DIR__ . '/../controllers/ScraperController.php';

$userId = getUserId();
$emasModel = new EmasModel();
$hargaEmasModel = new HargaEmasModel();

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

// Hitung nilai jual saat ini dan taksiran gadai
$totalNilaiJual = 0;
$totalTaksiranGadai = 0;
$detailGadai = [];
$persentaseTaksiran = 85; // Maksimal 85%

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
    $taksiranGadai = $nilaiJual * ($persentaseTaksiran / 100);

    $totalNilaiJual += $nilaiJual;
    $totalTaksiranGadai += $taksiranGadai;

    $detailGadai[] = [
        'kadar' => $kadar,
        'berat' => $berat,
        'harga_jual' => $hargaJual,
        'nilai_jual' => $nilaiJual,
        'taksiran_gadai' => $taksiranGadai
    ];
}

$pageTitle = 'Simulasi Gadai';
ob_start();
?>
<div class="row mb-4">
    <div class="col-12">
        <div class="gold-card">
            <h5><i class="bi bi-calculator"></i> Simulasi Gadai Emas</h5>
            <p class="mb-3">Hitung estimasi pinjaman yang bisa Anda dapatkan dengan menggadaikan emas</p>
            <div class="row">
                <div class="col-md-4">
                    <small>Total Nilai Emas Saat Ini</small>
                    <h2><?php echo formatRupiah($totalNilaiJual); ?></h2>
                </div>
                <div class="col-md-4">
                    <small>Taksiran Maksimal (<?php echo $persentaseTaksiran; ?>%)</small>
                    <h2 class="text-success"><?php echo formatRupiah($totalTaksiranGadai); ?></h2>
                </div>
                <div class="col-md-4">
                    <small>Total Berat Emas</small>
                    <h2><?php echo formatAngka($totalBerat, 2); ?> gram</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Gadai</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="bi bi-lightbulb"></i> Cara Kerja Simulasi Gadai:</h6>
                    <ul class="mb-0">
                        <li>Taksiran maksimal adalah <strong><?php echo $persentaseTaksiran; ?>%</strong> dari nilai emas saat ini</li>
                        <li>Nilai emas dihitung berdasarkan harga pasar hari ini</li>
                        <li>Perhitungan dilakukan per kadar emas (24K, 22K, 18K, 10K)</li>
                        <li>Hasil simulasi ini hanya estimasi, nilai sebenarnya dapat berbeda</li>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle"></i> Catatan Penting:</h6>
                    <ul class="mb-0">
                        <li>Nilai taksiran dapat berubah sesuai dengan kondisi fisik emas</li>
                        <li>Harga emas berfluktuasi setiap hari</li>
                        <li>Pastikan emas dalam kondisi baik untuk mendapatkan taksiran maksimal</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Harga Emas Hari Ini</h5>
            </div>
            <div class="card-body">
                <?php if ($hargaHariIni): ?>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <small class="text-muted">24K</small>
                            <h5><?php echo formatRupiah($hargaHariIni['harga_24k']); ?>/gram</h5>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">22K</small>
                            <h5><?php echo formatRupiah($hargaHariIni['harga_22k']); ?>/gram</h5>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">18K</small>
                            <h5><?php echo formatRupiah($hargaHariIni['harga_18k']); ?>/gram</h5>
                        </div>
                        <div class="col-6 mb-3">
                            <small class="text-muted">10K</small>
                            <h5><?php echo formatRupiah($hargaHariIni['harga_10k']); ?>/gram</h5>
                        </div>
                    </div>
                    <small class="text-muted">
                        Update terakhir: <?php echo date('d M Y H:i', strtotime($hargaHariIni['updated_at'] ?? $hargaHariIni['created_at'])); ?>
                    </small>
                <?php else: ?>
                    <p class="text-muted">Data harga emas belum tersedia</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Detail Perhitungan Per Kadar</h5>
    </div>
    <div class="card-body">
        <?php if (empty($detailGadai)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Anda belum memiliki emas. Silakan tambahkan emas terlebih dahulu.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kadar Emas</th>
                            <th>Berat (Gram)</th>
                            <th>Harga/Gram</th>
                            <th>Nilai Jual</th>
                            <th>Taksiran Gadai (<?php echo $persentaseTaksiran; ?>%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detailGadai as $detail): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($detail['kadar']); ?></strong></td>
                                <td><?php echo formatAngka($detail['berat'], 2); ?></td>
                                <td><?php echo formatRupiah($detail['harga_jual']); ?></td>
                                <td><?php echo formatRupiah($detail['nilai_jual']); ?></td>
                                <td><strong class="text-success"><?php echo formatRupiah($detail['taksiran_gadai']); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-active">
                            <th colspan="3">TOTAL</th>
                            <th><?php echo formatRupiah($totalNilaiJual); ?></th>
                            <th><strong class="text-success"><?php echo formatRupiah($totalTaksiranGadai); ?></strong></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calculator"></i> Kalkulator Gadai</h5>
            </div>
            <div class="card-body">
                <p>Hitung taksiran gadai untuk jumlah emas tertentu:</p>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="kadarInput" class="form-label">Kadar Emas</label>
                        <select class="form-select" id="kadarInput">
                            <option value="24K">24K</option>
                            <option value="22K">22K</option>
                            <option value="18K">18K</option>
                            <option value="10K">10K</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="beratInput" class="form-label">Berat (Gram)</label>
                        <input type="number" class="form-control" id="beratInput" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button type="button" class="btn btn-gold w-100" onclick="hitungGadai()">
                            <i class="bi bi-calculator"></i> Hitung
                        </button>
                    </div>
                </div>
                <div id="hasilKalkulator" class="mt-3" style="display: none;">
                    <div class="alert alert-success">
                        <h6>Hasil Perhitungan:</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small>Nilai Emas</small>
                                <h5 id="nilaiEmas">Rp 0</h5>
                            </div>
                            <div class="col-md-4">
                                <small>Taksiran Gadai (<?php echo $persentaseTaksiran; ?>%)</small>
                                <h5 id="taksiranGadai" class="text-success">Rp 0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Data harga emas untuk kalkulator
const hargaEmas = {
    '24K': <?php echo $hargaHariIni['harga_24k'] ?? 0; ?>,
    '22K': <?php echo $hargaHariIni['harga_22k'] ?? 0; ?>,
    '18K': <?php echo $hargaHariIni['harga_18k'] ?? 0; ?>,
    '10K': <?php echo $hargaHariIni['harga_10k'] ?? 0; ?>
};

const persentaseTaksiran = <?php echo $persentaseTaksiran; ?>;

function hitungGadai() {
    const kadar = document.getElementById('kadarInput').value;
    const berat = parseFloat(document.getElementById('beratInput').value) || 0;
    
    if (berat <= 0) {
        alert('Masukkan berat emas yang valid!');
        return;
    }
    
    const hargaPerGram = hargaEmas[kadar];
    if (!hargaPerGram || hargaPerGram <= 0) {
        alert('Harga emas untuk kadar ' + kadar + ' belum tersedia!');
        return;
    }
    
    const nilaiEmas = berat * hargaPerGram;
    const taksiranGadai = nilaiEmas * (persentaseTaksiran / 100);
    
    // Format rupiah
    document.getElementById('nilaiEmas').textContent = formatRupiahJS(nilaiEmas);
    document.getElementById('taksiranGadai').textContent = formatRupiahJS(taksiranGadai);
    
    document.getElementById('hasilKalkulator').style.display = 'block';
}

function formatRupiahJS(angka) {
    return 'Rp ' + angka.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0});
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

