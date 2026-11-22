# 02. Hak Akses dan Matriks Perizinan (RBAC)

Dokumen ini mendefinisikan arsitektur kontrol akses berbasis peran (*Role-Based Access Control* / RBAC) pada **Stokify**, mekanisme middleware keamanan, dan matriks hak akses seluruh rute aplikasi.

---

## 1. Peran Pengguna (Roles)

Stokify memiliki 2 peran pengguna utama di setiap kios tenant:

1. **Admin (Pemilik Usaha / Manajer Toko)**:
   - Pemegang akun utama kios.
   - Mengendalikan seluruh konfigurasi master data (kategori, barang, supplier, akun kasir, profil kios).
   - Mengelola pasokan barang masuk, penyesuaian stok, dan stok opname fisik.
   - Mengakses laporan keuangan, audit jejak mutasi, dan analisis performa barang.
   - Memiliki kewenangan menutup paksa shift kasir yang menggantung dan membatalkan nota kasir jika shift masih aktif.
   - Dapat ikut bertransaksi di halaman kasir POS (tercatat atas nama Admin sendiri).

2. **Kasir (Petugas Kasir Toko)**:
   - Pengguna operasional kasir harian.
   - Wajib membuka shift sebelum bertransaksi kasir.
   - Mengoperasikan layar penjualan kasir POS, mencari barang, dan mencetak struk.
   - Dapat membatalkan nota penjualan miliknya sendiri selama shift-nya masih berjalan.
   - Menutup shift mandiri dengan input hitung buta uang fisik laci kasir.
   - **DILARANG KERAS** melihat harga modal, laba kotor, margin keuntungan, laporan keuangan, maupun mengedit master barang (menghasilkan status HTTP 403 Forbidden).

---

## 2. Mekanisme Middleware Keamanan

| Middleware | Deskripsi & Perilaku Pengamanan |
|:---|:---|
| `auth` | Memastikan pengguna telah terotentikasi melalui session web. Mengarahkan pengguna tak terautentikasi ke `/login`. |
| `active` | Memeriksa kolom `is_aktif` pada tabel `users`. Jika akun dinonaktifkan oleh Admin, sesi langsung diputus dan diarahkan keluar dengan pesan error. |
| `role:admin` | Memeriksa apakah `$user->role === 'admin'`. Jika kasir mencoba mengakses, server melempar `403 Forbidden` (`AccessDeniedHttpException`). |
| `role:kasir` | Mengkhususkan akses rute untuk peran kasir. |
| `AuthenticateSession` | Memverifikasi konsistensi `remember_token` dan session hash. Digunakan saat Admin melakukan reset password atau penonaktifan kasir agar sesi kasir langsung putus seketika. |
| `throttle:*` | Membatasi laju permintaan per pengguna untuk mencegah brute-force, DoS, atau spam ekspor PDF. |

---

## 3. Matriks Perizinan Rute (Route Permission Matrix)

Tabel berikut merangkum izin akses untuk setiap kelompok rute dalam aplikasi:

| Kelompok Fitur | Rute & HTTP Method | Nama Route | Admin | Kasir | Keterangan / Respon Kasir |
|:---|:---|:---|:---:|:---:|:---|
| **Publik & Auth** | `GET /` | `landing` | ALLOW | ALLOW | Halaman muka (Landing page) |
| | `GET /login`, `POST /login` | `login` | ALLOW | ALLOW | Login fleksibel (email/username) |
| | `POST /logout` | `logout` | ALLOW | ALLOW | Mengakhiri sesi aktif |
| | `GET /daftar`, `POST /daftar` | `register` | ALLOW | DENY | Pendaftaran kios baru |
| **Kasir POS** | `GET /kasir` | `kasir.index` | ALLOW | ALLOW | Halaman POS (wajib shift berjalan) |
| | `GET /kasir/barang/cari` | `kasir.barang.cari` | ALLOW | ALLOW | JSON cari barang (tanpa harga modal) |
| | `POST /kasir/penjualan` | `kasir.penjualan.store` | ALLOW | ALLOW | Simpan transaksi penjualan |
| | `GET /kasir/penjualan/{id}/struk` | `kasir.penjualan.struk` | ALLOW | ALLOW | Cetak struk (hanya nota kios sendiri) |
| | `POST /kasir/penjualan/{id}/batal` | `kasir.penjualan.batal` | ALLOW | ALLOW | Void nota (selama shift berjalan) |
| | `GET /kasir/riwayat` | `kasir.riwayat` | ALLOW | ALLOW | Riwayat nota shift berjalan |
| **Shift Kasir** | `GET /kasir/shift/buka` | `kasir.shift.buka` | ALLOW | ALLOW | Formulir buka shift kasir |
| | `POST /kasir/shift/buka` | `kasir.shift.simpanBuka` | ALLOW | ALLOW | Simpan pembukaan shift baru |
| | `GET /kasir/shift/tutup` | `kasir.shift.tutup` | ALLOW | ALLOW | Formulir rekonsiliasi tutup shift |
| | `POST /kasir/shift/tutup` | `kasir.shift.simpanTutup` | ALLOW | ALLOW | Hitung buta uang fisik & tutup |
| | `GET /kasir/shift/rekap` | `kasir.shift.rekap` | ALLOW | ALLOW | Rekap shift kasir (tanpa laba/modal) |
| **Dashboard** | `GET /admin/dashboard` | `admin.dashboard` | ALLOW | 403 | Ringkasan omzet, laba kotor, grafik |
| **Master Barang** | `GET /admin/barang` | `admin.barang.index` | ALLOW | 403 | Daftar katalog barang |
| | `GET /admin/barang/create` | `admin.barang.create` | ALLOW | 403 | Form tambah master barang |
| | `POST /admin/barang` | `admin.barang.store` | ALLOW | 403 | Simpan master barang baru |
| | `GET /admin/barang/{id}/edit` | `admin.barang.edit` | ALLOW | 403 | Form ubah data master barang |
| | `PUT /admin/barang/{id}` | `admin.barang.update` | ALLOW | 403 | Simpan pembaruan barang |
| | `POST /admin/barang/{id}/toggle` | `admin.barang.toggle` | ALLOW | 403 | Aktifkan/nonaktifkan barang |
| **Kategori** | `GET /admin/kategori` | `admin.kategori.index` | ALLOW | 403 | Kelola kategori produk |
| | `POST /admin/kategori` | `admin.kategori.store` | ALLOW | 403 | Tambah kategori baru |
| | `PUT /admin/kategori/{id}` | `admin.kategori.update` | ALLOW | 403 | Ubah nama kategori |
| **Supplier** | `GET /admin/supplier` | `admin.supplier.index` | ALLOW | 403 | Daftar mitra supplier |
| | `POST /admin/supplier` | `admin.supplier.store` | ALLOW | 403 | Tambah supplier baru |
| | `PUT /admin/supplier/{id}` | `admin.supplier.update` | ALLOW | 403 | Perbarui kontak supplier |
| **Kelola Kasir** | `GET /admin/kasir` | `admin.kasir.index` | ALLOW | 403 | Daftar petugas kasir kios |
| | `POST /admin/kasir` | `admin.kasir.store` | ALLOW | 403 | Tambah akun kasir baru |
| | `PUT /admin/kasir/{id}` | `admin.kasir.update` | ALLOW | 403 | Ubah identitas kasir |
| | `POST /admin/kasir/{id}/toggle` | `admin.kasir.toggle` | ALLOW | 403 | Aktif/nonaktifkan akun kasir |
| | `POST /admin/kasir/{id}/reset-password` | `admin.kasir.resetPassword` | ALLOW | 403 | Reset kata sandi kasir |
| **Barang Masuk** | `GET /admin/barang-masuk` | `admin.barang-masuk.index` | ALLOW | 403 | Daftar riwayat barang masuk |
| | `GET /admin/barang-masuk/create` | `admin.barang-masuk.create` | ALLOW | 403 | Form penerimaan barang |
| | `POST /admin/barang-masuk` | `admin.barang-masuk.store` | ALLOW | 403 | Simpan restok & hitung HPP |
| | `GET /admin/barang-masuk/{id}` | `admin.barang-masuk.show` | ALLOW | 403 | Rincian faktur barang masuk |
| **Penyesuaian** | `GET /admin/penyesuaian-stok` | `admin.penyesuaian-stok.index` | ALLOW | 403 | Riwayat penyesuaian stok |
| | `POST /admin/penyesuaian-stok/manual`| `admin.penyesuaian-stok.store-manual` | ALLOW | 403 | Catat barang rusak / hilang |
| | `POST /admin/penyesuaian-stok/opname`| `admin.penyesuaian-stok.store-opname` | ALLOW | 403 | Eksekusi hasil stok opname fisik |
| **Audit Shift** | `GET /admin/shift` | `admin.shift.index` | ALLOW | 403 | Rekap seluruh shift kasir |
| | `GET /admin/shift/{id}` | `admin.shift.show` | ALLOW | 403 | Detail laci & selisih shift |
| | `POST /admin/shift/{id}/tutup-paksa`| `admin.shift.tutup_paksa` | ALLOW | 403 | Tutup paksa shift menggantung |
| **Laporan** | `GET /admin/laporan` | `admin.laporan.index` | ALLOW | 403 | Menu utama laporan |
| | `GET /admin/laporan/keuangan` | `admin.laporan.keuangan` | ALLOW | 403 | Layar ringkasan keuangan |
| | `GET /admin/laporan/keuangan/pdf`| `admin.laporan.keuangan.pdf` | ALLOW | 403 | Unduh PDF Laporan Keuangan |
| | `GET /admin/laporan/audit` | `admin.laporan.audit` | ALLOW | 403 | Layar audit transaksi & shift |
| | `GET /admin/laporan/audit/pdf` | `admin.laporan.audit.pdf` | ALLOW | 403 | Unduh PDF Laporan Audit |
| | `GET /admin/laporan/stok` | `admin.laporan.stok` | ALLOW | 403 | Layar status persediaan stok |
| | `GET /admin/laporan/stok/pdf` | `admin.laporan.stok.pdf` | ALLOW | 403 | Unduh PDF Laporan Stok |
| **Analisis** | `GET /admin/analisis` | `admin.analisis.index` | ALLOW | 403 | Layar Pareto fast/slow moving |
| | `GET /admin/analisis/rekomendasi`| `admin.analisis.rekomendasi` | ALLOW | 403 | Rekomendasi restok (ROP) |
| | `GET /admin/analisis/pdf` | `admin.analisis.pdf` | ALLOW | 403 | Unduh PDF daftar belanja restok |
| | `PUT /admin/analisis/pengaturan` | `admin.analisis.pengaturan.update` | ALLOW | 403 | Atur lead time & pengaman |
| **Pengaturan** | `GET /admin/kios` | `admin.kios.index` | ALLOW | 403 | Tampilan profil identitas kios |
| | `PUT /admin/kios` | `admin.kios.update` | ALLOW | 403 | Simpan pembaruan nama/alamat |
| | `GET /admin/log-aktivitas` | `admin.log.index` | ALLOW | 403 | Penampil jejak audit aktivitas |

---

## 4. Perlindungan Akses Antar-Tenant (Cross-Tenant Isolation)

Semua entitas data tenant (Barang, Kategori, Supplier, Transaksi, Shift, Mutasi, Log) dilindungi oleh trait `BelongsToKios`.
Ketika Admin Kios A mencoba mengakses ID milik Kios B (misal: `/admin/barang/999/edit` di mana barang 999 milik Kios B):
- Scope `KiosScope` secara otomatis menyisipkan klausul SQL `WHERE kios_id = ?`.
- Query Eloquent `findOrFail` gagal menemukan baris tersebut dan melempar `ModelNotFoundException`.
- Server mengembalikan respon standar **404 Not Found**, sehingga Admin Kios A bahkan tidak mengetahui apakah entitas ID 999 ada di database atau tidak.
