<?php
/**
 * Model untuk Emas User
 * Menangani operasi database terkait aset emas user
 */

require_once __DIR__ . '/../config/config.php';

class EmasModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Mendapatkan semua emas milik user
     */
    public function getAllByUserId($userId) {
        $stmt = $this->conn->prepare("SELECT * FROM emas_user WHERE user_id = ? ORDER BY tanggal_beli DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Mendapatkan emas berdasarkan ID
     */
    public function findById($id, $userId) {
        $stmt = $this->conn->prepare("SELECT * FROM emas_user WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Menambahkan emas baru
     */
    public function create($userId, $kadarEmas, $beratEmas, $hargaBeli, $tanggalBeli) {
        $stmt = $this->conn->prepare("INSERT INTO emas_user (user_id, kadar_emas, berat_emas, harga_beli, tanggal_beli) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdds", $userId, $kadarEmas, $beratEmas, $hargaBeli, $tanggalBeli);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update emas
     */
    public function update($id, $userId, $kadarEmas, $beratEmas, $hargaBeli, $tanggalBeli) {
        $stmt = $this->conn->prepare("UPDATE emas_user SET kadar_emas = ?, berat_emas = ?, harga_beli = ?, tanggal_beli = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sddsii", $kadarEmas, $beratEmas, $hargaBeli, $tanggalBeli, $id, $userId);
        return $stmt->execute();
    }
    
    /**
     * Hapus emas
     */
    public function delete($id, $userId) {
        $stmt = $this->conn->prepare("DELETE FROM emas_user WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $userId);
        return $stmt->execute();
    }
    
    /**
     * Menghitung total berat emas per kadar
     */
    public function getTotalBeratByKadar($userId) {
        $stmt = $this->conn->prepare("SELECT kadar_emas, SUM(berat_emas) as total_berat, SUM(berat_emas * harga_beli) as total_nilai_beli FROM emas_user WHERE user_id = ? GROUP BY kadar_emas");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Menghitung total nilai beli
     */
    public function getTotalNilaiBeli($userId) {
        $stmt = $this->conn->prepare("SELECT SUM(berat_emas * harga_beli) as total FROM emas_user WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
    
    /**
     * Menghitung total berat emas semua kadar
     */
    public function getTotalBerat($userId) {
        $stmt = $this->conn->prepare("SELECT SUM(berat_emas) as total FROM emas_user WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }
}

