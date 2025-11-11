<?php
/**
 * File Test Koneksi
 * Gunakan file ini untuk test koneksi database dan konfigurasi
 * HAPUS file ini setelah setup selesai untuk keamanan!
 */

// Test Database Connection
echo "<h2>Test Koneksi Database</h2>";

require_once __DIR__ . '/config/database.php';

try {
    $conn = getDBConnection();
    if ($conn) {
        echo "<p style='color: green;'>✓ Koneksi database BERHASIL</p>";
        
        // Test query
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            echo "<p style='color: green;'>✓ Query database BERHASIL</p>";
            echo "<p>Tables yang ditemukan:</p><ul>";
            while ($row = $result->fetch_row()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: red;'>✗ Query database GAGAL</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Koneksi database GAGAL</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test Google OAuth Config
echo "<h2>Test Konfigurasi Google OAuth</h2>";

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/google_oauth.php';

if (defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== 'YOUR_GOOGLE_CLIENT_ID_HERE') {
    echo "<p style='color: green;'>✓ Google Client ID sudah dikonfigurasi</p>";
} else {
    echo "<p style='color: orange;'>⚠ Google Client ID belum dikonfigurasi</p>";
}

if (defined('GOOGLE_CLIENT_SECRET') && GOOGLE_CLIENT_SECRET !== 'YOUR_GOOGLE_CLIENT_SECRET_HERE') {
    echo "<p style='color: green;'>✓ Google Client Secret sudah dikonfigurasi</p>";
} else {
    echo "<p style='color: orange;'>⚠ Google Client Secret belum dikonfigurasi</p>";
}

// Test Base URL
echo "<h2>Test Konfigurasi Base URL</h2>";
echo "<p>Base URL: <strong>" . BASE_URL . "</strong></p>";
echo "<p>Current URL: <strong>" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</strong></p>";

// Test PHP Extensions
echo "<h2>Test PHP Extensions</h2>";

$required = ['mysqli', 'curl', 'json', 'session'];
foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✓ Extension $ext tersedia</p>";
    } else {
        echo "<p style='color: red;'>✗ Extension $ext TIDAK tersedia</p>";
    }
}

// Test Folder Permissions
echo "<h2>Test Permission Folder</h2>";

$folders = ['backups', 'logs'];
foreach ($folders as $folder) {
    $path = __DIR__ . '/' . $folder;
    if (is_dir($path)) {
        if (is_writable($path)) {
            echo "<p style='color: green;'>✓ Folder $folder dapat ditulis</p>";
        } else {
            echo "<p style='color: orange;'>⚠ Folder $folder TIDAK dapat ditulis (perlu chmod 755)</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Folder $folder belum ada (akan dibuat otomatis)</p>";
    }
}

echo "<hr>";
echo "<p><strong>PENTING:</strong> Hapus file ini setelah setup selesai untuk keamanan!</p>";
echo "<p><a href='index.php'>Kembali ke Aplikasi</a></p>";

