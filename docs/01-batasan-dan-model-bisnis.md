# 01. Batasan Sistem dan Model Bisnis Stokify

Dokumen ini menjelaskan model bisnis operasional yang diadopsi oleh platform **Stokify**, prinsip transparansi kapabilitas perangkat lunak, serta 5 batasan sistem fundamental yang diterapkan beserta justifikasi teknis dan bisnisnya.

---

## 1. Model Bisnis Kios Ritel Mandiri

Stokify dirancang dengan fokus spesifik pada kios kelontong, toko sembako, dan ritel skala mikro dan kecil (*Mom-and-Pop Stores*). Karakteristik utama dari model bisnis ini meliputi:

1. **Volume Transaksi Harian Tinggi, Nominal Variatif**: Melayani ratusan pembeli setiap hari dengan pembelian cepat untuk kebutuhan pokok harian (beras, gula, minyak, telur, mi instan, minuman).
2. **Perputaran Persediaan Cepat (*Fast Moving*)**: Barang masuk secara berkala dari distributor/agen grosir, dan persediaan harus selalu terpantau agar tidak terjadi kekosongan stok (*stockout*).
3. **Fluktuasi Harga Beli dari Supplier**: Harga beli pasokan sering berubah mengikuti harga komoditas pasar, sehingga memerlukan metode Harga Pokok Penjualan (HPP) yang adaptif dan akurat secara real-time.
4. **Pola Kerja Shift Petugas Kasir**: Operasional kios sering melibatkan beberapa kasir dengan pergantian jam kerja (pagi, siang, malam) di mana rekonsiliasi kas laci kasir wajib dipisahkan per individu guna menegakkan akuntabilitas keuangan.

---

## 2. Lima Batasan Sistem Transparan

Demi menjaga keandalan sistem, integritas pembukuan, dan kesederhanaan penggunaan, Stokify secara sadar menetapkan 5 batasan fungsional berikut:

### Batasan 1: Pembayaran Tunai Saja (*Cash-Only*)
- **Deskripsi**: Transaksi penjualan kasir pada rilis saat ini hanya melayani pembayaran fisik dengan uang tunai (rupiah).
- **Justifikasi Bisnis**: Lebih dari 85% transaksi di kios kelontong tradisional dilakukan secara tunai tanpa biaya transaksi (*merchant fee*). Sistem fokus memastikan nominal pembayaran, kembalian, dan modal kas laci tercatat 100% presisi.
- **Batasan Teknis**: Belum ada integrasi ke payment gateway digital, QRIS dinamis, maupun mesin EDC perbankan.

### Batasan 2: Tanpa Kasbon / Piutang Usaha
- **Deskripsi**: Setiap transaksi penjualan yang diselesaikan berstatus lunas seketika (*immediate cash settlement*).
- **Justifikasi Bisnis**: Fitur kasbon atau piutang langganan sering menjadi celah utama kebocoran kas dan sengketa pencatatan di tingkat kasir. Dengan meniadakan kasbon, seluruh nota yang berstatus `selesai` dijamin memiliki arus kas riil di laci kasir.
- **Batasan Teknis**: Kolom status nota kasir hanya mengenal `selesai` dan `dibatalkan` (void). Tidak ada status `belum lunas` atau modul pelacakan hutang piutang.

### Batasan 3: Satu Lokasi Fisik per Kios Terdaftar (*Single-Location*)
- **Deskripsi**: Setiap kios yang didaftarkan oleh akun pemilik mengelola satu unit toko fisik independen.
- **Justifikasi Bisnis**: Menghindari kompleksitas manajemen multi-gudang dan transfer stok antar-cabang yang membebani pemilik kios mandiri. Pemilik yang memiliki cabang kedua dapat mendaftarkan akun kios terpisah untuk menjaga isolasi pembukuan per unit usaha.
- **Batasan Teknis**: Tidak ada fitur transfer persediaan antar-kios (*inter-warehouse transfer*).

### Batasan 4: Perhitungan Keuangan Berbasis Laba Kotor
- **Deskripsi**: Laporan keuangan resmi hanya menyajikan **Omzet**, **Harga Pokok Penjualan (HPP)**, dan **Laba Kotor** (Omzet - HPP).
- **Aturan Istilah**: Sistem **secara tegas melarang penggunaan istilah "laba bersih"** di seluruh antarmuka dan laporan PDF.
- **Justifikasi Bisnis**: Stokify belum mencatat beban pengeluaran operasional di luar persediaan barang (seperti biaya sewa tempat, tagihan listrik/air, gaji karyawan, dan transportasi). Menyebut keuntungan penjualan sebagai laba bersih adalah kekeliruan akuntansi yang menyesatkan pemilik kios.
- **Catatan Wajib pada Laporan**: *"Laba kotor belum memperhitungkan biaya operasional (sewa, listrik, gaji, dan lain-lain)."*

### Batasan 5: Penguncian Transaksi Setelah Shift Ditutup
- **Deskripsi**: Pembatalan nota penjualan (*void*) hanya diizinkan selama shift kasir yang bersangkutan **masih berstatus berjalan**. Begitu shift ditutup oleh kasir atau ditutup paksa oleh admin, seluruh nota di dalam shift tersebut **terkunci permanen**.
- **Justifikasi Bisnis**: Penutupan shift melibatkan rekonsiliasi fisik kas laci. Jika nota dapat dibatalkan setelah shift ditutup, maka hasil rekonsiliasi uang fisik yang telah disepakati dan dihitung akan menjadi tidak valid dan merusak audit pembukuan kas.

---

## 3. Rencana Pengembangan Lanjutan (Roadmap Fase 2)

Fitur-fitur di bawah ini direncanakan untuk dievaluasi pada iterasi arsitektur berikutnya berdasarkan masukan dari para pemilik kios:

| No | Modul Rencana | Estimasi Nilai Tambah |
|:---|:---|:---|
| 1 | **Pembayaran QRIS Statis / Dinamis** | Memfasilitasi pembeli non-tunai dengan konfirmasi manual kasir atau webhook otomatis. |
| 2 | **Buku Pencatatan Kasbon Terpisah** | Modul pencatatan piutang pelanggan khusus level Admin tanpa mengganggu alur POS kasir. |
| 3 | **Modul Beban Operasional Usaha** | Input biaya operasional (listrik, sewa, gaji) agar sistem dapat menghitung laba bersih operasional. |
| 4 | **Dukungan Multi-Gudang / Cabang** | Fitur konsolidasi multi-cabang dengan transfer stok barang antar-lokasi. |
| 5 | **Aplikasi Mobile Kasir Offline-First** | Sinkronisasi lokal ke awan (*local-first PWA/mobile*) saat koneksi internet kios terputus sementara. |
