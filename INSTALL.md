# Panduan Instalasi Sistem Tabungan Emas Digital

## 📋 Langkah-langkah Instalasi

### 1. Persiapan Server

Pastikan server Anda memiliki:

- PHP 7.4+ dengan extension: mysqli, curl, gzip
- MySQL 5.7+
- Apache/Nginx dengan mod_rewrite
- Akses ke Google Cloud Console

### 2. Upload File

Upload semua file ke server hosting Anda (via FTP, cPanel File Manager, atau Git).

### 3. Setup Database

#### Via phpMyAdmin:

1. Login ke phpMyAdmin
2. Buat database baru: `tabungan_emas_db`
3. Pilih database tersebut
4. Klik tab "Import"
5. Pilih file `database.sql`
6. Klik "Go" untuk import

#### Via MySQL CLI:

```bash
mysql -u root -p
CREATE DATABASE tabungan_emas_db;
USE tabungan_emas_db;
SOURCE /path/to/database.sql;
```

### 4. Konfigurasi Database

Edit file `config/database.php`:

```php
define('DB_HOST', 'localhost');        // Host database
define('DB_USER', 'username_db');      // Username database
define('DB_PASS', 'password_db');      // Password database
define('DB_NAME', 'tabungan_emas_db'); // Nama database
```

### 5. Setup Google OAuth

#### Langkah 1: Buat Project di Google Cloud Console

1. Buka https://console.cloud.google.com/
2. Klik "Select a project" → "New Project"
3. Isi nama project (contoh: "Tabungan Emas")
4. Klik "Create"

#### Langkah 2: Enable Google+ API

1. Di sidebar, pilih "APIs & Services" → "Library"
2. Cari "Google+ API"
3. Klik "Enable"

#### Langkah 3: Buat OAuth 2.0 Credentials

1. Pilih "APIs & Services" → "Credentials"
2. Klik "Create Credentials" → "OAuth 2.0 Client ID"
3. Jika diminta, pilih "Configure Consent Screen":

   - User Type: External
   - App name: Tabungan Emas Digital
   - User support email: email Anda
   - Developer contact: email Anda
   - Klik "Save and Continue"
   - Klik "Save and Continue" lagi (skip scopes)
   - Klik "Save and Continue" (skip test users)
   - Klik "Back to Dashboard"

4. Kembali ke "Create OAuth 2.0 Client ID":

   - Application type: Web application
   - Name: Tabungan Emas Web Client
   - Authorized redirect URIs:
     ```
     http://yourdomain.com/index.php?action=google_callback
     ```
     (Ganti dengan domain Anda)
   - Klik "Create"

5. Copy **Client ID** dan **Client Secret**

#### Langkah 4: Update Konfigurasi

Edit file `config/google_oauth.php`:

```php
define('GOOGLE_CLIENT_ID', '123456789-abcdefghijklmnop.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'GOCSPX-abcdefghijklmnopqrstuvwxyz');
```

### 6. Konfigurasi Base URL

Edit file `config/config.php`:

```php
define('BASE_URL', 'http://yourdomain.com/');
```

**Penting:** Pastikan URL diakhiri dengan slash (`/`)

### 7. Set Permission Folder

Buat folder dan set permission:

```bash
mkdir backups
mkdir logs
chmod 755 backups
chmod 755 logs
```

Atau via cPanel File Manager:

- Klik kanan folder → Change Permissions → 755

### 8. Setup Cron Jobs (Opsional)

#### Via cPanel:

1. Login ke cPanel
2. Buka "Cron Jobs"
3. Tambahkan cron job:

**Update Harga (Setiap 1 Jam):**

```
Minute: 0
Hour: *
Day: *
Month: *
Weekday: *
Command: /usr/bin/php /home/username/public_html/cron/update_harga.php
```

**Backup Database (Setiap Minggu):**

```
Minute: 0
Hour: 2
Day: *
Month: *
Weekday: 0
Command: /usr/bin/php /home/username/public_html/cron/backup_database.php
```

**Catatan:** Ganti `/home/username/public_html/` dengan path aplikasi Anda.

#### Cek Path PHP:

```bash
which php
```

### 9. Test Aplikasi

1. Buka browser
2. Akses: `http://yourdomain.com/`
3. Klik "Login dengan Google"
4. Pilih akun Google
5. Setujui permission
6. Anda akan diarahkan ke dashboard

### 10. Troubleshooting

#### Error: "Koneksi database gagal"

- ✅ Cek username, password, dan nama database di `config/database.php`
- ✅ Pastikan database sudah dibuat
- ✅ Cek MySQL service berjalan

#### Error: "Google OAuth failed"

- ✅ Pastikan Client ID dan Secret sudah benar
- ✅ Pastikan redirect URI sudah sesuai (dengan slash di akhir)
- ✅ Pastikan Google+ API sudah di-enable
- ✅ Cek di Google Console: Authorized redirect URIs harus sama persis

#### Harga emas tidak ter-update

- ✅ Cek koneksi internet server
- ✅ Test manual: `php cron/update_harga.php`
- ✅ Cek log di `logs/cron.log`
- ✅ Website target mungkin berubah struktur, update pattern di `ScraperController.php`

#### Dark mode tidak tersimpan

- ✅ Pastikan cookies di-enable
- ✅ Cek browser console untuk error JavaScript

#### Error 500 Internal Server Error

- ✅ Cek error log di cPanel
- ✅ Pastikan PHP version 7.4+
- ✅ Pastikan extension mysqli dan curl aktif
- ✅ Cek permission file dan folder

### 11. Production Checklist

Sebelum go live, pastikan:

- [ ] Set `error_reporting(0)` di `config/config.php`
- [ ] Set `display_errors` ke `0`
- [ ] Gunakan HTTPS (SSL Certificate)
- [ ] Set `session.cookie_secure` ke `1` di `config/config.php` (jika HTTPS)
- [ ] Backup database secara manual
- [ ] Test semua fitur
- [ ] Setup cron jobs
- [ ] Monitor log files

### 12. Support

Jika mengalami masalah:

1. Cek file `logs/cron.log` untuk error
2. Cek error log di cPanel
3. Pastikan semua langkah instalasi sudah benar
4. Cek dokumentasi di `README.md`

---

**Selamat! Sistem Tabungan Emas Digital siap digunakan!** 🎉
