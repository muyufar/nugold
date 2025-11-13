<?php

/**
 * Konfigurasi Database
 * Sesuaikan dengan setting database server Anda
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tabungan_emas_db');
define('DB_CHARSET', 'utf8mb4');

/**
 * Fungsi untuk membuat koneksi database
 * @return mysqli|null
 */
function getDBConnection()
{
    static $conn = null;

    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

            if ($conn->connect_error) {
                die("Koneksi database gagal: " . $conn->connect_error);
            }

            $conn->set_charset(DB_CHARSET);
        } catch (Exception $e) {
            die("Error koneksi database: " . $e->getMessage());
        }
    }

    return $conn;
}

/**
 * Fungsi untuk menutup koneksi database
 */
function closeDBConnection()
{
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
