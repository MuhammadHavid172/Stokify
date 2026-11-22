# Dokumentasi Resmi Sistem Stokify

Selamat datang di repositori dokumentasi resmi **Stokify** — Platform Sistem Informasi Manajemen Kios dan Point of Sale (POS) Multi-Tenant Modern.

Stokify dirancang khusus untuk memenuhi kebutuhan operasional toko kelontong, kios sembako, dan ritel mandiri skala mikro-kecil dengan penekanan pada kecepatan kasir, presisi mutasi persediaan, pemisahan shift yang akuntabel, dan isolasi data kios 100% aman.

---

## 1. Daftar Dokumen Arsitektur & Panduan

Seluruh dokumentasi teknis, model bisnis, skema database, panduan pengguna, hingga pengujian tersusun secara terstruktur dalam dokumen-dokumen berikut:

| No | Dokumen | Deskripsi Singkat |
|:---|:---|:---|
| 01 | [01-batasan-dan-model-bisnis.md](file:///d:/Stokify/docs/01-batasan-dan-model-bisnis.md) | Model bisnis kios sembako, 5 batasan sistem transparan, dan peta fitur fase lanjutan |
| 02 | [02-hak-akses.md](file:///d:/Stokify/docs/02-hak-akses.md) | Matriks perizinan Admin vs Kasir, middleware otentikasi, dan kontrol sesi |
| 03 | [03-erd.md](file:///d:/Stokify/docs/03-erd.md) | Diagram relasi entitas (Mermaid ERD) 14 tabel database dan kamus data detail |
| 04 | [04-dfd.md](file:///d:/Stokify/docs/04-dfd.md) | Diagram Alir Data (DFD Level 0 Konteks & DFD Level 1) beserta 9 proses bisnis inti |
| 05 | [05-use-case.md](file:///d:/Stokify/docs/05-use-case.md) | Diagram use case dan spesifikasi naratif 9 skenario use case utama |
| 06 | [06-flowchart.md](file:///d:/Stokify/docs/06-flowchart.md) | Diagram alur proses (POS, void, tutup shift, moving average, stok opname, ROP) |
| 07 | [07-rumus-dan-algoritma.md](file:///d:/Stokify/docs/07-rumus-dan-algoritma.md) | Rumus matematis lengkap: Moving Average HPP, Laba Kotor, Pareto ABC, dan ROP |
| 08 | [08-keamanan-dan-isolasi-data.md](file:///d:/Stokify/docs/08-keamanan-dan-isolasi-data.md) | Arsitektur isolasi multi-tenant `BelongsToKios`, idempotensi, audit trail, & hardening |
| 09 | [09-panduan-pengguna.md](file:///d:/Stokify/docs/09-panduan-pengguna.md) | Buku panduan operasional harian langkah-demi-langkah bagi Pemilik Kios dan Petugas Kasir |
| 10 | [10-deploy.md](file:///d:/Stokify/docs/10-deploy.md) | Panduan instalasi VPS Production (Nginx, PHP-FPM, MySQL), Cron, dan checklist Go-Live |
| 11 | [11-skenario-uji.md](file:///d:/Stokify/docs/11-skenario-uji.md) | Matriks 42 skenario User Acceptance Testing (UAT) manual terperinci |
| 12 | [12-ringkasan-test.md](file:///d:/Stokify/docs/12-ringkasan-test.md) | Laporan eksekusi pengujian otomatis PHPUnit (189 tests, 1051 assertions, 100% pass) |

---

## 2. Fitur Inti Aplikasi

1. **Transaksi Penjualan (POS Kasir Cepat)**: Pencarian barang instan via ketik nama/barcode, kalkulasi kembalian otomatis, pencetakan struk thermal 58mm/80mm, serta penguncian submit ganda (idempotency token).
2. **Manajemen Shift Kasir & Rekonsiliasi Kas**: Wajib buka shift sebelum transaksi, hitung buta uang fisik laci kasir saat tutup shift, dan deteksi selisih kas (pas, lebih, kurang) dengan catatan rekonsiliasi.
3. **Pembatalan Transaksi (Void)**: Hak pembatalan nota penjualan selama shift masih aktif. Mengembalikan saldo stok barang dan membentuk mutasi retur penjualan secara otomatis.
4. **Pengadaan Barang Masuk (Restok)**: Pencatatan pasokan masuk dari supplier dengan pembaruan otomatis Harga Pokok Penjualan (HPP) berbasis metode **Moving Average**.
5. **Penyesuaian Stok & Stok Opname**: Penyesuaian barang rusak/hilang serta fitur stok opname fisik periodik dengan kalkulasi nilai kerugian stok.
6. **Analisis Performa Barang (Pareto ABC) & Rekomendasi Restok**: Klasifikasi produk Fast, Medium, dan Slow Moving serta perhitungan Titik Pesan Ulang (*Reorder Point* / ROP) berbasis lead time kios.
7. **Laporan Resmi Siap Cetak (PDF)**: Laporan Ringkasan Keuangan (Omzet, HPP, Laba Kotor), Audit Transaksi & Shift, serta Laporan Persediaan Stok barang.
8. **Isolasi Multi-Tenant Mutlak**: Pemisahan data per kios secara otomatis menggunakan Laravel Global Scope `BelongsToKios`.

---

## 3. Spesifikasi Teknologi (Tech Stack)

- **Backend Framework**: Laravel 10 / 11 (PHP 8.2+)
- **Database**: MySQL 8.0 / MariaDB 10.4+ (InnoDB Engine)
- **Frontend / Styling**: Tailwind CSS 3.4+ (Palet Khusus Forest & Deep Pine)
- **Frontend Interactivity**: Alpine.js 3.x
- **Build Tool**: Vite 5.x
- **PDF Engine**: Barryvdh Laravel DomPDF
- **Testing Framework**: PHPUnit 10.x & Laravel Testing Suite

---

## 4. Panduan Instalasi Lokal

### Prasyarat Lingkungan
- PHP >= 8.2 dengan ekstensi `pdo_mysql`, `mbstring`, `bcmath`, `gd`, `zip`, `xml`
- Composer 2.x
- Node.js >= 18.x dan NPM
- Database MySQL aktif

### Langkah Setup

```bash
# 1. Masuk ke direktori proyek
cd d:/Stokify

# 2. Pasang dependensi PHP
composer install

# 3. Pasang dependensi Javascript & build aset
npm install
npm run build

# 4. Salin file konfigurasi environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Buat database MySQL bernama `stokify` (sesuaikan username/password di .env)
# 7. Jalankan migrasi dan seeder data demo
php artisan migrate:fresh --seed

# 8. Buat symbolic link storage
php artisan storage:link

# 9. Jalankan web server lokal
php artisan serve
```

Aplikasi siap diakses pada browser di URL: `http://localhost:8000`

---

## 5. Akun Pengguna Demo

Tersedia 3 kios contoh yang terisi data riil dari seeder (`Database\Seeders\DemoSeeder`):

### Kios 1: Kios Berkah Sentosa (Usaha Sembako Lengkap)
- **Admin**: `admin_berkah` / `password123` (email: `admin@berkah.test`)
- **Kasir 1**: `kasir_siti` / `password123`
- **Kasir 2**: `kasir_budi` / `password123`

### Kios 2: Kios Sumber Rejeki (Usaha Bahan Pokok & Bumbu)
- **Admin**: `admin_rejeki` / `password123` (email: `admin@rejeki.test`)
- **Kasir 1**: `kasir_dewi` / `password123`

### Kios 3: Kios Cahaya Mandiri (Usaha Ritel Mini)
- **Admin**: `admin_mandiri` / `password123` (email: `admin@mandiri.test`)
- **Kasir 1**: `kasir_agus` / `password123`

---

## 6. Perintah Artisan Khusus Stokify

| Perintah | Deskripsi |
|:---|:---|
| `php artisan stokify:cek-integritas` | Menjalankan 5 audit integritas stok, nota, shift menggantung, dan validasi foreign key tenant. |
| `php artisan stokify:backup-db` | Membuat backup dump SQL database lokal ke `storage/app/backup/` dengan retensi 14 file. |
| `php artisan stokify:reset-demo` | Mereset database demo (`migrate:fresh --seed`). Otomatis menolak dijalankan di production. |

---

## 7. Verifikasi Pengujian Otomatis

Untuk menjalankan seluruh rangkaian automated tests (189 tests, 1051 assertions):

```bash
php artisan test
```

Untuk menjalankan test per modul:
```bash
# Test final Tahap 11 (Pengamanan, Integritas, Audit Tenant)
php artisan test --filter=TahapSebelasTest

# Test transaksi penjualan & POS
php artisan test --filter=TahapTujuhTest

# Test shift kasir & void
php artisan test --filter=TahapDelapanTest

# Test laporan & ekspor PDF
php artisan test --filter=TahapSembilanTest

# Test analisis Pareto & rekomendasi restok
php artisan test --filter=TahapSepuluhTest
```
