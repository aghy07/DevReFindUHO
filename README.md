# ReFindUHO Backend

Sistem backend PHP untuk pelaporan barang hilang dan temuan Universitas Halu Oleo.

## Persyaratan

- PHP 8.1 atau lebih baru
- MySQL 8 / MariaDB 10.4 atau lebih baru
- Ekstensi PHP `pdo_mysql` dan `fileinfo`
- Server web Apache/XAMPP

## Instalasi

1. Letakkan folder proyek di document root Apache, misalnya `C:\xampp\htdocs\DevReFindUHO`.
2. Impor `database/schema.sql` melalui phpMyAdmin atau MySQL CLI.
3. Konfigurasi koneksi menggunakan environment variable:
   - `DB_HOST` (default `127.0.0.1`)
   - `DB_PORT` (default `3306`)
   - `DB_NAME` (default `refinduho`)
   - `DB_USER` (default `root`)
   - `DB_PASS` (default kosong)

Contoh untuk PowerShell pada Windows:

```powershell
$env:DB_HOST = "127.0.0.1"
$env:DB_PORT = "3306"
$env:DB_NAME = "refinduho"
$env:DB_USER = "root"
$env:DB_PASS = ""
```

Pengaturan environment Apache dapat ditulis pada VirtualHost menggunakan `SetEnv`.

## Akun Seed

- Email: `admin@uho.ac.id`
- Password: `admin123`

Segera ubah password administrator setelah instalasi di lingkungan produksi.

## Struktur Backend

- `config/koneksi.php`: koneksi PDO, sesi aman, CSRF, flash, dan helper URL.
- `models/`: operasi dan aturan data.
- `controllers/`: validasi serta alur proses aplikasi.
- `database/schema.sql`: skema dan data awal.
