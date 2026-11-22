# Panduan Komprehensif Seluruh Menu Aplikasi Stokify

Dokumen ini berisi penjelasan terlengkap mengenai seluruh menu, sub-menu, tab, serta tombol fungsi yang ada di dalam aplikasi kasir dan inventori **Stokify**.

---

## Daftar Isi Cepat
1. [Struktur Utama Menu Navigasi](#struktur-utama-menu-navigasi)
2. [Kelompok 1: Operasional](#kelompok-1-operasional)
   - [Menu 1: Dashboard](#menu-1-dashboard)
   - [Menu 2: Kasir POS](#menu-2-kasir-pos)
   - [Menu 3: Penjualan & Shift](#menu-3-penjualan--shift)
3. [Kelompok 2: Inventori](#kelompok-2-inventori)
   - [Menu 4: Data Barang](#menu-4-data-barang)
   - [Menu 5: Barang Masuk](#menu-5-barang-masuk)
   - [Menu 6: Penyesuaian Stok](#menu-6-penyesuaian-stok)
4. [Kelompok 3: Laporan](#kelompok-3-laporan)
   - [Menu 7: Laporan & Analisis](#menu-7-laporan--analisis)
5. [Kelompok 4: Pengaturan](#kelompok-4-pengaturan)
   - [Menu 8: Pengaturan Kios](#menu-8-pengaturan-kios)
6. [Tabel Pintas: Kapan Menggunakan Menu Apa?](#tabel-pintas-kapan-menggunakan-menu-apa)

---

## Struktur Utama Menu Navigasi
Di bilah samping kiri (*sidebar*), menu Stokify dikelompokkan menjadi 4 bagian fungsional:
* **OPERASIONAL:** Dashboard, Kasir POS, Penjualan & Shift
* **INVENTORI:** Data Barang, Barang Masuk, Penyesuaian Stok
* **LAPORAN:** Laporan & Analisis
* **PENGATURAN:** Pengaturan Kios (Profil, Petugas Kasir, Log Aktivitas)

---

## Kelompok 1: Operasional

### Menu 1: Dashboard
**Fungsi Utama:** Sebagai "layar kendali utama" untuk memantau kesehatan operasional dan finansial toko/kios dalam 1 tampilan ringkas secara seketika (*real-time*).

**Komponen & Informasi di Dashboard:**
1. **4 Kartu Metrik Utama (Atas):**
   - **Omzet Hari Ini:** Total penerimaan uang kotor dari transaksi kasir yang selesai pada hari ini.
   - **Total Transaksi Hari Ini:** Jumlah nota belanja pelanggan yang berhasil dilayani hari ini.
   - **Keuntungan Kotor Hari Ini:** Nilai keuntungan yang diperoleh hari ini (dihitung dari: *Omzet dikurangi Modal Pokok barang yang laku*).
   - **Peringatan Stok Menipis:** Jumlah barang yang stok fisiknya sudah berada di bawah batas minimum dan harus segera dipasok ulang.
2. **Grafik Omzet Penjualan 7 Hari Terakhir:**
   - Menampilkan diagram batang visual perkembangan omzet harian kios dari hari ke hari dalam sepekan terakhir.
3. **Kartu Status Shift Kasir Berjalan:**
   - Menampilkan informasi petugas kasir mana yang saat ini sedang aktif menjaga kasir, waktu buka shift, dan mendeteksi secara otomatis jika ada shift yang berjalan lebih dari 16 jam tanpa ditutup.
4. **Kartu Peringatan Stok Menipis (Maksimal 5 Barang Terkritis):**
   - Memberi tahu pemilik toko barang apa saja yang stoknya paling kritis agar tidak sampai kehabisan dagangan saat dicari pembeli.
5. **Kartu Barang Terlaris 30 Hari Terakhir (Top 5):**
   - Menampilkan 5 barang yang paling sering dibeli pembeli beserta kontribusi keuntungan kotornya.
6. **Log Aktivitas Terkini:**
   - Menampilkan 5 rekaman aktivitas keamanan terakhir (misal: siapa kasir yang baru login, siapa yang menginput barang baru, atau pembatalan transaksi).

---

### Menu 2: Kasir POS
**Fungsi Utama:** Layar kasir cepat untuk melayani transaksi pembeli harian toko/kios. Dirancang sangat cepat, responsif, dan mendukung penggunaan pemindai barcode (*barcode scanner*) maupun ketik keyboard.

**Komponen & Cara Penggunaan Kasir POS:**
1. **Pencarian Cepat & Barcode:**
   - Arahkan pemindai barcode ke kemasan barang, atau ketik nama/kode barang di kolom pencarian.
   - Tekan **Enter**: barang akan otomatis langsung masuk ke keranjang belanja.
2. **Keranjang Belanja:**
   - **Kontrol Kuantitas Kapsul Bersih:** Terdapat tombol minus `[-]`, kolom angka, dan tombol plus `[+]`.
     - Klik `+` atau `-` untuk menambah atau mengurangi barang satuan.
     - Klik langsung pada kotak angkanya untuk mengetik kuantitas dalam jumlah banyak (misal beli 24 botol sekaligus) tanpa perlu memencet tombol plus berkali-kali.
   - **Rincian Perhitungan Baris:** Menampilkan harga satuan, jumlah pembelian, satuan barang (pcs/botol/pack), dan subtotal rupiah.
   - **Tombol Sampah (Hapus Baris):** Untuk membatalkan salah satu barang jika pembeli berubah pikiran.
   - **Tombol Kosongkan:** Untuk menghapus seluruh isi keranjang jika transaksi dibatalkan sepenuhnya.
3. **Bagian Pembayaran Tunai & Kembalian (Tekan F9):**
   - **Total Tagihan:** Menampilkan angka besar nominal yang wajib dibayar pembeli.
   - **Kolom Uang Diterima:** Masukkan uang tunai yang diberikan oleh pembeli.
   - **Tombol Uang Cepat:** Tersedia tombol nominal instan (Uang Pas, Rp 10.000, Rp 20.000, Rp 50.000, Rp 100.000).
   - **Kembalian:** Dihitung otomatis oleh sistem secara akurat seketika uang diinput.
4. **Penyelesaian Transaksi & Cetak Struk:**
   - Tekan tombol **Selesaikan Transaksi (Enter)**.
   - Muncul jendela konfirmasi sukses beserta tombol **Cetak Struk** untuk mencetak struk belanja termal resmi bertuliskan identitas kios Anda.
5. **Tombol Pintas Keyboard (*Shortcut* Kasir):**
   - `F2`: Langsung memindahkan kursor ke kolom pencarian barang/barcode.
   - `F9`: Langsung memindahkan kursor ke kolom uang pembayaran.
   - `Enter`: Memproses dan menyelesaikan transaksi.

---

### Menu 3: Penjualan & Shift
Menu ini memiliki 2 tab navigasi:

#### Tab 1: Riwayat Penjualan
* **Fungsi:** Menyimpan seluruh riwayat nota penjualan yang pernah terjadi di toko/kios Anda.
* **Fitur & Filter:**
  * **Ringkasan Angka di Atas Tabel:** Menampilkan Total Omzet, Total Transaksi Selesai, dan Total Keuntungan Kotor sesuai rentang filter yang sedang aktif.
  * **Filter Pencarian:** Anda bisa menyaring berdasarkan *Nomor Nota*, rentang *Tanggal Mulai* s/d *Tanggal Selesai*, *Petugas Kasir*, serta *Status* (Selesai / Dibatalkan).
  * **Tombol Detail Transaksi:** Menampilkan seluruh rincian barang yang dibeli pada nota tersebut, harga modal bersejarah saat barang itu dijual, persentase keuntungan, dan waktu pencatatan presisi.
  * **Tombol Batalkan Transaksi:** Jika terjadi kesalahan input kasir, admin dapat membatalkan nota. Begitu dibatalkan, stok barang yang ada di nota tersebut akan otomatis dikembalikan ke gudang/rak secara utuh.

#### Tab 2: Shift Kasir
* **Fungsi:** Mengontrol dan mengaudit kejujuran kas laci kasir setiap pergantian jam kerja (*shift*).
* **Alur Shift:**
  1. **Buka Shift:** Kasir memasukkan modal awal uang receh di laci kas (*cash float*).
  2. **Selama Shift:** Semua transaksi tunai kasir tercatat otomatis oleh sistem.
  3. **Tutup Shift:** Saat jam kerja selesai, kasir menghitung uang fisik di laci dan memasukkan nominalnya ke sistem.
  4. **Rekonsiliasi Otomatis:** Sistem langsung membandingkan *Uang Fisik Sebenarnya* dengan *Uang Hitungan Komputer*. Jika ada perbedaan, akan muncul status **Selisih Kas** (Lebih atau Kurang).
  5. **Fitur Tutup Paksa:** Khusus admin, jika kasir lupa menutup shift atau mendadak berhalangan hadir, admin dapat menutup shift secara paksa agar kasir berikutnya bisa bertugas.

---

## Kelompok 2: Inventori

### Menu 4: Data Barang
Menu ini merupakan master katalog dagangan Anda, terdiri dari 2 tab:

#### Tab 1: Daftar Barang
* **Fungsi:** Mengelola seluruh data barang yang dijual di kios.
* **Kolom Informasi:**
  * *Kode Barang:* Kode unik pengenal barang (bisa barcode pabrik atau kode otomatis sistem, contoh: `SMB-001`).
  * *Nama Barang:* Nama dagangan lengkap.
  * *Kategori:* Kelompok barang (misal: Sembako, Minuman, Rokok).
  * *Harga Modal:* Modal pokok rata-rata barang saat dibeli dari pemasok.
  * *Harga Jual:* Harga resmi yang dibayar oleh pembeli di kasir.
  * *Stok Saat Ini:* Jumlah fisik barang yang tersedia dan siap dijual.
  * *Batas Minimum Stok:* Ambang batas peringatan jika stok sudah tipis.
  * *Status (Aktif / Nonaktif):* Barang nonaktif tidak akan muncul di kasir dan tidak bisa dijual sementara.
* **Tombol Tambah Barang:** Untuk mendaftarkan barang baru ke sistem lengkap dengan modal awal, harga jual, dan stok awal.
* **Tombol Edit & Detail Mutasi:** Mengubah nama/harga barang atau melihat riwayat pergerakan stok barang dari waktu ke waktu.

#### Tab 2: Kategori Barang
* **Fungsi:** Mengelompokkan barang agar tertata rapi di rak dan laporan.
* **Pengaturan Kode Awalan (*Prefix*):**
  Saat membuat kategori baru, Anda bisa menentukan awalan kode otomatis. Misalnya kategori *Minuman* diberi awalan `MNM`. Setiap kali ada barang minuman baru, sistem akan otomatis memberi kode `MNM-001`, `MNM-002`, dan seterusnya.

---

### Menu 5: Barang Masuk
Menu ini digunakan ketika kios Anda kedatangan pasokan dagangan baru dari supplier / agen / distributor. Terdiri dari 2 tab:

#### Tab 1: Riwayat Barang Masuk
* **Fungsi:** Mencatat penerimaan stok baru ke dalam toko.
* **Tombol Catat Pasokan Baru:**
  1. Pilih nama Supplier pengirim barang.
  2. Masukkan nomor faktur / nomor surat jalan dari supplier.
  3. Pilih tanggal pasokan datang.
  4. Tambahkan baris barang yang dipasok, kuantitas yang masuk, dan harga beli per satuannya.
  5. Simpan catatan pasokan.
* **Dampak Otomatis di Sistem:**
  * Stok barang terkait di kios akan **otomatis bertambah**.
  * Harga modal pokok barang akan **otomatis disesuaikan secara akurat** (*Weighted Average Cost*).
* **Pembatalan Nota Pasokan:** Jika salah menginput kuantitas faktur, nota barang masuk bisa dibatalkan, dan stok fisik di rak akan ditarik kembali secara otomatis.

#### Tab 2: Data Supplier
* **Fungsi:** Menyimpan direktori buku alamat kontak agen, distributor, dan pemasok barang kios Anda.
* **Informasi:** Nama supplier, nomor kontak telepon/WhatsApp, alamat kantor, nama sales yang bertugas, serta catatan tempo pembayaran.

---

### Menu 6: Penyesuaian Stok
**Fungsi Utama:** Digunakan saat terjadi ketidakcocokan antara data di komputer dengan kondisi barang fisik di rak toko, atau ketika ada barang yang rusak, bocor, hilang, dan kedaluwarsa.

**Fitur yang Tersedia:**
1. **Penyesuaian Satuan (Manual):**
   - Digunakan untuk kasus harian mendesak (misal: ada 1 botol minyak goreng pecah atau 2 bungkus mie kedaluwarsa).
   - Admin memilih nama barang, memilih jenis alasan (*Rusak*, *Kedaluwarsa*, *Hilang*, atau *Koreksi Hitung*), lalu memasukkan jumlah selisihnya.
   - Sistem akan otomatis memotong stok dan menghitung **nilai kerugian rupiah** berdasarkan modal pokok barang tersebut.
2. **Lembar Kerja Stock Opname Toko:**
   - Digunakan saat toko melakukan audit fisik menyeluruh (misal: opname bulanan atau mingguan).
   - Admin dapat memilih kategori barang yang ingin dihitung, lalu sistem menampilkan lembar isian jumlah fisik riil di rak. Selisih lebih atau kurang akan otomatis diperbaiki oleh sistem menjadi mutasi penyesuaian resmi.

---

## Kelompok 3: Laporan

### Menu 7: Laporan & Analisis
Pusat evaluasi data keuangan, pergerakan barang, dan strategi restok toko Anda:

#### Sub-Menu 1: Laporan Keuangan & Penjualan
* Rekapitulasi omzet kotor, modal barang yang terjual, serta keuntungan kotor yang didapatkan kios.
* Bisa disaring berdasarkan rentang: **Harian**, **Mingguan**, **Bulanan**, atau **Rentang Tanggal Kustom**.
* Dilengkapi tombol **Unduh PDF Resmi** bertanda tangan untuk pembukuan fisik pemilik toko.

#### Sub-Menu 2: Laporan Arus Stok
* Memperlihatkan perputaran fisik barang dari titik awal sampai akhir:
  * Berapa stok awal periode.
  * Berapa jumlah barang yang masuk dari supplier.
  * Berapa barang yang keluar lewat transaksi kasir.
  * Berapa penyesuaian karena rusak/hilang.
  * Berapa saldo akhir stok di rak saat ini.
* Mendukung ekspor laporan ke format PDF.

#### Sub-Menu 3: Laporan Audit Kasir
* Rekapitulasi shift seluruh staf kasir, jam operasional kasir, serta catatan selisih kas (lebih/kurang) untuk mendeteksi kecurangan atau kelalaian penghitungan uang fisik.

#### Sub-Menu 4: Analisis Performa Barang (Top 10 Terlaris)
* **10 Barang Terlaris:** Menampilkan ranking 1 sampai 10 barang yang paling sering dibeli pelanggan lengkap dengan grafik batang visual proporsi penjualan.
* **Klasifikasi Performa Barang:**
  * **Fast Moving:** Barang yang laris manis dan cepat berputar (tulang punggung omzet kios).
  * **Normal:** Barang dengan tingkat penjualan stabil.
  * **Slow Moving:** Barang yang lambat laku.
  * **Tidak Laku:** Barang yang tidak pernah terjual sama sekali dalam rentang periode tersebut.
* **Barang Kurang Laku & Modal Tertahan:** Menghitung berapa nominal rupiah uang modal kios Anda yang "mati/macet" di rak toko akibat barang lambat laku.
* **Barang Margin Tipis:** Mendeteksi dagangan yang Anda jual dengan persentase keuntungan terlalu kecil (di bawah target margin kios).

#### Sub-Menu 5: Rekomendasi Restok Barang
* Menggunakan perhitungan cerdas *Reorder Point (ROP)* untuk menyarankan daftar barang apa saja yang harus segera dipesan ke supplier sebelum kehabisan stok.
* Dilengkapi tombol **Unduh Daftar Belanja PDF** yang bisa langsung Anda bawa saat berbelanja stok barang ke distributor.

---

## Kelompok 4: Pengaturan

### Menu 8: Pengaturan Kios
Menu ini menyimpan data administrasi dan keamanan kios, terdiri dari 3 tab:

#### Tab 1: Profil Kios
* **Pengaturan Identitas Kios:**
  - Nama Kios
  - Alamat Toko
  - Nomor Telepon / WhatsApp
  - Catatan Kaki (*Footer*) Kertas Struk (contoh: "Terima kasih atas kunjungan Anda. Barang yang sudah dibeli tidak dapat ditukar").
  > *Penting: Informasi nama, alamat, dan nomor telepon di sini akan otomatis tercetak pada bagian atas struk belanja pembeli di kasir.*
* **Parameter Cerdas Toko:**
  - Mengatur batas hari pengiriman supplier (*lead time*), stok pengaman (*safety stock*), dan target margin keuntungan kios.

#### Tab 2: Petugas Kasir
* **Manajemen Akun Staf Toko:**
  - Menambah akun kasir baru (Nama Lengkap, Username, dan Password/PIN).
  - Tombol **Reset Password** jika petugas kasir lupa kata sandi.
  - Tombol **Toggle Aktif / Nonaktifkan**: Jika seorang kasir sudah berhenti bekerja atau cuti, admin cukup menonaktifkan akunnya tanpa menghapus riwayat transaksi masa lalu yang pernah dibuatnya.

#### Tab 3: Log Aktivitas
* **Catatan Audit Keamanan Toko (*Audit Trail*):**
  - Merekam seluruh jejak digital secara transparan: siapa pengguna yang melakukan aksi, tanggal & jam presisi, alamat IP perangkat, serta keterangan tindakan (misal: login, membuat transaksi kasir, mengubah harga barang, menambah pasokan, atau membatalkan nota).
  - Berguna untuk mencegah manipulasi data internal toko.

---

## Tabel Pintas: Kapan Menggunakan Menu Apa?

| Situasi / Kebutuhan Toko Anda | Menu yang Harus Dibuka |
| :--- | :--- |
| Ingin melihat ringkasan omzet hari ini & stok barang yang menipis | **Dashboard** |
| Ada pembeli datang membawa barang belanjaan ke meja kasir | **Kasir POS** |
| Pelanggan minta nota cetak ulang atau ingin mengecek nota kemarin | **Penjualan & Shift** ➔ Tab *Riwayat Penjualan* |
| Kasir mau mulai bekerja pagi hari atau pergantian jam kerja | **Penjualan & Shift** ➔ Tab *Shift Kasir* |
| Ada barang dagangan merek baru yang baru pertama kali ingin dijual | **Data Barang** ➔ Tombol *Tambah Barang* |
| Mengelompokkan barang dagangan baru ke kelompok tertentu | **Data Barang** ➔ Tab *Kategori Barang* |
| Mobil distributor supplier datang membawa pasokan barang | **Barang Masuk** ➔ Tombol *Catat Pasokan Baru* |
| Ingin menyimpan nomor kontak sales distributor atau agen toko | **Barang Masuk** ➔ Tab *Data Supplier* |
| Ada telur yang pecah, susu yang bocor, atau snack kedaluwarsa | **Penyesuaian Stok** ➔ Tombol *Tambah Penyesuaian* |
| Menghitung stok fisik di seluruh rak toko di akhir bulan (Opname) | **Penyesuaian Stok** ➔ Tombol *Stock Opname* |
| Mencetak laporan keuangan laba kotor untuk pemilik modal | **Laporan & Analisis** ➔ Tab *Laporan Keuangan* |
| Mengetahui barang apa saja yang paling disukai pembeli (terlaris) | **Laporan & Analisis** ➔ Tab *Analisis Performa* |
| Mau belanja ke pasar/distributor dan butuh daftar barang yang habis | **Laporan & Analisis** ➔ Tab *Rekomendasi Restok* |
| Mengganti alamat atau nomor WhatsApp yang tercetak di kertas struk | **Pengaturan Kios** ➔ Tab *Profil Kios* |
| Mendaftarkan staf kasir baru atau mereset password kasir | **Pengaturan Kios** ➔ Tab *Petugas Kasir* |
| Memeriksa siapa yang menghapus atau mengubah data penting toko | **Pengaturan Kios** ➔ Tab *Log Aktivitas* |

---

*Dokumen ini merupakan panduan resmi aplikasi Stokify. Gunakan panduan ini sebagai rujukan operasional sehari-hari Anda dan seluruh staf kios.*
