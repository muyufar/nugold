-- SQL untuk menambahkan kolom harga_10k ke tabel harga_emas
-- Jalankan file ini jika database sudah ada dan ingin menambahkan support 10K

USE tabungan_emas_db;

-- Tambah kolom harga_10k
ALTER TABLE harga_emas 
ADD COLUMN harga_10k DECIMAL(15, 2) NOT NULL DEFAULT 0 AFTER harga_18k;

-- Update data yang sudah ada dengan perhitungan 10K (10/24 dari 24K)
UPDATE harga_emas 
SET harga_10k = ROUND(harga_24k * 0.4167, 2)
WHERE harga_10k = 0;

