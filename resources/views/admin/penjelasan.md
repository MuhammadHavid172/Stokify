# PANDUAN LENGKAP & ALUR PENGGUNAAN APLIKASI STOKIFY
Sistem Informasi Manajemen Kios & Kasir POS Multi-Tenant

---

## DAFTAR ISI
1. [Pendahuluan: Apa itu Stokify?](#1-pendahuluan-apa-itu-stokify)
2. [Alur Dari Awal Sampai Beroperasi (Apa yang Diinput Duluan?)](#2-alur-dari-awal-sampai-beroperasi-apa-yang-diinput-duluan)
   - [Tahap 1: Registrasi Kios Baru](#tahap-1-registrasi-kios-baru)
   - [Tahap 2: Login Pemilik (Admin)](#tahap-2-login-pemilik-admin)
   - [Tahap 3: Mengisi Pengaturan Profil Kios](#tahap-3-mengisi-pengaturan-profil-kios)
   - [Tahap 4: Menyiapkan Kategori Barang](#tahap-4-menyiapkan-kategori-barang)
   - [Tahap 5: Menginput Data Barang & Stok Awal](#tahap-5-menginput-data-barang--stok-awal)
   - [Tahap 6: Mendaftarkan Supplier (Pemasok)](#tahap-6-mendaftarkan-supplier-pemasok)
   - [Tahap 7: Membuat Akun Petugas Kasir](#tahap-7-membuat-akun-petugas-kasir)
3. [Alur Operasional Harian Kasir (POS)](#3-alur-operasional-harian-kasir-pos)
   - [Alur 1: Buka Shift & Masukkan Modal Awal](#alur-1-buka-shift--masukkan-modal-awal)
   - [Alur 2: Transaksi Penjualan & Cetak Struk](#alur-2-transaksi-penjualan--cetak-struk)
   - [Alur 3: Tutup Shift & Rekonsiliasi Kas Laci](#alur-3-tutup-shift--rekonsiliasi-kas-laci)
4. [Alur Operasional Rutin Admin Toko](#4-alur-operasional-rutin-admin-toko)
   - [Pencatatan Barang Masuk (Belanja Pasokan)](#pencatatan-barang-masuk-belanja-pasokan)
   - [Penyesuaian Stok (Stock Opname, Rusak, Hilang)](#penyesuaian-stok-stock-opname-rusak-hilang)
   - [Analisis Barang Terlaris & Rekomendasi Restok (Metode ABC)](#analisis-barang-terlaris--rekomendasi-restok-metode-abc)
   - [Laporan Keuangan & Audit Mutasi Stok (Cetak PDF)](#laporan-keuangan--audit-mutasi-stok-cetak-pdf)
5. [Fungsi Detail 8 Menu Utama di Bilah Samping (Sidebar)](#5-fungsi-detail-8-menu-utama-di-bilah-samping-sidebar)
6. [Pertanyaan yang Sering Muncul (FAQ) & Tips Agar Tidak Selisih](#6-pertanyaan-yang-sering-muncul-faq--tips-agar-tidak-selisih)

---

## 1. Pendahuluan: Apa itu Stokify?

**Stokify** adalah aplikasi kasir (POS) dan manajemen persediaan barang yang dirancang khusus untuk pemilik toko kelontong, kios sembako, dan ritel mandiri. 

Sistem ini bersifat **Multi-Tenant Terisolasi**, artinya setiap kios yang mendaftar memiliki ruang data yang 100% terpisah. Data barang, harga modal, riwayat kasir, dan keuangan kios Anda tidak akan pernah bercampur dengan kios lain.

---

## 2. Alur Dari Awal Sampai Beroperasi (Apa yang Diinput Duluan?)

Banyak pengguna baru bingung harus mulai dari mana. Urutan resmi yang benar adalah:

```
[Registrasi Kios] ➔ [Login Admin] ➔ [Pengaturan Kios] ➔ [Buat Kategori] ➔ [Input Data Barang] ➔ [Tambah Supplier] ➔ [Buat Akun Kasir] ➔ [Siap Jualan di Kasir POS]
```

### Tahap 1: Registrasi Kios Baru
1. Masuk ke halaman pendaftaran melalui tombol **Daftar Kios Gratis** di halaman depan (`/daftar`).
2. Masukkan identitas toko:
   - **Nama Kios:** Contoh: *Kios Berkah Makmur*
   - **Alamat & No. Telepon Toko** (opsional pada saat awal)
3. Masukkan identitas pemilik akun (Admin):
   - **Nama Lengkap:** Nama pemilik usaha
   - **Username:** Nama pengguna unik untuk login (minimal 4 karakter)
   - **Email:** Digunakan untuk pemulihan kata sandi
   - **Kata Sandi:** Minimal 8 karakter
4. Tekan tombol **Daftar Kios**. Sistem otomatis menyiapkan database khusus untuk kios Anda dan langsung memasukkan Anda ke dalam sistem.

---

### Tahap 2: Login Pemilik (Admin)
Jika Anda sudah memiliki akun:
1. Masuk melalui menu **Masuk** (`/masuk`).
2. Anda bisa login menggunakan **Email**, **Username**, ataupun **Nama Kios** beserta kata sandi Anda.
3. Setelah login berhasil, Anda langsung diarahkan ke halaman **Dashboard Admin**.

---

### Tahap 3: Mengisi Pengaturan Profil Kios
*(Menu: Pengaturan Kios ➔ Profil Kios)*
- Mengapa ini penting? Karena nama kios, alamat, dan nomor telepon yang Anda simpan di sini akan **otomatis tercetak di bagian atas kertas struk belanja pembeli**.
- Pastikan nomor kontak dan alamat sudah benar agar struk belanja terlihat profesional dan meyakinkan.

---

### Tahap 4: Menyiapkan Kategori Barang
*(Menu: Data Barang ➔ Tab Kategori Barang)*
- Sebelum memasukkan barang, buatlah beberapa kelompok kategori agar inventori rapi.
- Contoh kategori:
  - *Sembako* (akan menghasilkan awalan kode otomatis: **SMB-001**)
  - *Minuman* (awalan kode otomatis: **MNM-001**)
  - *Makanan Ringan* (awalan kode otomatis: **MKR-001**)
  - *Kebutuhan Rumah Tangga*
  - *Rokok* (awalan kode otomatis: **ROK-001**)

---

### Tahap 5: Menginput Data Barang & Stok Awal
*(Menu: Data Barang ➔ Tombol Tambah Barang)*
Di form ini Anda memasukkan master dagangan kios Anda:
1. **Pilih Kategori:** Begitu kategori dipilih, kotak **Kode Barang** otomatis terisi (misal: `SMB-001`). Anda juga bisa mengetik barcode kemasan langsung menggunakan alat pemindai (*barcode scanner*).
2. **Nama Barang:** Contoh: *Minyak Goreng 2L Tropical*.
3. **Satuan:** Pilih satuan terkecil yang dijual (contoh: *botol*, *kg*, *bungkus*, *pcs*).
4. **Harga Beli (Harga Modal):** Harga saat Anda membeli barang dari agen/supplier.
5. **Harga Jual:** Harga yang dikenakan kepada pembeli di kasir.
6. **Stok Awal:** Jumlah fisik barang yang saat ini sudah ada di rak toko Anda.
7. **Batas Stok Minimum:** Angka peringatan (misal: diisi `5`). Jika stok tersisa 5 atau kurang, ikon lonceng notifikasi otomatis berdering merah memberi tahu stok perlu ditambah.

---

### Tahap 6: Mendaftarkan Supplier (Pemasok)
*(Menu: Barang Masuk ➔ Tab Data Supplier)*
- Masukkan daftar agen, distributor, atau pedagang besar tempat Anda biasa membeli pasokan barang.
- Catat nama agen, nomor WhatsApp/telepon perwakilan sales, serta alamat gudang mereka.
- Ini mempermudah Anda menghubungi mereka saat persediaan menipis.

---

### Tahap 7: Membuat Akun Petugas Kasir
*(Menu: Pengaturan Kios ➔ Tab Petugas Kasir)*
- Jangan gunakan akun Admin untuk transaksi harian kasir demi keamanan data keuangan.
- Buat akun khusus kasir:
  - Masukkan nama karyawan (misal: *Siti Kasir*).
  - Tentukan username (misal: *siti.berkah*).
  - Buat kata sandi khusus kasir.
- Petugas kasir hanya bisa mengakses layar **Kasir POS** dan tidak bisa melihat keuntungan toko, laporan keuangan, atau mengubah harga modal.

---

## 3. Alur Operasional Harian Kasir (POS)

Setelah persiapan selesai, toko siap melayani pembeli setiap hari dengan alur berikut:

### Alur 1: Buka Shift & Masukkan Modal Awal
1. Petugas kasir membuka menu **Kasir POS** (`/kasir`).
2. Sebelum melayani transaksi pertama, kasir **wajib memasukkan Modal Awal Laci** (uang kembalian yang disiapkan di laci kasir pada pagi hari, contoh: `Rp 200.000`).
3. Tekan **Buka Shift Sekarang**. Layar kasir aktif dan siap memindai barang.

### Alur 2: Transaksi Penjualan & Cetak Struk
1. **Input Belanjaan:**
   - Arahkan barcode scanner ke kemasan barang, ATAU
   - Ketik nama/kode barang di kolom pencarian kilat, lalu tekan Enter.
2. Atur jumlah beli (*quantity*).
3. **Pembayaran:**
   - Tekan tombol Bayar (atau pintasan tombol cepat).
   - Masukkan nominal uang yang diterima dari pembeli (contoh: belanja `Rp 45.000`, uang diterima `Rp 50.000`).
   - Sistem secara otomatis menghitung uang kembalian (`Rp 5.000`) dengan angka tebal dan jelas.
4. **Selesaikan & Cetak:**
   - Klik **Simpan Transaksi**.
   - Muncul dialog struk, tekan **Cetak Struk** (mendukung printer thermal 58mm dan 80mm).
   - Pada detik itu juga, stok barang di database toko otomatis berkurang secara *real-time*.

### Alur 3: Tutup Shift & Rekonsiliasi Kas Laci
Saat jam kerja kasir selesai:
1. Kasir menekan tombol **Tutup Shift**.
2. **Hitung Buta (*Blind Count*):** Kasir menghitung fisik seluruh lembaran dan koin uang di dalam laci, lalu mengetik total uang fisik yang dihitung ke sistem tanpa diberi tahu sistem berapa total seharusnya.
3. Sistem secara otomatis membandingkan:
   - `Uang Seharusnya = Modal Awal + Total Penjualan Tunai`
   - `Uang Fisik Dihitung`
4. Hasil rekonsiliasi akan langsung terlihat:
   - **Uang Pas (Seimbang)**
   - **Uang Kurang (Selisih minus)**
   - **Uang Lebih (Selisih plus)**
5. Kasir dapat mengisi catatan alasan bila ada selisih, lalu mengonfirmasi penutupan shift. Rekapitulasi shift langsung tercatat rapi di laporan admin.

---

## 4. Alur Operasional Rutin Admin Toko

Sebagai pemilik kios, berikut alur kerja yang Anda lakukan secara berkala:

### Pencatatan Barang Masuk (Belanja Pasokan)
*(Menu: Barang Masuk ➔ Tambah Barang Masuk)*
Ketika pasokan belanja datang dari supplier:
1. Pilih supplier pengirim dan masukkan nomor faktur/nota dari supplier.
2. Pilih barang yang datang dan masukkan jumlah serta harga beli terkini.
3. Simpan nota.
4. **Keunggulan Sistem:**
   - Stok bertambah seketika.
   - Jika harga beli dari supplier mengalami kenaikan/penurunan dibanding sebelumnya, sistem otomatis menghitung ulang **Harga Modal Rata-rata Tertimbang (*Weighted Average Cost*)**, sehingga laba kotor Anda tetap akurat tanpa perlu pusing menghitung manual.

### Penyesuaian Stok (Stock Opname, Rusak, Hilang)
*(Menu: Penyesuaian Stok ➔ Buat Penyesuaian)*
Dilakukan mingguan atau bulanan:
1. Bandingkan stok fisik yang ada di rak dengan stok di sistem.
2. Jika ada selisih fisik (misalnya: telur pecah 2 butir, mie instan kedaluwarsa 1 bungkus, atau selisih hitung):
   - Pilih barang yang bersangkutan.
   - Pilih jenis penyesuaian: *Barang Rusak*, *Kedaluwarsa*, *Hilang*, atau *Koreksi Opname*.
   - Masukkan jumlah selisih.
3. Nilai kerugian penyesuaian stok ini akan otomatis dicatat pada laporan keuangan sebagai pengurang keuntungan kotor.

### Analisis Barang Terlaris & Rekomendasi Restok (Metode ABC)
*(Menu: Laporan & Analisis ➔ Tab Analisis Barang)*
Sistem secara cerdas mengelompokkan barang dagangan Anda:
- **Kelas A (Sangat Laris):** 20% barang yang menyumbang 80% omzet kios. Barang ini tidak boleh sampai kosong sedetik pun.
- **Kelas B (Sedang):** Penjualan stabil.
- **Kelas C (Slow Moving):** Perputaran lambat, jangan beli terlalu banyak agar modal tidak macet.
- **Titik Pesan Ulang (*Reorder Point / ROP*):** Sistem memberi rekomendasi jumlah unit yang harus dibeli ke supplier sebelum stok di rak habis.

### Laporan Keuangan & Audit Mutasi Stok (Cetak PDF)
*(Menu: Laporan & Analisis)*
1. **Laporan Keuangan:** Melihat total omzet penjualan, total HPP, total laba kotor, dan laba setelah penyesuaian stok pada periode harian, mingguan, bulanan, atau tahunan.
2. **Audit Mutasi Stok:** Riwayat buku besar pergerakan barang (kapan masuk, kapan laku terjual di kasir, dan sisa saldo stok).
3. Anda bisa mengunduh dan mencetak seluruh dokumen resmi ini ke format **PDF A4** bertanggal untuk arsip toko Anda.

---

## 5. Fungsi Detail 8 Menu Utama di Bilah Samping (Sidebar)

Berikut adalah rangkuman fungsi setiap menu di bilah navigasi kiri:

| No | Nama Menu | Fungsi Utama |
|---|---|---|
| **1** | **Dashboard** | Pusat kendali visual admin. Menampilkan omzet hari ini, jumlah nota hari ini, kartu lonceng stok menipis, kasir aktif, serta checklist panduan kios baru. |
| **2** | **Kasir POS** | Layar mesin kasir kasir untuk memproses transaksi belanja pembeli secara kilat, buka/tutup shift kasir, dan cetak struk nota belanja. |
| **3** | **Penjualan & Shift** | Memantau seluruh arsip nota transaksi penjualan harian/bulanan dan memantau riwayat shift kasir beserta laporan rekonsiliasi uang laci kasir. |
| **4** | **Data Barang** | Manajemen master barang dagangan kios: tambah barang baru, ubah harga jual/modal, cetak/scan barcode, atur batas stok minimum, dan kelola kategori. |
| **5** | **Barang Masuk** | Mencatat faktur pasokan belanja stok baru dari supplier, mencatat tanggal jatuh tempo/pembayaran, dan mengelola daftar kontak supplier. |
| **6** | **Penyesuaian Stok** | Fitur stock opname berkala untuk mencocokkan stok fisik di toko dengan stok sistem, mencatat barang pecah/rusak, barang kadaluarsa, atau barang hilang. |
| **7** | **Laporan & Analisis** | Laporan finansial lengkap (Omzet, HPP, Laba Kotor), audit buku besar mutasi stok, analisis barang terlaris Pareto ABC, rekomendasi restok, dan unduh PDF. |
| **8** | **Pengaturan Kios** | Mengatur identitas toko (nama kios, alamat, telepon di kepala struk), membuat akun petugas kasir, reset kata sandi kasir, dan melihat log aktivitas audit. |

---

## 6. Pertanyaan yang Sering Muncul (FAQ) & Tips Agar Tidak Selisih

**T: Kenapa stok barang saya tidak bertambah setelah didaftarkan?**  
*J:* Saat membuat barang baru, pastikan mengisi kolom **Stok Awal**. Jika barang baru dibeli belakangan dari agen, catatlah melalui menu **Barang Masuk**.

**T: Mengapa kasir tidak bisa melihat keuntungan kios dan laporan keuangan?**  
*J:* Demi keamanan pemilik toko, akun petugas kasir memiliki hak akses terbatas (*RBAC*). Kasir hanya memiliki akses ke layar penjualan kasir POS untuk melayani pembeli.

**T: Kapan saya harus melakukan penyesuaian stok?**  
*J:* Lakukan penyesuaian stok setiap kali ada barang rusak, bocor, pecah, kedaluwarsa, atau saat Anda melakukan penghitungan fisik stok mingguan.

**T: Apa tips agar uang di laci kasir tidak pernah selisih saat tutup shift?**  
1. Pastikan uang modal awal yang diinput saat buka shift dihitung dengan teliti.
2. Jangan mengambil uang laci kasir untuk keperluan pribadi tanpa pencatatan.
3. Selalu masukkan transaksi penjualan ke dalam sistem setiap kali menerima uang dari pembeli.

---

## 7. Panduan Satuan Barang: Karton vs Botol/Eceran (Mencegah Sistem Tercatat Rugi)

### Kasus Nyata: Mengapa Sistem Mencatat Rugi?
Misalkan Anda membeli **Teh Pucuk 1 Karton (isi 10 botol)** seharga **Rp 25.000**, lalu Anda ingin menjualnya eceran dengan harga **Rp 5.000 per botol**.

**Kesalahan yang Sering Terjadi:**
* Satuan dipilih: `botol`
* Harga Modal diinput: `Rp 25.000` *(Salah, karena ini adalah harga 1 karton penuh)*
* Harga Jual diinput: `Rp 5.000` *(Harga 1 botol)*

**Akibatnya di Sistem:**
Saat kasir berhasil menjual 1 botol:
* Pemasukan Penjualan = `Rp 5.000`
* Harga Pokok / Modal = `Rp 25.000`
* **Laba Kotor = Rp 5.000 - Rp 25.000 = -Rp 20.000 (Sistem mencatat RUGI Rp 20.000!)**

---

### Rumus Emas Inventori: "Modal & Stok Harus Mengikuti Satuan Terkecil yang Dijual"

Jika Anda memutuskan menjual barang secara **eceran per botol**, maka nilai modal dan jumlah stok wajib dikonversi ke **botol**:

1. **Hitung Modal per Botol:**
   $$\text{Harga Modal per Botol} = \frac{\text{Harga 1 Karton}}{\text{Jumlah Botol per Karton}} = \frac{\text{Rp } 25.000}{10} = \mathbf{Rp\ 2.500}$$

2. **Pengisian Form Data Barang yang Benar:**
   * Nama Barang: `Teh Pucuk 350ml`
   * Satuan: `botol`
   * Harga Modal: `Rp 2.500`
   * Harga Jual: `Rp 5.000`
   * Stok Awal: Jika Anda punya 2 karton, masukkan `20` botol (bukan 2).

3. **Hasil Perhitungan di Sistem:**
   Saat laku 1 botol:
   * Pemasukan = `Rp 5.000`
   * Modal = `Rp 2.500`
   * **Laba Kotor = +Rp 2.500 (UNTUNG Rp 2.500 per botol!)**

---

### Bagaimana Jika Toko Ingin Menjual Dua-duanya? (Bisa Eceran Per Botol DAN Bisa Per Karton Utuh)

Banyak warung melayani pembeli eceran sekaligus pembeli yang butuh 1 karton utuh (misal untuk acara atau hajatan). Ada **2 strategi terbaik** untuk menanganinya:

#### Strategi A: Mendaftarkan 2 Data Barang Terpisah (Paling Rapi & Direkomendasikan)
Buat 2 data barang di menu **Data Barang**:

1. **Barang 1 (Khusus Eceran):**
   * Nama: `Teh Pucuk 350ml (Botol)`
   * Satuan: `botol`
   * Harga Modal: `Rp 2.500`
   * Harga Jual: `Rp 5.000`

2. **Barang 2 (Khusus Dus / Karton Utuh):**
   * Nama: `Teh Pucuk 350ml (Karton isi 10)`
   * Satuan: `karton`
   * Harga Modal: `Rp 25.000`
   * Harga Jual: `Rp 45.000` *(bisa diberi harga grosir lebih murah dari eceran)*

*Tips Saat Membongkar Dus:*  
Jika stok botol eceran di rak habis dan Anda membuka 1 karton dari gudang untuk diecer, buka menu **Penyesuaian Stok**:
* Kurangi 1 pada `Teh Pucuk Karton`
* Tambah 10 pada `Teh Pucuk Botol`
Dengan cara ini, jumlah fisik barang di gudang maupun di rak kasir akan selalu 100% akurat dan tidak pernah selisih!

---

#### Strategi B: Tetap 1 Barang Satuan Botol (Paling Sederhana)
* Tetap gunakan 1 data barang dengan satuan `botol` (Modal: `Rp 2.500`, Jual: `Rp 5.000`).
* Seluruh stok dihitung dalam botol (misal punya 5 karton isi 10 = `50 botol`).
* Jika ada pembeli yang membeli 1 karton utuh, kasir cukup memasukkan jumlah beli `10` pada layar kasir POS.

---

### Cara Mencatat Saat Belanja Stok Baru di Menu "Barang Masuk":
Jika barang Anda ber-satuan `botol`, saat belanja pasokan 1 karton isi 10 seharga Rp 25.000 datang dari supplier:
* **Jumlah Masuk:** Masukkan `10` (botol)
* **Harga Beli Satuan:** Masukkan `Rp 2.500`
* Total faktur otomatis menjadi: $10 \times \text{Rp } 2.500 = \text{Rp } 25.000$. Stok bertambah 10 botol dan harga modal tetap stabil di Rp 2.500!

---
*Dokumen ini dibuat resmi untuk panduan operasional pengguna aplikasi Stokify.*
