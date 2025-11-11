<?php
/**
 * Halaman Login
 */
$pageTitle = 'Login';
ob_start();
?>
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-gem" style="font-size: 4rem; color: #ffc700;"></i>
                        <h2 class="mt-3 mb-1">Tabungan Emas Digital</h2>
                        <p class="text-muted">Kelola aset emas Anda dengan mudah</p>
                    </div>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> Login gagal. Silakan coba lagi.
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="<?php echo BASE_URL; ?>index.php?action=login" class="btn btn-lg btn-gold">
                            <i class="bi bi-google"></i> Login dengan Google
                        </a>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Dengan login, Anda menyetujui syarat dan ketentuan penggunaan
                        </small>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted small">
                    <i class="bi bi-shield-check"></i> Data Anda aman dan terenkripsi
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .min-vh-100 {
        min-height: 100vh;
    }
</style>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

