<?php
/**
 * Model untuk Alert Harga
 * Menangani operasi database terkait alert harga emas
 */

require_once __DIR__ . '/../config/config.php';

class AlertModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Mendapatkan alert user
     */
    public function getByUserId($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM alert_harga WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Menyimpan atau update alert
     */
    public function saveOrUpdate($userId, $hargaMin, $hargaMax, $statusNotifikasi) {
        // Handle NULL values untuk harga_min dan harga_max
        // Jika NULL, gunakan NULL literal dalam query
        if ($hargaMin === null && $hargaMax === null) {
            // Keduanya NULL
            $stmt = $this->conn->prepare("INSERT INTO alert_harga (user_id, harga_min, harga_max, status_notifikasi) VALUES (?, NULL, NULL, ?) ON DUPLICATE KEY UPDATE harga_min = NULL, harga_max = NULL, status_notifikasi = ?, updated_at = CURRENT_TIMESTAMP");
            $stmt->bind_param("iss", $userId, $statusNotifikasi, $statusNotifikasi);
        } elseif ($hargaMin === null) {
            // Hanya harga_min NULL
            $stmt = $this->conn->prepare("INSERT INTO alert_harga (user_id, harga_min, harga_max, status_notifikasi) VALUES (?, NULL, ?, ?) ON DUPLICATE KEY UPDATE harga_min = NULL, harga_max = ?, status_notifikasi = ?, updated_at = CURRENT_TIMESTAMP");
            $stmt->bind_param("idsdss", $userId, $hargaMax, $statusNotifikasi, $hargaMax, $statusNotifikasi);
        } elseif ($hargaMax === null) {
            // Hanya harga_max NULL
            $stmt = $this->conn->prepare("INSERT INTO alert_harga (user_id, harga_min, harga_max, status_notifikasi) VALUES (?, ?, NULL, ?) ON DUPLICATE KEY UPDATE harga_min = ?, harga_max = NULL, status_notifikasi = ?, updated_at = CURRENT_TIMESTAMP");
            $stmt->bind_param("idsdss", $userId, $hargaMin, $statusNotifikasi, $hargaMin, $statusNotifikasi);
        } else {
            // Keduanya tidak NULL
            // Query memiliki 7 placeholder: VALUES (4) + UPDATE (3)
            // Parameter: userId(i), hargaMin(d), hargaMax(d), status(s), hargaMin(d), hargaMax(d), status(s)
            // Type string harus 7 karakter: "iddsdds"
            $stmt = $this->conn->prepare("INSERT INTO alert_harga (user_id, harga_min, harga_max, status_notifikasi) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE harga_min = ?, harga_max = ?, status_notifikasi = ?, updated_at = CURRENT_TIMESTAMP");
            $stmt->bind_param("iddsdds", $userId, $hargaMin, $hargaMax, $statusNotifikasi, $hargaMin, $hargaMax, $statusNotifikasi);
        }
        return $stmt->execute();
    }
    
    /**
     * Cek apakah harga memicu alert
     */
    public function checkAlert($userId, $hargaSaatIni) {
        $alert = $this->getByUserId($userId);
        
        if (!$alert || $alert['status_notifikasi'] !== 'aktif') {
            return null;
        }
        
        $result = array(
            'triggered' => false,
            'type' => null,
            'message' => ''
        );
        
        if ($alert['harga_min'] !== null && $hargaSaatIni < $alert['harga_min']) {
            $result['triggered'] = true;
            $result['type'] = 'min';
            $result['message'] = "Harga emas turun di bawah batas minimum: " . formatRupiah($alert['harga_min']);
        }
        
        if ($alert['harga_max'] !== null && $hargaSaatIni > $alert['harga_max']) {
            $result['triggered'] = true;
            $result['type'] = 'max';
            $result['message'] = "Harga emas naik di atas batas maksimum: " . formatRupiah($alert['harga_max']);
        }
        
        return $result;
    }
}

