# Sistem Tabungan Emas Digital

Sistem manajemen tabungan emas digital berbasis PHP Native dengan fitur lengkap untuk mengelola aset emas, memantau harga, dan menerima notifikasi alert.

## 🚀 Fitur Utama

- ✅ **Autentikasi Google OAuth** - Login menggunakan akun Google
- ✅ **Dashboard Interaktif** - Tampilan ringkasan aset emas dengan grafik
- ✅ **Manajemen Aset Emas** - Tambah, edit, dan hapus data emas
- ✅ **Pemantauan Harga Otomatis** - Scraping harga emas dari website
- ✅ **Sistem Alert Harga** - Notifikasi ketika harga mencapai batas tertentu
- ✅ **Riwayat Harga** - Grafik tren harga emas (7, 30, 90 hari)
- ✅ **Simulasi Penjualan** - Hitung estimasi uang jika menjual semua emas
- ✅ **Dark Mode** - Toggle tema gelap/terang
- ✅ **Backup Database Otomatis** - Backup mingguan dengan kompresi

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Apache/Nginx dengan mod_rewrite
- cURL extension (untuk scraping)
- Google OAuth 2.0 Credentials

## 📦 Instalasi

### 1. Clone atau Download Project

```bash
git clone <repository-url>
cd nugold
```

### 2. Setup Database

1. Buat database baru di phpMyAdmin atau MySQL CLI
2. Import file `database.sql`:
   ```sql
   mysql -u root -p tabungan_emas_db < database.sql
   ```
   Atau melalui phpMyAdmin: Import → Pilih file `database.sql`

### 3. Konfigurasi Database

Edit file `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'password_anda');
define('DB_NAME', 'tabungan_emas_db');
```

### 4. Setup Google OAuth

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project yang sudah ada
3. Enable **Google+ API**
4. Buka **Credentials** → **Create Credentials** → **OAuth 2.0 Client ID**
5. Set **Authorized redirect URIs**:
   ```
   http://localhost/nugold/index.php?action=google_callback
   ```
   (Ganti dengan domain Anda jika sudah di hosting)
6. Copy **Client ID** dan **Client Secret**
7. Edit file `config/google_oauth.php`:

```php
define('GOOGLE_CLIENT_ID', 'YOUR_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_CLIENT_SECRET_HERE');
```

### 5. Konfigurasi Base URL

Edit file `config/config.php`:

```php
define('BASE_URL', 'http://localhost/nugold/');
```

(Ganti dengan URL aplikasi Anda)

### 6. Set Permission Folder

```bash
chmod 755 backups/
chmod 755 logs/
chmod 644 .htaccess
```

### 7. Setup Cron Jobs (Opsional)

Untuk update harga otomatis setiap 1 jam:

```bash
0 * * * * /usr/bin/php /path/to/nugold/cron/update_harga.php
```

Untuk backup database setiap minggu:

```bash
0 2 * * 0 /usr/bin/php /path/to/nugold/cron/backup_database.php
```

**Atau melalui cPanel:**
- Masuk ke **Cron Jobs**
- Tambahkan cron job dengan command di atas

## 🎯 Cara Penggunaan

### 1. Login
- Buka aplikasi di browser
- Klik "Login dengan Google"
- Pilih akun Google yang ingin digunakan

### 2. Dashboard
- Lihat ringkasan total aset emas
- Pantau nilai beli vs nilai jual saat ini
- Lihat grafik harga 7 hari terakhir

### 3. Tambah Emas
- Klik menu "Tambah Emas"
- Isi data: kadar, berat, harga beli, tanggal beli
- Klik "Simpan"

### 4. Pengaturan Alert
- Klik menu "Pengaturan Alert"
- Set harga minimum dan/atau maksimum
- Aktifkan notifikasi
- Sistem akan menampilkan alert di dashboard ketika harga mencapai batas

### 5. Riwayat Harga
- Klik menu "Riwayat Harga"
- Pilih periode: 7, 30, atau 90 hari
- Lihat grafik dan tabel riwayat harga

## 📁 Struktur Folder

```
nugold/
├── config/              # File konfigurasi
│   ├── config.php
│   ├── database.php
│   └── google_oauth.php
├── controllers/         # Controller untuk business logic
│   ├── BackupController.php
│   └── ScraperController.php
├── models/              # Model untuk database operations
│   ├── AlertModel.php
│   ├── EmasModel.php
│   ├── HargaEmasModel.php
│   └── UserModel.php
├── views/               # Template/view files
│   ├── layout.php
│   ├── login.php
│   ├── dashboard.php
│   ├── tambah_emas.php
│   ├── edit_emas.php
│   ├── hapus_emas.php
│   ├── riwayat_harga.php
│   ├── pengaturan_alert.php
│   └── profil.php
├── cron/                # Script cron job
│   ├── update_harga.php
│   └── backup_database.php
├── backups/             # Folder backup database (auto-created)
├── logs/                # Folder log (auto-created)
├── database.sql         # File SQL untuk setup database
├── index.php            # Entry point utama
├── .htaccess            # Apache configuration
└── README.md            # Dokumentasi
```

## 🔒 Keamanan

- ✅ Prepared statements untuk mencegah SQL Injection
- ✅ Input sanitization
- ✅ Session security (httponly cookies)
- ✅ File protection (.htaccess)
- ✅ Password tidak disimpan (menggunakan Google OAuth)

## 🐛 Troubleshooting

### Error: "Koneksi database gagal"
- Pastikan database sudah dibuat
- Cek konfigurasi di `config/database.php`
- Pastikan MySQL service berjalan

### Error: "Google OAuth failed"
- Pastikan Client ID dan Client Secret sudah benar
- Pastikan redirect URI sudah sesuai
- Pastikan Google+ API sudah di-enable

### Harga emas tidak ter-update
- Cek koneksi internet
- Website target mungkin berubah struktur HTML
- Update pattern scraping di `controllers/ScraperController.php`
- Atau jalankan manual: `php cron/update_harga.php`

### Dark mode tidak tersimpan
- Pastikan cookies di-enable di browser
- Cek permission folder

## 📝 Catatan Penting

1. **Scraping Harga**: Pattern scraping di `ScraperController.php` mungkin perlu disesuaikan jika website target berubah struktur HTML-nya.

2. **Backup Database**: Pastikan folder `backups/` memiliki permission write.

3. **Cron Jobs**: Pastikan path PHP di cron job sudah benar. Cek dengan: `which php`

4. **Production**: 
   - Set `error_reporting(0)` di `config/config.php`
   - Set `display_errors` ke `0`
   - Gunakan HTTPS untuk keamanan

## 🤝 Kontribusi

Silakan buat issue atau pull request jika menemukan bug atau ingin menambahkan fitur.

## 📄 Lisensi

Project ini dibuat untuk keperluan edukasi dan komersial.

## 👨‍💻 Developer

Dibuat dengan ❤️ menggunakan PHP Native, Bootstrap 5, dan Chart.js

---

**Selamat menggunakan Sistem Tabungan Emas Digital!** 🏆

