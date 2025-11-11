<?php
/**
 * Controller untuk Backup Database
 * Menyediakan fungsi untuk backup database otomatis
 */

require_once __DIR__ . '/../config/config.php';

class BackupController {
    private $backupDir;
    
    public function __construct() {
        $this->backupDir = BASE_PATH . 'backups/';
        
        // Buat folder backups jika belum ada
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Backup database ke file SQL
     */
    public function backupDatabase() {
        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $this->backupDir . $filename;
        
        // Gunakan mysqldump jika tersedia
        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg(DB_USER),
            escapeshellarg(DB_PASS),
            escapeshellarg(DB_HOST),
            escapeshellarg(DB_NAME),
            escapeshellarg($filepath)
        );
        
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0 && file_exists($filepath)) {
            // Kompres file backup
            $this->compressBackup($filepath);
            return $filename;
        }
        
        // Fallback: backup manual menggunakan PHP
        return $this->backupDatabaseManual($filepath);
    }
    
    /**
     * Backup database secara manual menggunakan PHP
     */
    private function backupDatabaseManual($filepath) {
        $conn = getDBConnection();
        $tables = array();
        $result = $conn->query("SHOW TABLES");
        
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        $sql = "-- Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql .= "SET time_zone = \"+00:00\";\n\n";
        
        foreach ($tables as $table) {
            // Drop table
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            
            // Create table
            $createTable = $conn->query("SHOW CREATE TABLE `$table`");
            $row = $createTable->fetch_row();
            $sql .= $row[1] . ";\n\n";
            
            // Insert data
            $result = $conn->query("SELECT * FROM `$table`");
            if ($result->num_rows > 0) {
                $sql .= "INSERT INTO `$table` VALUES\n";
                $values = array();
                
                while ($row = $result->fetch_assoc()) {
                    $rowValues = array();
                    foreach ($row as $value) {
                        $rowValues[] = $conn->real_escape_string($value);
                    }
                    $values[] = "('" . implode("','", $rowValues) . "')";
                }
                
                $sql .= implode(",\n", $values) . ";\n\n";
            }
        }
        
        if (file_put_contents($filepath, $sql)) {
            $this->compressBackup($filepath);
            return basename($filepath);
        }
        
        return false;
    }
    
    /**
     * Kompres file backup
     */
    private function compressBackup($filepath) {
        if (function_exists('gzencode')) {
            $compressed = gzencode(file_get_contents($filepath));
            $compressedPath = $filepath . '.gz';
            file_put_contents($compressedPath, $compressed);
            
            // Hapus file asli jika kompresi berhasil
            if (file_exists($compressedPath)) {
                unlink($filepath);
            }
        }
    }
    
    /**
     * Hapus backup lama (lebih dari 30 hari)
     */
    public function cleanOldBackups($days = 30) {
        $files = glob($this->backupDir . 'backup_*.sql*');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < (time() - ($days * 24 * 60 * 60))) {
                unlink($file);
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Daftar semua backup yang tersedia
     */
    public function listBackups() {
        $files = glob($this->backupDir . 'backup_*.sql*');
        $backups = array();
        
        foreach ($files as $file) {
            $backups[] = array(
                'filename' => basename($file),
                'size' => filesize($file),
                'date' => date('Y-m-d H:i:s', filemtime($file))
            );
        }
        
        // Sort by date descending
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }
}

