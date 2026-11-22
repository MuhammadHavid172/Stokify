# 08. Keamanan, Integritas, dan Isolasi Data Multi-Tenant

Dokumen ini menjelaskan arsitektur pengamanan berlapis (*defense-in-depth*) yang diterapkan pada **Stokify**, mencakup isolasi tenant, snapshot harga, konkurensi database, jejak audit, hingga pembatasan laju (*rate limiting*).

---

## 1. Arsitektur Isolasi Multi-Tenant (*BelongsToKios*)

Stokify menggunakan model arsitektur **Shared Database, Shared Schema** dengan pemisahan logis berbasis kolom penyewa (`kios_id`). Untuk memastikan tidak ada data yang bocor antar-kios, sistem menerapkan abstraksi Laravel Eloquent Global Scope:

```
[ HTTP Request Masuk ]
         │
         ▼
[ Auth Middleware -> Identifikasi Kios Pengguna ]
         │
         ▼
[ Eloquent Query: KiosScope Menambahkan "WHERE kios_id = ?" ]
         │
         ▼
[ Database MySQL: Eksekusi Query Hanya Pada Data Kios Sendiri ]
```

### Komponen Pengamanan Tenant:
1. **Trait `BelongsToKios`**:
   - Dipasang pada seluruh model data operasional (`Barang`, `Kategori`, `Supplier`, `Penjualan`, `DetailPenjualan`, `ShiftKasir`, `BarangMasuk`, `DetailBarangMasuk`, `PenyesuaianStok`, `DetailPenyesuaianStok`, `MutasiStok`, `LogAktivitas`).
   - Mendaftarkan `KiosScope` secara global ke model terkait.
   - Mengunci pengisian `kios_id` saat event `creating`: jika ada pengguna yang sedang login, `kios_id` otomatis diisi dari `Auth::user()->kios_id` dan **kebal terhadap manipulasi input HTTP / mass assignment**.
2. **KiosScope**:
   - Menyisipkan klausul SQL `WHERE {table}.kios_id = {current_user_kios_id}` pada seluruh query `SELECT`, `UPDATE`, dan `DELETE`.
   - Menggagalkan query pencarian lintas kios (menghasilkan status HTTP 404 Not Found secara aman).

---

## 2. Integritas Keuangan Melalui Snapshot Harga

Salah satu kerentanan umum pada aplikasi POS sederhana adalah menghitung laba masa lalu menggunakan harga barang saat ini. Jika pemilik menaikkan harga jual dari Rp 10.000 menjadi Rp 12.000, maka laporan laba bulan lalu ikut melonjak secara fiktif.

Stokify mencegah anomali ini melalui **Snapshot Data Transaksi**:
- Pada tabel `penjualan`: terdapat kolom `total` dan `total_modal`.
- Pada tabel `detail_penjualan`: terdapat kolom `harga_jual`, `harga_modal`, `subtotal`, dan `subtotal_modal`.
- Pada saat kasir menekan tombol selesai bayar, sistem mengambil nilai `harga_jual` dan `harga_modal` (HPP) master barang saat detik transaksi itu terjadi dan membekukannya di baris `detail_penjualan`.
- Laporan Keuangan dan Analisis Performa membaca langsung dari baris snapshot transaksi, sehingga laporan pembukuan bersifat permanen dan tidak dapat berubah akibat mutasi harga barang di masa depan.

---

## 3. Idempotensi Penjualan & Pencegahan Submit Ganda

Pada jaringan kasir yang lambat, kasir dapat menekan tombol bayar berulang kali, atau browser melakukan *auto-retry*, yang berpotensi memotong stok berkali-kali untuk satu nota belanja.

Stokify mengatasi hal ini melalui **Idempotency Token**:
1. Saat kasir membuka layar kasir atau merender keranjang belanja, JavaScript menghasilkan UUID / token unik acak.
2. Token ini dikirim bersama muatan JSON transaksi ke endpoint `POST /kasir/penjualan`.
3. Pada tabel `penjualan`, kolom `token` memiliki indeks unik komposit: `UNIQUE(kios_id, token)`.
4. Jika token yang sama dikirimkan kembali (misal akibat double click), `PenjualanService` mendeteksi keberadaan token tersebut dan mengembalikan data nota yang sudah berhasil sebelumnya tanpa memotong stok maupun mencatat mutasi baru.

---

## 4. Kontrol Konkurensi Database (*Pessimistic Locking*)

Jika dua kasir menjual barang yang sama secara bersamaan ketika stok hanya tersisa 1 unit, kondisi balapan (*race condition*) dapat menyebabkan stok menjadi negatif (-1).

Stokify menerapkan **Pessimistic Locking**:
```php
$barang = Barang::where('id', $item['barang_id'])
    ->lockForUpdate()
    ->firstOrFail();

if ($barang->stok < $item['jumlah']) {
    throw new InvalidArgumentException("Stok {$barang->nama} tidak mencukupi.");
}
```
Query `SELECT ... FOR UPDATE` mengunci baris barang pada tingkat engine InnoDB MySQL hingga transaksi database selesai (*commit* atau *rollback*). Kasir kedua akan menunggu antrean baris selesai sebelum dapat membaca saldo stok terkini.

---

## 5. Jejak Rekam Audit (*Audit Trail*) & Sensor Data Sensitif

Tabel `log_aktivitas` mencatat setiap aksi krusial pengguna (perubahan barang, penyesuaian stok, restok barang masuk, buka/tutup shift, void nota, hingga perubahan profil kios).

### Mekanisme Sensor Otomatis (*Masking*)
Saat menyimpan snapshot data (`data_lama` dan `data_baru`), `LogAktivitasController` dan `LogAktivitas::catat()` secara otomatis menyensor kata sandi, token keamanan, dan kunci rahasia menjadi `********`.
- Field yang disensor: `password`, `kata_sandi`, `password_confirmation`, `token`, `remember_token`, `pin`, `secret`.
- Log aktivitas bersifat **hanya baca (*read-only*)**; tidak disediakan route maupun controller untuk mengubah (`PUT`) atau menghapus (`DELETE`) baris log.

---

## 6. Pembatasan Laju Permintaan (*Rate Limiting*)

Dikonfigurasi melalui `App\Providers\RouteServiceProvider`:

| Limit Name | Batas Maksimal | Lingkup Identitas | Respon Saat Melebihi Batas |
|:---|:---|:---|:---|
| `register` | 5 registrasi per jam | IP Address | 429 Too Many Requests |
| `kasir.barang.cari` | 120 pencarian per menit | User ID (atau IP) | JSON 429 `{success: false, message: ...}` |
| `kasir.penjualan.store` | 60 transaksi per menit | User ID (atau IP) | JSON 429 `{success: false, message: ...}` |
| `unduh.pdf` | 20 ekspor PDF per menit | User ID (atau IP) | HTML 429 Halaman Error Terdedikasi |

---

## 7. Middleware Header Keamanan (*Security Headers*)

Middleware `App\Http\Middleware\SecurityHeaders` secara otomatis menyuntikkan header proteksi browser pada setiap respon aplikasi:
- `X-Frame-Options: SAMEORIGIN` (mencegah serangan Clickjacking).
- `X-Content-Type-Options: nosniff` (mencegah MIME-type sniffing).
- `Referrer-Policy: strict-origin-when-cross-origin` (melindungi privasi URL asal).
- `Permissions-Policy: camera=(), microphone=(), geolocation=()` (membatasi akses hardware).
- `Strict-Transport-Security` (disuntikkan otomatis jika koneksi menggunakan HTTPS pada lingkungan produksi).

---

## 8. Pengerasan Sesi dan Kebijakan Kata Sandi

1. **Durasi Sesi Kasir 8 Jam**: `config/session.php` disetel `lifetime = 480` menit (8 jam penuh), sesuai dengan durasi shift kerja harian kasir agar sesi tidak terputus di tengah antrean pembeli.
2. **Standar Kata Sandi**: `AppServiceProvider` menerapkan aturan `Password::defaults(fn() => Password::min(8))`.
3. **Pencegahan Lazy Loading**: Pada lingkungan non-produksi (`local` dan `testing`), `Model::preventLazyLoading(! app()->isProduction())` aktif untuk mendeteksi potensi masalah performa *N+1 Query* sejak tahap pengembangan.
