# 10. Panduan Deployment dan Pemeliharaan Server Produksi

Dokumen ini memandu proses pemasangan (*deployment*), konfigurasi server, optimasi performa, otomasi penjadwalan (*cron scheduler*), serta pemeliharaan berkala sistem **Stokify** pada lingkungan produksi (*production VPS / Cloud / Dedicated Server*).

---

## 1. Spesifikasi Server Produksi yang Disarankan

- **Sistem Operasi**: Ubuntu 22.04 LTS / 24.04 LTS (atau Debian 12)
- **Web Server**: Nginx 1.22+
- **PHP**: PHP 8.2 atau 8.3 FPM dengan ekstensi:
  `php8.2-fpm`, `php8.2-mysql`, `php8.2-mbstring`, `php8.2-bcmath`, `php8.2-xml`, `php8.2-curl`, `php8.2-gd`, `php8.2-zip`, `php8.2-intl`
- **Database**: MySQL 8.0+ (atau MariaDB 10.6+) dengan InnoDB Engine
- **Perangkat Keras Minimal**: 2 vCPU, 2 GB RAM, 25 GB SSD Storage
- **SSL**: Let's Encrypt Certbot (HTTPS Wajib untuk PWA/POS)

---

## 2. Konfigurasi Nginx Virtual Host

Simpan konfigurasi berikut pada `/etc/nginx/sites-available/stokify.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name kios.stokify.id; # Ganti dengan domain toko Anda
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name kios.stokify.id;

    root /var/www/stokify/public;
    index index.php index.html;

    # SSL Certificates (Let's Encrypt Certbot)
    ssl_certificate /etc/letsencrypt/live/kios.stokify.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/kios.stokify.id/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Header Keamanan Tingkat Web Server
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    charset utf-8;
    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /favicon.svg { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location ~* \.(css|js|jpg|jpeg|png|gif|ico|svg|woff2)$ {
        expires 1y;
        add_header Cache-Control "public, no-transform";
    }
}
```

Aktifkan konfigurasi dan muat ulang Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/stokify.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 3. Hak Akses File dan Direktori Linux

Berikan kepemilikan direktori ke pengguna web server (`www-data`):

```bash
cd /var/www/stokify

# Kepemilikan user dan group
sudo chown -R www-data:www-data /var/www/stokify

# Hak akses folder dan file standar
sudo find /var/www/stokify -type f -exec chmod 644 {} \;
sudo find /var/www/stokify -type d -exec chmod 755 {} \;

# Hak akses tulis khusus direktori writable
sudo chmod -R 775 /var/www/stokify/storage
sudo chmod -R 775 /var/www/stokify/bootstrap/cache
```

---

## 4. Urutan Perintah Deployment Produksi

Saat melakukan deploy awal atau pembaruan kode versi baru:

```bash
cd /var/www/stokify

# 1. Aktifkan Maintenance Mode (opsional untuk update besar)
php artisan down --render="errors.503" --secret="rahasia-admin-2026"

# 2. Tarik kode versi terbaru dari git
git pull origin main

# 3. Pasang dependensi PHP mode produksi (tanpa dev tools)
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Bangun aset frontend produksi
npm ci
npm run build

# 5. Jalankan migrasi database
php artisan migrate --force

# 6. Buat tautan storage jika belum ada
php artisan storage:link

# 7. Bersihkan dan aktifkan cache konfigurasi produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 8. Matikan Maintenance Mode
php artisan up
```

---

## 5. Konfigurasi Penjadwalan Otomatis (Linux Cron)

Buka cron server dengan perintah `crontab -e -u www-data` dan tambahkan entri berikut:

```cron
# Laravel Scheduler Master Runner (Dijalankan setiap menit)
* * * * * cd /var/www/stokify && php artisan schedule:run >> /dev/null 2>&1
```

Di dalam `app/Console/Kernel.php`, jadwalkan perintah otomatis berikut:
```php
protected function schedule(Schedule $schedule): void
{
    // Backup database setiap malam pukul 02:00 WIT
    $schedule->command('stokify:backup-db')
        ->dailyAt('02:00')
        ->appendOutputTo(storage_path('logs/backup.log'));

    // Audit integritas data setiap malam pukul 03:00 WIT
    $schedule->command('stokify:cek-integritas')
        ->dailyAt('03:00')
        ->appendOutputTo(storage_path('logs/integritas.log'));
}
```

---

## 6. Prosedur Backup & Restore Database

### Menjalankan Backup Mandiri
```bash
# Menghasilkan berkas sql dump di storage/app/backup/stokify-YYYYMMDD-HHMM.sql
php artisan stokify:backup-db
```
*Catatan Retensi*: Perintah ini secara otomatis menghapus file backup lama dan hanya mempertahankan 14 file backup harian terakhir. Disarankan untuk menyalin file backup ke penyimpanan awan terpisah (Google Drive / S3 / R2).

### Memulihkan (*Restore*) Database
```bash
mysql -u root -p stokify < storage/app/backup/stokify-20261118-0200.sql
```

---

## 7. Catatan Pemasangan pada Shared Hosting (cPanel)

Jika aplikasi dipasang pada Shared Hosting cPanel tanpa akses SSH root:
1. Pindahkan isi folder `public/` ke folder `public_html/`.
2. Letakkan seluruh folder aplikasi lainnya satu tingkat di atas `public_html/` (misal: di `/home/user/stokify_app/`).
3. Sesuaikan file `public_html/index.php`:
   ```php
   require __DIR__.'/../stokify_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../stokify_app/bootstrap/app.php';
   ```
4. Setup cron job di cPanel: `php -q /home/user/stokify_app/artisan schedule:run`.

---

## 8. Checklist Pra Go-Live (Sebelum Dipakai Transaksi Nyata)

- [ ] Variabel `.env`: `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] Kunci aplikasi (`APP_KEY`) terisi dan unik.
- [ ] `APP_URL` menggunakan protokol HTTPS (`https://kios.stokify.id`).
- [ ] Kredensial akun demo bawaan telah diubah kata sandinya atau dibersihkan.
- [ ] Database backup terjadwal aktif di server.
- [ ] Header keamanan browser aktif (`curl -I https://kios.stokify.id` menunjukkan `X-Frame-Options` dll).
- [ ] Printer thermal kasir (58mm/80mm) telah diuji coba cetak struk dari peramban.
- [ ] Hasil audit awal: `php artisan stokify:cek-integritas` menghasilkan **0 Temuan (AMAN)**.
