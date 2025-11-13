-- Database Schema untuk Sistem Tabungan Emas Digital
-- Jalankan file ini di phpMyAdmin atau MySQL CLI untuk membuat database

CREATE DATABASE IF NOT EXISTS tabungan_emas_db;
USE tabungan_emas_db;

-- Tabel Users (untuk menyimpan data user dari Google OAuth)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    photo TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Emas User (untuk menyimpan aset emas setiap user)
CREATE TABLE IF NOT EXISTS emas_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kadar_emas VARCHAR(10) NOT NULL COMMENT '24K, 22K, 18K, 10K',
    berat_emas DECIMAL(10, 3) NOT NULL COMMENT 'dalam gram',
    harga_beli DECIMAL(15, 2) NOT NULL COMMENT 'harga beli per gram',
    tanggal_beli DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Harga Emas (untuk menyimpan data harga emas hasil scraping)
CREATE TABLE IF NOT EXISTS harga_emas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    harga_24k DECIMAL(15, 2) NOT NULL,
    harga_22k DECIMAL(15, 2) NOT NULL,
    harga_18k DECIMAL(15, 2) NOT NULL,
    harga_10k DECIMAL(15, 2) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tanggal (tanggal),
    INDEX idx_tanggal (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Alert Harga (untuk konfigurasi notifikasi user)
CREATE TABLE IF NOT EXISTS alert_harga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    harga_min DECIMAL(15, 2) DEFAULT NULL COMMENT 'batas minimum harga per gram',
    harga_max DECIMAL(15, 2) DEFAULT NULL COMMENT 'batas maksimum harga per gram',
    status_notifikasi ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_alert (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Session (opsional, untuk manajemen session)
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT NOT NULL,
    data TEXT,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

