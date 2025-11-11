<?php
/**
 * Cron Job untuk Update Harga Emas
 * Jalankan script ini setiap 1 jam sekali
 * 
 * Cara setup cron job:
 * - Linux/Unix: Tambahkan ke crontab: 0 * * * * /usr/bin/php /path/to/cron/update_harga.php
 * - Windows: Gunakan Task Scheduler
 * - cPanel: Gunakan Cron Jobs di cPanel
 */

// Set path ke root aplikasi
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/ScraperController.php';

// Update harga emas
$scraper = new ScraperController();
$result = $scraper->updateHargaEmas();

// Log hasil
$logFile = __DIR__ . '/../logs/cron.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$message = date('Y-m-d H:i:s') . ' - Update harga emas: ' . ($result ? 'SUCCESS' : 'FAILED') . "\n";
file_put_contents($logFile, $message, FILE_APPEND);

echo $message;

