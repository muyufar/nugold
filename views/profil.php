<?php
/**
 * Halaman Profil User
 */

require_once __DIR__ . '/../models/UserModel.php';

$userId = getUserId();
$userModel = new UserModel();
$user = $userModel->findById($userId);

$pageTitle = 'Profil';
ob_start();
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Profil Pengguna</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <?php if ($user['photo']): ?>
                        <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Foto Profil" 
                             class="rounded-circle" style="width: 120px; height: 120px; border: 4px solid #ffc700;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px; border: 4px solid #ffc700;">
                            <i class="bi bi-person" style="font-size: 4rem; color: white;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Nama:</strong></div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Email:</strong></div>
                    <div class="col-sm-8"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Google ID:</strong></div>
                    <div class="col-sm-8"><small class="text-muted"><?php echo htmlspecialchars($user['google_id']); ?></small></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4"><strong>Bergabung:</strong></div>
                    <div class="col-sm-8"><?php echo date('d F Y', strtotime($user['created_at'])); ?></div>
                </div>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>index.php?action=logout" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>

