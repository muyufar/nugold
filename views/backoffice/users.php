<?php

/**
 * Halaman Manajemen User
 */

require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../models/EmasModel.php';

$userModel = new UserModel();
$emasModel = new EmasModel();
$conn = getDBConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Search
$search = $_GET['search'] ?? '';
$whereClause = '';
if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $whereClause = "WHERE name LIKE ? OR email LIKE ?";
}

// Get total users
if (!empty($whereClause)) {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users $whereClause");
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $totalUsers = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM users");
    $totalUsers = $stmt->fetch_assoc()['total'];
}

$totalPages = ceil($totalUsers / $perPage);

// Get users
if (!empty($whereClause)) {
    // Gunakan integer casting untuk LIMIT dan OFFSET
    $stmt = $conn->prepare("SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset);
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Untuk query tanpa WHERE clause, gunakan query langsung karena LIMIT/OFFSET sudah di-cast ke integer (aman)
    $perPageInt = (int)$perPage;
    $offsetInt = (int)$offset;
    $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT $perPageInt OFFSET $offsetInt");
    if ($result === false) {
        // Jika query gagal, tampilkan error (untuk debugging)
        die("Query error: " . $conn->error);
    }
    $users = $result->fetch_all(MYSQLI_ASSOC);
}

// Hapus duplikat berdasarkan ID (jika ada)
$uniqueUsers = [];
$seenIds = [];
foreach ($users as $user) {
    $userId = $user['id'];
    if (!in_array($userId, $seenIds)) {
        $seenIds[] = $userId;
        $uniqueUsers[] = $user;
    }
}
$users = $uniqueUsers;

// Get emas count for each user
// Gunakan array baru untuk menghindari masalah dengan reference
$usersWithEmas = [];
foreach ($users as $user) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(berat_emas) as total_berat FROM emas_user WHERE user_id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $user['emas_count'] = $result['count'] ?? 0;
    $user['total_berat'] = floatval($result['total_berat'] ?? 0);
    $usersWithEmas[] = $user;
}
$users = $usersWithEmas;

$pageTitle = 'Manajemen User';
ob_start();
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Daftar User</h5>
        <div class="d-flex gap-2">
            <form method="GET" class="d-flex">
                <input type="hidden" name="action" value="users">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari nama/email..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary ms-2">
                    <i class="bi bi-search"></i>
                </button>
                <?php if (!empty($search)): ?>
                    <a href="<?php echo BASE_URL; ?>backoffice.php?action=users" class="btn btn-sm btn-outline-secondary ms-2">
                        <i class="bi bi-x"></i>
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Google ID</th>
                        <th>Jumlah Emas</th>
                        <th>Total Berat (Gram)</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Tidak ada data user</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <?php if ($user['photo']): ?>
                                        <img src="<?php echo htmlspecialchars($user['photo']); ?>"
                                            alt="" class="rounded-circle me-2"
                                            style="width: 30px; height: 30px; object-fit: cover;">
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><small class="text-muted"><?php echo htmlspecialchars(substr($user['google_id'] ?? '-', 0, 20)); ?>...</small></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $user['emas_count']; ?></span>
                                </td>
                                <td>
                                    <strong><?php echo formatAngka($user['total_berat'], 2); ?></strong>
                                </td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>backoffice.php?action=user_detail&id=<?php echo $user['id']; ?>"
                                        class="btn btn-sm btn-outline-info" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
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
                            <a class="page-link" href="?action=users&page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?action=users&page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?action=users&page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <div class="text-center text-muted mt-3">
            Menampilkan <?php echo count($users); ?> dari <?php echo number_format($totalUsers); ?> user
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>