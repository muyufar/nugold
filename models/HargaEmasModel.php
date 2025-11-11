<?php

/**
 * Model untuk Harga Emas
 * Menangani operasi database terkait harga emas
 */

require_once __DIR__ . '/../config/config.php';

class HargaEmasModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = getDBConnection();
    }

    /**
     * Mendapatkan harga emas hari ini
     */
    public function getHargaHariIni()
    {
        $tanggal = date('Y-m-d');
        $stmt = $this->conn->prepare("SELECT * FROM harga_emas WHERE tanggal = ?");
        $stmt->bind_param("s", $tanggal);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Mendapatkan harga emas berdasarkan tanggal
     */
    public function getHargaByTanggal($tanggal)
    {
        $stmt = $this->conn->prepare("SELECT * FROM harga_emas WHERE tanggal = ?");
        $stmt->bind_param("s", $tanggal);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Menyimpan atau update harga emas
     */
    public function saveOrUpdate($tanggal, $harga24k, $harga22k, $harga18k, $harga10k = null)
    {
        // Jika harga10k tidak diberikan, hitung dari 24K (10/24 = 0.4167)
        if ($harga10k === null) {
            $harga10k = $harga24k * 0.4167;
        }

        // Query: INSERT (5 params) + UPDATE (4 params) = 9 params total
        // Type string: s (tanggal), d (harga24k), d (harga22k), d (harga18k), d (harga10k), d (harga24k), d (harga22k), d (harga18k), d (harga10k) = "sdddddddd" (9 karakter)
        $stmt = $this->conn->prepare("INSERT INTO harga_emas (tanggal, harga_24k, harga_22k, harga_18k, harga_10k) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE harga_24k = ?, harga_22k = ?, harga_18k = ?, harga_10k = ?, updated_at = CURRENT_TIMESTAMP");
        $stmt->bind_param("sdddddddd", $tanggal, $harga24k, $harga22k, $harga18k, $harga10k, $harga24k, $harga22k, $harga18k, $harga10k);
        return $stmt->execute();
    }

    /**
     * Mendapatkan riwayat harga emas (untuk grafik)
     */
    public function getRiwayatHarga($hari = 7)
    {
        $stmt = $this->conn->prepare("SELECT * FROM harga_emas WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY tanggal ASC");
        $stmt->bind_param("i", $hari);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Mendapatkan harga terakhir yang tersimpan
     */
    public function getHargaTerakhir()
    {
        $stmt = $this->conn->prepare("SELECT * FROM harga_emas ORDER BY tanggal DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
