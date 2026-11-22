# 11. Matriks Skenario Uji Penerimaan Pengguna (UAT)

Dokumen ini memuat 42 skenario pengujian manual penerimaan pengguna (*User Acceptance Testing* / UAT) yang mencakup seluruh modul dan fitur fungsional sistem **Stokify**.

---

## Matriks Pengujian UAT (42 Skenario)

| Kode | Modul | Skenario Pengujian | Hasil yang Diharapkan | Status |
|:---:|:---|:---|:---|:---:|
| **UAT-01** | Landing | Mengakses URL beranda `/` | Halaman landing tampil cepat dengan palet Forest, 8 fitur, 3 batasan sistem, dan tombol aksi | LULUS |
| **UAT-02** | Registrasi | Mendaftar kios baru dengan data valid | Kios dan admin baru terbuat, diarahkan otomatis ke Dashboard | LULUS |
| **UAT-03** | Registrasi | Mendaftar dengan email yang sudah ada | Submit ditolak dengan pesan validasi "Email sudah terdaftar" | LULUS |
| **UAT-04** | Registrasi | Mendaftar dengan password 6 karakter (< 8) | Submit ditolak dengan pesan validasi "Minimal 8 karakter" | LULUS |
| **UAT-05** | Autentikasi | Login menggunakan alamat email admin | Login berhasil dan diarahkan ke Dashboard Admin | LULUS |
| **UAT-06** | Autentikasi | Login menggunakan username kasir | Login berhasil dan diarahkan ke layar POS Kasir | LULUS |
| **UAT-07** | Autentikasi | Login menggunakan kombinasi nama kios | Sistem mengenali nama kios dan berhasil mengautentikasi | LULUS |
| **UAT-08** | Autentikasi | Login akun kasir yang dinonaktifkan admin | Ditolak masuk dengan pesan "Akun Anda sedang dinonaktifkan" | LULUS |
| **UAT-09** | Profil Kios | Admin memperbarui nama kios dan alamat toko | Identitas kios terbarukan dan tercatat pada log `kios.ubah` | LULUS |
| **UAT-10** | Profil Kios | Kasir mencoba membuka rute `/admin/kios` | Akses ditolak dengan status HTTP 403 Forbidden | LULUS |
| **UAT-11** | Kelola Kasir | Admin membuat akun kasir baru | Akun kasir tersimpan dan kasir dapat langsung login | LULUS |
| **UAT-12** | Kelola Kasir | Admin me-reset kata sandi akun kasir | Password berubah dan sesi lama kasir otomatis terputus | LULUS |
| **UAT-13** | Kelola Kasir | Kasir mencoba membuka menu `/admin/kasir` | Server mengembalikan respon 403 Forbidden | LULUS |
| **UAT-14** | Master Barang | Menambah master barang baru dengan stok awal | Barang tersimpan dan mutasi stok awal terbentuk otomatis | LULUS |
| **UAT-15** | Master Barang | Input kode barcode yang sama pada satu kios | Ditolak oleh validasi "Kode barang sudah digunakan" | LULUS |
| **UAT-16** | Master Barang | Mengubah harga jual master barang | Harga jual terbarukan tanpa memengaruhi snapshot nota lama | LULUS |
| **UAT-17** | Shift Kasir | Kasir membuka shift dengan modal awal Rp 50.000 | Shift berstatus `berjalan` terbuat dan modal tercatat | LULUS |
| **UAT-18** | Shift Kasir | Kasir mencoba membuka shift kedua saat masih aktif | Ditolak; kasir hanya boleh memiliki 1 shift berjalan | LULUS |
| **UAT-19** | POS Kasir | Kasir mencari barang via input ketik nama | Hasil pencarian JSON muncul instan tanpa harga modal | LULUS |
| **UAT-20** | POS Kasir | Transaksi kasir dengan uang bayar pas | Nota tersimpan, kembalian Rp 0, pop-up struk muncul | LULUS |
| **UAT-21** | POS Kasir | Transaksi kasir dengan uang bayar lebih | Kembalian terhitung presisi di layar dan tercetak di struk | LULUS |
| **UAT-22** | POS Kasir | Transaksi dengan jumlah beli melebihi stok | Transaksi di-rollback dengan pesan "Stok tidak mencukupi" | LULUS |
| **UAT-23** | POS Kasir | Klik tombol bayar berkali-kali secara cepat | Token idempotensi mencegah pemotongan stok ganda | LULUS |
| **UAT-24** | POS Kasir | Transaksi tanpa shift berjalan | Akses POS diarahkan ke halaman pembukaan shift | LULUS |
| **UAT-25** | Cetak Struk | Cetak ulang struk dari menu riwayat kasir | Struk format thermal 58mm/80mm siap dicetak ulang | LULUS |
| **UAT-26** | Void Nota | Kasir membatalkan nota sendiri pada shift aktif | Status nota berubah dibatalkan dan stok barang kembali | LULUS |
| **UAT-27** | Void Nota | Void tanpa mengisi alasan atau alasan < 5 karakter | Ditolak dengan pesan validasi "Alasan minimal 5 karakter" | LULUS |
| **UAT-28** | Void Nota | Kasir mencoba void nota milik kasir lain | Ditolak dengan status HTTP 403 Forbidden | LULUS |
| **UAT-29** | Tutup Shift | Kasir tutup shift dengan hitung buta uang fisik pas | Shift berstatus `ditutup` dengan selisih kas Rp 0 | LULUS |
| **UAT-30** | Tutup Shift | Tutup shift berselisih uang tanpa mengisi catatan | Ditolak; formulir mewajibkan mengisi catatan rekonsiliasi | LULUS |
| **UAT-31** | Tutup Shift | Kasir mencoba void nota setelah shift ditutup | Ditolak; seluruh nota terkunci permanen pasca tutup shift | LULUS |
| **UAT-32** | Shift Kasir | Admin menutup paksa shift yang menggantung | Shift tertutup paksa dengan penanda `ditutup_paksa = true` | LULUS |
| **UAT-33** | Barang Masuk | Catat pasokan barang masuk dari supplier | Stok bertambah dan HPP Moving Average terhitung otomatis | LULUS |
| **UAT-34** | Opname Stok | Eksekusi hasil stok opname fisik di rak | Saldo stok sistem dikoreksi sesuai riil dan kerugian tercatat | LULUS |
| **UAT-35** | Laporan | Admin membuka layar Laporan Keuangan | Menampilkan Omzet, HPP, dan Laba Kotor (tanpa istilah dilarang) | LULUS |
| **UAT-36** | Laporan | Admin mengunduh PDF Laporan Keuangan | Berkas PDF resmi ber-kop kios berhasil diunduh | LULUS |
| **UAT-37** | Laporan | Kasir mencoba mengakses `/admin/laporan/*` | Server mengembalikan respon 403 Forbidden | LULUS |
| **UAT-38** | Analisis | Membuka layar Pareto Fast/Slow Moving | Produk terklasifikasi ke kategori A (80%), B, dan C | LULUS |
| **UAT-39** | Analisis | Memeriksa rekomendasi restok ROP | Menampilkan status perlu restok dan saran kuantitas belanja | LULUS |
| **UAT-40** | Log Audit | Membuka penampil jejak aktivitas kios | Log muncul terurut waktu dan field sensitif di-mask `********` | LULUS |
| **UAT-41** | Multi-Tenant | Admin Kios A mencoba membuka ID milik Kios B | Server mengembalikan respon 404 Not Found secara aman | LULUS |
| **UAT-42** | Integritas | Menjalankan `php artisan stokify:cek-integritas` | Seluruh 5 indikator integritas lulus dengan 0 temuan | LULUS |
