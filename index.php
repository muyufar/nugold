<?php

/**
 * Entry Point Utama Sistem Tabungan Emas Digital
 * Menangani routing dan autentikasi
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/google_oauth.php';
require_once __DIR__ . '/models/UserModel.php';
require_once __DIR__ . '/controllers/ScraperController.php';

// Ambil action dari URL
$action = $_GET['action'] ?? 'dashboard';

// Handle routing
switch ($action) {
    case 'login':
        // Redirect ke Google OAuth
        $authUrl = getGoogleAuthUrl();
        redirect($authUrl);
        break;

    case 'google_callback':
        // Handle callback dari Google OAuth
        if (isset($_GET['code'])) {
            $code = $_GET['code'];
            $accessToken = getGoogleAccessToken($code);

            if ($accessToken) {
                $userInfo = getGoogleUserInfo($accessToken);

                if ($userInfo) {
                    $userModel = new UserModel();
                    $userId = $userModel->loginOrCreate(
                        $userInfo['id'],
                        $userInfo['name'],
                        $userInfo['email'],
                        $userInfo['picture'] ?? ''
                    );

                    if ($userId) {
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['user_name'] = $userInfo['name'];
                        $_SESSION['user_email'] = $userInfo['email'];
                        $_SESSION['user_photo'] = $userInfo['picture'] ?? '';

                        redirect(BASE_URL . 'index.php?action=dashboard');
                    }
                }
            }
        }

        // Jika gagal, redirect ke login
        redirect(BASE_URL . 'index.php?action=login&error=1');
        break;

    case 'logout':
        session_destroy();
        redirect(BASE_URL);
        break;

    case 'dashboard':
        requireLogin();
        include __DIR__ . '/views/dashboard.php';
        break;

    case 'tambah_emas':
        requireLogin();
        include __DIR__ . '/views/tambah_emas.php';
        break;

    case 'edit_emas':
        requireLogin();
        include __DIR__ . '/views/edit_emas.php';
        break;

    case 'hapus_emas':
        requireLogin();
        include __DIR__ . '/views/hapus_emas.php';
        break;

    case 'riwayat_harga':
        requireLogin();
        include __DIR__ . '/views/riwayat_harga.php';
        break;

    case 'pengaturan_alert':
        requireLogin();
        include __DIR__ . '/views/pengaturan_alert.php';
        break;

    case 'profil':
        requireLogin();
        include __DIR__ . '/views/profil.php';
        break;

    case 'api_scrape_harga':
        // API endpoint untuk update harga (bisa dipanggil via cron)
        $scraper = new ScraperController();
        $result = $scraper->updateHargaEmas();
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
        exit;
        break;

    default:
        // Halaman landing atau redirect ke login jika belum login
        if (isLoggedIn()) {
            redirect(BASE_URL . 'index.php?action=dashboard');
        } else {
            include __DIR__ . '/views/login.php';
        }
        break;
}
