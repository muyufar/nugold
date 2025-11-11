<?php
/**
 * Model untuk User
 * Menangani operasi database terkait user
 */

require_once __DIR__ . '/../config/config.php';

class UserModel {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Mencari user berdasarkan Google ID
     */
    public function findByGoogleId($googleId) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->bind_param("s", $googleId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Mencari user berdasarkan email
     */
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Mencari user berdasarkan ID
     */
    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    /**
     * Membuat user baru
     */
    public function create($googleId, $name, $email, $photo) {
        $stmt = $this->conn->prepare("INSERT INTO users (google_id, name, email, photo) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $googleId, $name, $email, $photo);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update data user
     */
    public function update($id, $name, $email, $photo) {
        $stmt = $this->conn->prepare("UPDATE users SET name = ?, email = ?, photo = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $photo, $id);
        return $stmt->execute();
    }
    
    /**
     * Login user (create atau update dari Google)
     */
    public function loginOrCreate($googleId, $name, $email, $photo) {
        $user = $this->findByGoogleId($googleId);
        
        if ($user) {
            // Update data user jika ada perubahan
            $this->update($user['id'], $name, $email, $photo);
            return $user['id'];
        } else {
            // Buat user baru
            return $this->create($googleId, $name, $email, $photo);
        }
    }
}

