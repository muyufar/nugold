<?php

/**
 * Entry Point Backoffice
 * Sistem admin untuk mengelola aplikasi tabungan emas digital
 */

require_once __DIR__ . '/config/config.php';

// Kredensial statis untuk login backoffice
define('BACKOFFICE_USERNAME', 'yusuf');
define('BACKOFFICE_PASSWORD', 'yusuf');

/**
 * Fungsi helper untuk check apakah admin sudah login
 */
function isBackofficeLoggedIn()
{
    return isset($_SESSION['backoffice_logged_in']) && $_SESSION['backoffice_logged_in'] === true;
}

/**
 * Fungsi helper untuk require login backoffice
 */
function requireBackofficeLogin()
{
    if (!isBackofficeLoggedIn()) {
        redirect(BASE_URL . 'backoffice.php?action=login');
    }
}

// Ambil action dari URL
$action = $_GET['action'] ?? 'login';

// Handle routing
switch ($action) {
    case 'login':
        // Jika sudah login, redirect ke dashboard
        if (isBackofficeLoggedIn()) {
            redirect(BASE_URL . 'backoffice.php?action=dashboard');
        }
        
        // Handle form login
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if ($username === BACKOFFICE_USERNAME && $password === BACKOFFICE_PASSWORD) {
                $_SESSION['backoffice_logged_in'] = true;
                $_SESSION['backoffice_username'] = $username;
                redirect(BASE_URL . 'backoffice.php?action=dashboard');
            } else {
                $error = 'Username atau password salah!';
            }
        }
        
        include __DIR__ . '/views/backoffice/login.php';
        break;

    case 'logout':
        session_destroy();
        redirect(BASE_URL . 'backoffice.php?action=login');
        break;

    case 'dashboard':
        requireBackofficeLogin();
        include __DIR__ . '/views/backoffice/dashboard.php';
        break;

    case 'users':
        requireBackofficeLogin();
        include __DIR__ . '/views/backoffice/users.php';
        break;

    case 'harga_emas':
        requireBackofficeLogin();
        include __DIR__ . '/views/backoffice/harga_emas.php';
        break;

    case 'statistik':
        requireBackofficeLogin();
        include __DIR__ . '/views/backoffice/statistik.php';
        break;

    case 'update_harga':
        requireBackofficeLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once __DIR__ . '/controllers/ScraperController.php';
            $scraper = new ScraperController();
            $result = $scraper->updateHargaEmas();
            if ($result) {
                $_SESSION['success_message'] = 'Harga emas berhasil diupdate!';
            } else {
                $_SESSION['error_message'] = 'Gagal mengupdate harga emas!';
            }
        }
        redirect(BASE_URL . 'backoffice.php?action=harga_emas');
        break;

    default:
        if (isBackofficeLoggedIn()) {
            redirect(BASE_URL . 'backoffice.php?action=dashboard');
        } else {
            redirect(BASE_URL . 'backoffice.php?action=login');
        }
        break;
}

