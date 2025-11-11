<?php

/**
 * Halaman Pengaturan Alert Harga
 */

require_once __DIR__ . '/../models/AlertModel.php';

$userId = getUserId();
$alertModel = new AlertModel();

$error = '';
$success = '';

// Ambil data alert user
$alert = $alertModel->getByUserId($userId);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hargaMin = !empty($_POST['harga_min']) ? floatval($_POST['harga_min']) : null;
    $hargaMax = !empty($_POST['harga_max']) ? floatval($_POST['harga_max']) : null;
    $statusNotifikasi = sanitize($_POST['status_notifikasi'] ?? 'aktif');

    // Validasi
    if ($hargaMin !== null && $hargaMax !== null && $hargaMin >= $hargaMax) {
        $error = 'Harga minimum harus lebih kecil dari harga maksimum!';
    } else {
        $result = $alertModel->saveOrUpdate($userId, $hargaMin, $hargaMax, $statusNotifikasi);
        if ($result) {
            $success = 'Pengaturan alert berhasil disimpan!';
            $alert = $alertModel->getByUserId($userId); // Refresh data
        } else {
            $error = 'Gagal menyimpan pengaturan. Silakan coba lagi.';
        }
    }
}

$pageTitle = 'Pengaturan Alert';
ob_start();
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bell"></i> Pengaturan Notifikasi Harga</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Status Notifikasi</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_notifikasi" id="status_aktif"
                                value="aktif" <?php echo (!$alert || $alert['status_notifikasi'] === 'aktif') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="status_aktif">
                                Aktif
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="status_notifikasi" id="status_nonaktif"
                                value="nonaktif" <?php echo ($alert && $alert['status_notifikasi'] === 'nonaktif') ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="status_nonaktif">
                                Nonaktif
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="harga_min" class="form-label">Harga Minimum (Rp/gram)</label>
                        <input type="number" class="form-control" id="harga_min" name="harga_min"
                            step="0.01" min="0"
                            value="<?php echo $alert && $alert['harga_min'] ? htmlspecialchars($alert['harga_min']) : ''; ?>"
                            placeholder="Kosongkan jika tidak ingin mengatur">
                        <small class="form-text text-muted">
                            Sistem akan mengirim notifikasi jika harga emas turun di bawah batas ini
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="harga_max" class="form-label">Harga Maksimum (Rp/gram)</label>
                        <input type="number" class="form-control" id="harga_max" name="harga_max"
                            step="0.01" min="0"
                            value="<?php echo $alert && $alert['harga_max'] ? htmlspecialchars($alert['harga_max']) : ''; ?>"
                            placeholder="Kosongkan jika tidak ingin mengatur">
                        <small class="form-text text-muted">
                            Sistem akan mengirim notifikasi jika harga emas naik di atas batas ini
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Info:</strong> Notifikasi akan muncul di dashboard ketika harga emas mencapai batas yang Anda tentukan.
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-gold">
                            <i class="bi bi-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="bi bi-question-circle"></i> Cara Kerja Alert</h6>
                <ul class="mb-0 small">
                    <li>Setelah mengatur batas harga, sistem akan memantau harga emas secara otomatis</li>
                    <li>Notifikasi akan muncul di dashboard ketika harga mencapai batas yang ditentukan</li>
                    <li>Anda bisa mengatur hanya harga minimum, hanya maksimum, atau keduanya</li>
                    <li>Nonaktifkan notifikasi jika tidak ingin menerima alert</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>