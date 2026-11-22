# 09. Buku Panduan Pengguna (User Manual)

Buku panduan ini memandu pemilik usaha (Admin) dan petugas kasir dalam mengoperasikan sistem informasi manajemen kios **Stokify** untuk aktivitas harian toko.

---

## BAGIAN A: PANDUAN PEMILIK KIOS (ADMIN)

### 1. Pendaftaran Kios & Login Pertama Kali
1. Buka peramban (*browser*) dan akses URL Stokify.
2. Di halaman beranda, klik tombol **"Daftar Kios Gratis"**.
3. Isi formulir pendaftaran:
   - **Nama Kios**: Nama usaha toko fisik Anda (contoh: *Kios Berkah Sentosa*).
   - **Nama Lengkap Pemilik**: Nama Anda sebagai pemilik toko.
   - **Email**: Alamat email aktif untuk pemulihan akun.
   - **Kata Sandi**: Minimal 8 karakter kombinasi.
4. Klik **"Daftarkan Kios Sekarang"**. Sistem akan membuat database kios Anda dan langsung mengarahkan Anda ke Dashboard Utama.

> `[SCREENSHOT: dashboard-admin]`
*Tampilan Ringkasan Harian Pemilik Usaha (Omzet, HPP, Transaksi, Grafik, dan Peringatan Stok).*

---

### 2. Pengaturan Profil Kios
1. Masuk ke menu sidebar kiri: **Pengaturan** -> **Profil Kios**.
2. Perbarui data identitas resmi toko Anda:
   - **Nama Kios**: Nama toko yang tercantum di kepala struk dan laporan.
   - **Alamat Toko**: Alamat fisik toko (jalan, nomor, kota).
   - **Nomor Telepon**: Nomor HP/WhatsApp toko untuk kontak pelanggan.
3. Klik tombol **"Simpan Perubahan Profil"**.
4. Data ini akan otomatis dicetak pada kepala struk kasir dan seluruh dokumen laporan PDF.

> `[SCREENSHOT: profil-kios]`
*Halaman formulir pengaturan profil kios dan informasi statistik toko.*

---

### 3. Mengelola Akun Petugas Kasir
1. Buka menu sidebar: **Pengaturan** -> **Kelola Kasir**.
2. Klik tombol **"Tambah Kasir Baru"**.
3. Masukkan nama lengkap kasir dan username unik (misal: `siti_kasir`).
4. Berikan kata sandi awal (minimal 8 karakter).
5. Klik **"Simpan Akun Kasir"**.
6. Petugas kasir kini dapat login menggunakan username dan kata sandi tersebut.
7. Jika kasir cuti atau berhenti bekerja, Anda dapat menekan tombol toggle **"Nonaktifkan"** atau tombol **"Reset Password"** sewaktu-waktu.

> `[SCREENSHOT: kelola-kasir]`
*Daftar petugas kasir, status aktif, dan tombol manajemen akun.*

---

### 4. Menyiapkan Kategori dan Master Barang
1. Buat kategori terlebih dahulu di menu **Master Data** -> **Kategori Barang** (misal: *Sembako*, *Bumbu Dapur*, *Minuman*).
2. Masuk ke menu **Master Data** -> **Data Barang** -> klik **"Tambah Barang Baru"**.
3. Masukkan data produk:
   - **Kode / Barcode**: Scan barcode kemasan produk atau buat kode manual (misal: `BRG-001`).
   - **Nama Barang**: Nama lengkap produk (contoh: *Beras Ramos 5 kg*).
   - **Kategori**: Pilih kategori yang sesuai.
   - **Satuan**: Pilih satuan kemasan (*sak*, *kg*, *liter*, *pcs*, *dus*).
   - **Harga Modal (HPP Awal)**: Harga beli awal dari agen (misal: Rp 60.000).
   - **Harga Jual**: Harga jual eceran ke pembeli (misal: Rp 68.000).
   - **Stok Awal**: Jumlah fisik barang yang saat ini ada di toko (misal: 25 sak).
   - **Batas Stok Minimum**: Ambang batas peringatan stok menipis (misal: 5 sak).
4. Klik **"Simpan Barang"**. Stok awal otomatis tercatat ke buku mutasi.

> `[SCREENSHOT: master-barang]`
*Tabel katalog master barang dengan indikator stok, status aktif, dan tombol aksi.*

---

### 5. Mencatat Pasokan Barang Masuk (Restok)
1. Saat pesanan dari supplier tiba di toko, buka menu **Inventori & Stok** -> **Barang Masuk**.
2. Klik tombol **"Catat Barang Masuk"**.
3. Pilih nama Supplier dan masukkan nomor faktur serta tanggal penerimaan.
4. Cari barang yang diterima, masukkan kuantitas barang masuk dan harga beli per unit saat ini.
5. Klik **"Simpan Barang Masuk"**.
6. Sistem secara otomatis:
   - Menambahkan kuantitas fisik ke saldo stok toko.
   - Menghitung ulang Harga Pokok Penjualan (HPP) menggunakan metode *Moving Average*.
   - Mencatat kartu stok pada buku besar mutasi.

> `[SCREENSHOT: form-barang-masuk]`
*Formulir pencatatan faktur barang masuk dan kalkulasi otomatis HPP Moving Average.*

---

### 6. Melakukan Penyesuaian Stok & Stok Opname Fisik
1. Masuk ke menu **Inventori & Stok** -> **Penyesuaian Stok**.
2. Untuk barang rusak/pecah:
   - Klik tab **"Penyesuaian Manual"**.
   - Pilih barang, jenis kerusakan (misal: kemasan bocor), jumlah yang berkurang, dan alasan.
   - Klik **"Simpan Penyesuaian"**.
3. Untuk audit mingguan/bulanan (Stok Opname):
   - Klik tab **"Stok Opname"**.
   - Input hasil hitung fisik di rak toko pada kolom stok fisik.
   - Sistem akan menyaring barang yang berselisih dan menampilkan total kerugian.
   - Masukkan alasan selisih dan klik **"Eksekusi Stok Opname"**. Saldo sistem akan dikoreksi mengikuti kondisi fisik riil.

> `[SCREENSHOT: stok-opname]`
*Tampilan tabel audit stok opname dengan selisih stok sistem vs fisik.*

---

### 7. Melihat Analisis Performa Barang & Rekomendasi Restok
1. Masuk ke menu **Analisis & Laporan** -> **Performa Barang**.
2. Pilih periode analisis (default 30 hari terakhir).
3. Anda akan melihat:
   - **Produk Fast Moving (Pareto A)**: Barang penyumbang 80% omzet kios.
   - **Peringatan Perlu Restok**: Barang-barang yang stoknya telah menyentuh atau berada di bawah Titik Pesan Ulang (*Reorder Point* / ROP).
   - **Saran Kuantitas Belanja**: Estimasi kuantitas yang perlu dibeli ke supplier agar stok mencukupi target belanja mingguan Anda.
4. Klik tombol **"Unduh Rekomendasi PDF"** untuk mencetak daftar belanja belanjaan Anda.

> `[SCREENSHOT: analisis-rekomendasi]`
*Layar rekomendasi restok ROP lengkap dengan status restok dan tombol cetak PDF.*

---

### 8. Menarik Laporan Keuangan & Audit PDF
1. Buka menu **Analisis & Laporan** -> **Laporan Keuangan**.
2. Tentukan rentang tanggal laporan yang Anda inginkan (misal: Bulan Ini).
3. Anda akan melihat angka ringkasan: **Total Omzet**, **Total HPP**, dan **Laba Kotor**.
4. Klik tombol **"Unduh Laporan PDF"** untuk mencetak dokumen resmi bertandatangan toko.

> `[SCREENSHOT: laporan-keuangan-pdf]`
*Contoh dokumen Laporan Keuangan resmi format PDF dengan kop kios dan catatan akuntansi.*

---

## BAGIAN B: PANDUAN PETUGAS KASIR

### 1. Membuka Shift Kasir
1. Buka peramban dan login menggunakan akun kasir Anda.
2. Karena kasir wajib memiliki shift sebelum bertransaksi, sistem akan otomatis menampilkan layar **Buka Shift Kasir**.
3. Hitung uang kembalian (kas kecil) yang ada di laci meja kasir Anda.
4. Masukkan nominal uang tersebut pada kolom **Modal Awal** (misal: Rp 100.000).
5. Klik tombol **"Buka Shift Sekarang"**. Anda akan langsung dialihkan ke Layar Penjualan POS.

> `[SCREENSHOT: kasir-buka-shift]`
*Formulir pembukaan shift kasir dengan input modal kas kecil.*

---

### 2. Melayani Transaksi Penjualan (POS)
1. Pada layar kasir, cari barang dengan dua cara:
   - **Ketik nama atau kode barang** pada kotak pencarian (pencarian instan muncul otomatis).
   - **Scan barcode** kemasan produk menggunakan barcode scanner USB/Bluetooth.
2. Klik barang atau tekan Enter untuk memasukkan ke keranjang belanja.
3. Atur kuantitas barang belanjaan pembeli (gunakan tombol `+` / `-` atau ketik angka langsung).
4. Periksa total nilai belanjaan.
5. Masukkan nominal uang tunai yang diserahkan oleh pembeli pada kolom **Uang Bayar** (tersedia tombol cepat pecahan uang pas: Rp 10.000, Rp 20.000, Rp 50.000, Rp 100.000).
6. Nominal uang kembalian akan otomatis terhitung dan tampil tebal di layar.
7. Tekan tombol **"Bayar & Cetak Struk"** (atau tekan tombol pintas keyboard).
8. Nota tersimpan, stok barang otomatis terpotong, laci kasir terbuka, dan pop-up cetak struk muncul di layar.

> `[SCREENSHOT: kasir-pos]`
*Antarmuka layar kasir kilat dengan panel keranjang belanja, pencarian barang, dan hitungan kembalian.*

---

### 3. Mencetak Ulang Struk
1. Jika kertas printer macet atau pembeli meminta cetak struk kembali:
2. Buka menu **Riwayat Transaksi** di sudut atas layar kasir.
3. Temukan transaksi yang dimaksud berdasarkan nomor nota atau jam transaksi.
4. Klik tombol ikon printer **"Cetak Struk"**.
5. Struk format thermal 58mm/80mm akan langsung terbuka untuk dicetak.

---

### 4. Membatalkan Nota Penjualan (Void)
1. Pembatalan nota hanya dapat dilakukan selama shift kasir Anda **masih berjalan**.
2. Buka menu **Riwayat Transaksi**.
3. Temukan nomor nota yang hendak dibatalkan, lalu klik tombol **"Void"** (ikon silang merah).
4. Masukkan alasan pembatalan minimal 5 karakter (contoh: *"Pembeli lupa bawa uang dompet"*).
5. Klik **"Konfirmasi Pembatalan"**.
6. Sistem membatalkan nota tersebut, mengeluarkan nominalnya dari omzet shift Anda, dan mengembalikan stok barang ke rak secara instan.

> `[SCREENSHOT: kasir-void-modal]`
*Modal konfirmasi pembatalan nota kasir dengan input alasan wajib.*

---

### 5. Menutup Shift Kasir dan Rekonsiliasi Kas Laci
1. Saat jam kerja Anda selesai, klik menu **Tutup Shift** di navigasi kasir.
2. Lakukan **Hitung Buta (*Blind Count*)**:
   - Buka laci kasir dan hitung seluruh uang kertas serta receh fisik yang ada di laci.
   - Jangan menebak-nebak atau mencoba mencocokkan; hitung murni apa yang ada di laci kasir.
3. Masukkan nominal hasil hitung fisik pada kolom **Uang Fisik di Laci**.
4. Jika nominal fisik berbeda dengan hitungan sistem toko, kolom **Catatan Selisih** akan muncul.
5. Tuliskan penjelasan mengapa terjadi selisih (misal: *"Kelebihan Rp 2.000 karena ada pembeli yang tidak mengambil kembalian receh"*).
6. Klik tombol **"Tutup Shift Kasir"**.
7. Sistem mengunci seluruh transaksi Anda dan menampilkan halaman Rekap Hasil Shift. Berikan uang fisik laci beserta rekap shift ini kepada pemilik toko / kasir shift berikutnya.

> `[SCREENSHOT: kasir-tutup-shift]`
*Layar rekonsiliasi tutup shift kasir dengan input hitung buta uang laci.*
