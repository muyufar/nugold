<?php
/**
 * Cron Job untuk Backup Database
 * Jalankan script ini setiap minggu (misalnya setiap hari Minggu jam 2 pagi)
 * 
 * Cara setup cron job:
 * - Linux/Unix: Tambahkan ke crontab: 0 2 * * 0 /usr/bin/php /path/to/cron/backup_database.php
 * - Windows: Gunakan Task Scheduler
 * - cPanel: Gunakan Cron Jobs di cPanel
 */

// Set path ke root aplikasi
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/BackupController.php';

// Backup database
$backupController = new BackupController();
$filename = $backupController->backupDatabase();

// Hapus backup lama (lebih dari 30 hari)
$deleted = $backupController->cleanOldBackups(30);

// Log hasil
$logFile = __DIR__ . '/../logs/cron.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$message = date('Y-m-d H:i:s') . ' - Backup database: ' . ($filename ? 'SUCCESS - ' . $filename : 'FAILED');
$message .= ' | Deleted old backups: ' . $deleted . "\n";
file_put_contents($logFile, $message, FILE_APPEND);

echo $message;

