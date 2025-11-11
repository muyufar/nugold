<?php
/**
 * Halaman Edit Emas
 */

require_once __DIR__ . '/../models/EmasModel.php';

$userId = getUserId();
$emasModel = new EmasModel();

$error = '';
$success = '';
$emas = null;

// Ambil data emas
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $emas = $emasModel->findById($id, $userId);
    if (!$emas) {
        redirect(BASE_URL . 'index.php?action=dashboard');
    }
} else {
    redirect(BASE_URL . 'index.php?action=dashboard');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kadarEmas = sanitize($_POST['kadar_emas'] ?? '');
    $beratEmas = floatval($_POST['berat_emas'] ?? 0);
    $hargaBeli = floatval($_POST['harga_beli'] ?? 0);
    $tanggalBeli = sanitize($_POST['tanggal_beli'] ?? date('Y-m-d'));
    
    // Validasi
    if (empty($kadarEmas) || $beratEmas <= 0 || $hargaBeli <= 0) {
        $error = 'Semua field harus diisi dengan benar!';
    } else {
        $result = $emasModel->update($id, $userId, $kadarEmas, $beratEmas, $hargaBeli, $tanggalBeli);
        if ($result) {
            $success = 'Data emas berhasil diupdate!';
            header("refresh:2;url=" . BASE_URL . "index.php?action=dashboard");
        } else {
            $error = 'Gagal mengupdate data. Silakan coba lagi.';
        }
    }
}

$pageTitle = 'Edit Emas';
ob_start();
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Aset Emas</h5>
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
                        <p class="mb-0">Mengalihkan ke dashboard...</p>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="kadar_emas" class="form-label">Kadar Emas <span class="text-danger">*</span></label>
                            <select class="form-select" id="kadar_emas" name="kadar_emas" required>
                                <option value="">Pilih Kadar Emas</option>
                                <option value="24K" <?php echo $emas['kadar_emas'] === '24K' ? 'selected' : ''; ?>>24K (Kadar Tertinggi)</option>
                                <option value="22K" <?php echo $emas['kadar_emas'] === '22K' ? 'selected' : ''; ?>>22K</option>
                                <option value="18K" <?php echo $emas['kadar_emas'] === '18K' ? 'selected' : ''; ?>>18K</option>
                                <option value="10K" <?php echo $emas['kadar_emas'] === '10K' ? 'selected' : ''; ?>>10K</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="berat_emas" class="form-label">Berat Emas (gram) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="berat_emas" name="berat_emas" 
                                   step="0.001" min="0.001" required 
                                   value="<?php echo htmlspecialchars($emas['berat_emas']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="harga_beli" class="form-label">Harga Beli per Gram (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="harga_beli" name="harga_beli" 
                                   step="0.01" min="0.01" required 
                                   value="<?php echo htmlspecialchars($emas['harga_beli']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="tanggal_beli" class="form-label">Tanggal Beli <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_beli" name="tanggal_beli" 
                                   value="<?php echo htmlspecialchars($emas['tanggal_beli']); ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?php echo BASE_URL; ?>index.php?action=dashboard" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-gold">
                                <i class="bi bi-save"></i> Update
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

