<?php

/**
 * Konfigurasi Umum Sistem
 */

// Base URL aplikasi (sesuaikan dengan domain Anda)
define('BASE_URL', 'http://projek.nu/nugold/');

// Path aplikasi
define('BASE_PATH', __DIR__ . '/../');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set 1 jika menggunakan HTTPS

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (set ke 0 di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database config
require_once __DIR__ . '/database.php';

/**
 * Fungsi helper untuk redirect
 */
function redirect($url)
{
    header("Location: " . $url);
    exit();
}

/**
 * Fungsi helper untuk format rupiah
 */
function formatRupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Fungsi helper untuk format angka dengan desimal
 */
function formatAngka($angka, $desimal = 2)
{
    return number_format($angka, $desimal, ',', '.');
}

/**
 * Fungsi helper untuk sanitize input
 */
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Fungsi helper untuk check apakah user sudah login
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Fungsi helper untuk mendapatkan user ID dari session
 */
function getUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Fungsi helper untuk require login
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'index.php?action=login');
    }
}
