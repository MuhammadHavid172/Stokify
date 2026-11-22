# 07. Rumus Matematis dan Algoritma Bisnis

Dokumen ini mendokumentasikan seluruh formula perhitungan matematis dan algoritma bisnis yang diimplementasikan pada sistem **Stokify**, dilengkapi dengan contoh studi kasus nyata perhitungan per langkah.

---

## 1. Harga Pokok Penjualan (HPP) Rata-Rata Bergerak (*Moving Average*)

### Formula Matematis

Ketika ada pasokan barang masuk baru yang diterima, HPP (`harga_modal`) pada master barang diperbarui secara otomatis menggunakan metode rata-rata tertimbang (*weighted moving average*):

$$\text{HPP}_{\text{baru}} = \text{round}\left( \frac{(\text{Stok}_{\text{lama}} \times \text{HPP}_{\text{lama}}) + (\text{Qty}_{\text{masuk}} \times \text{HargaBeli}_{\text{baru}})}{\text{Stok}_{\text{lama}} + \text{Qty}_{\text{masuk}}} \right)$$

*Keterangan Khusus:*
- Jika saldo $\text{Stok}_{\text{lama}} \le 0$ saat barang masuk dicatat, maka $\text{HPP}_{\text{baru}} = \text{HargaBeli}_{\text{baru}}$.
- Hasil dibulatkan ke bilangan bulat terdekat (*integer rounding*) untuk menghindari angka pecahan desimal pada rupiah.

### Contoh Perhitungan Nyata

1. **Kondisi Awal**:
   - Barang: Beras Ramos 5 kg
   - $\text{Stok}_{\text{lama}} = 20\text{ sak}$
   - $\text{HPP}_{\text{lama}} = \text{Rp } 60.000$ per sak
   - Nilai aset awal $= 20 \times 60.000 = \text{Rp } 1.200.000$

2. **Transaksi Barang Masuk**:
   - Membeli pasokan tambahan dari supplier sebanyak $\text{Qty}_{\text{masuk}} = 30\text{ sak}$.
   - Harga beli dari supplier mengalami kenaikan: $\text{HargaBeli}_{\text{baru}} = \text{Rp } 65.000$ per sak.
   - Nilai pasokan baru $= 30 \times 65.000 = \text{Rp } 1.950.000$

3. **Kalkulasi HPP Baru**:
   $$\text{Total Nilai Persediaan} = \text{Rp } 1.200.000 + \text{Rp } 1.950.000 = \text{Rp } 3.150.000$$
   $$\text{Total Kuantitas Persediaan} = 20 + 30 = 50\text{ sak}$$
   $$\text{HPP}_{\text{baru}} = \frac{3.150.000}{50} = \text{Rp } 63.000$$

Maka nilai `harga_modal` Beras Ramos di database otomatis berubah dari Rp 60.000 menjadi **Rp 63.000**. Transaksi penjualan berikutnya akan menggunakan Rp 63.000 sebagai snapshot HPP.

---

## 2. Omzet, HPP Transaksi, dan Laba Kotor

### Formula Matematis

Perhitungan performa keuangan hanya diambil dari transaksi penjualan berstatus `selesai` (nota dibatalkan/void **TIDAK** dihitung):

$$\text{Omzet} = \sum (\text{penjualan.total})$$

$$\text{HPP Snapshot} = \sum (\text{penjualan.total\_modal}) = \sum (\text{detail\_penjualan.subtotal\_modal})$$

$$\text{Laba Kotor} = \text{Omzet} - \text{HPP Snapshot}$$

$$\text{Margin Laba Kotor (\%)} = \left( \frac{\text{Laba Kotor}}{\text{Omzet}} \right) \times 100\%$$

*Prinsip Integritas:*
Nilai laba kotor dihitung dari snapshot harga modal saat transaksi terjadi (`detail_penjualan.harga_modal`), bukan dari harga modal barang saat ini. Dengan demikian, perubahan harga barang di kemudian hari tidak akan pernah mengubah laporan laba kotor transaksi masa lalu.

---

## 3. Rekonsiliasi Kas dan Selisih Laci Shift Kasir

### Formula Matematis

Uang kas yang seharusnya ada di laci saat penutupan shift dihitung dari saldo kas awal ditambah seluruh nota tunai yang selesai:

$$\text{Uang Seharusnya} = \text{Modal Awal} + \sum (\text{penjualan.total}_{\text{selesai, tunai}})$$

$$\text{Selisih Kas} = \text{Uang Fisik} - \text{Uang Seharusnya}$$

### Evaluasi Selisih:
- **$\text{Selisih} = 0$ (Pas / Seimbang)**: Uang di laci tepat sesuai dengan transaksi sistem.
- **$\text{Selisih} > 0$ (Lebih Kas)**: Terdapat kelebihan uang di laci kasir (misal: pembeli tidak mengambil receh kembalian).
- **$\text{Selisih} < 0$ (Kurang Kas)**: Terjadi kekurangan fisik uang di laci (kasir wajib memberikan catatan rekonsiliasi).

### Contoh Perhitungan Nyata

- Kasir Siti membuka shift dengan $\text{Modal Awal} = \text{Rp } 150.000$.
- Sepanjang shift, terdapat 12 nota selesai senilai total Rp 850.000 dan 1 nota dibatalkan (void) senilai Rp 50.000.
- $\text{Uang Seharusnya} = 150.000 + 850.000 = \text{Rp } 1.000.000$ (nota batal Rp 50.000 tidak dihitung).
- Saat jam shift berakhir, kasir Siti menghitung buta fisik uang di laci: $\text{Uang Fisik} = \text{Rp } 1.005.000$.
- $\text{Selisih} = 1.005.000 - 1.000.000 = +\text{Rp } 5.000$ (status: *Lebih Kas Rp 5.000*).

---

## 4. Rekonsiliasi Buku Besar Mutasi Stok

Setiap baris pada tabel `mutasi_stok` tunduk pada persamaan matematis tertutup (*closed-loop ledger equation*):

$$\text{Stok}_{\text{sesudah}} = \text{Stok}_{\text{sebelum}} + \text{Jumlah}$$

- Untuk transaksi masuk (`barang_masuk`, `stok_awal`, `void_penjualan`), $\text{Jumlah} > 0$.
- Untuk transaksi keluar (`penjualan`, penyesuaian berkurang), $\text{Jumlah} < 0$.

Konsistensi ini diaudit secara otomatis oleh perintah `php artisan stokify:cek-integritas`.

---

## 5. Klasifikasi Analisis Pareto (ABC Analysis)

Sistem mengklasifikasikan performa barang dagangan ke dalam 3 kelompok berdasarkan kontribusi kumulatif omzet terhadap total penjualan kios pada periode yang dipilih:

1. **Kelompok A (*Fast Moving* - Produk Kunci)**:
   - Produk-produk dengan kontribusi omzet kumulatif dari **0% sampai 80%**.
   - Merupakan barang yang paling laku dan paling banyak menyumbang perputaran kas kios.
2. **Kelompok B (*Medium Moving* - Produk Penopang)**:
   - Produk-produk dengan kontribusi omzet kumulatif dari **80% sampai 95%**.
3. **Kelompok C (*Slow Moving* - Produk Lambat)**:
   - Produk-produk dengan kontribusi omzet kumulatif dari **95% sampai 100%**.
   - Perputaran lambat, disarankan untuk membatasi kuantitas restok agar dana usaha tidak mengendap mati.

---

## 6. Algoritma Titik Pesan Ulang (*Reorder Point* / ROP) dan Saran Belanja

### Komponen Parameter Kios
Setiap kios dapat mengonfigurasi parameter operasional mandiri pada menu Analisis:
- $L$ (*Lead Time*): Waktu tunggu pasokan tiba sejak dipesan (hari, default = 2 hari).
- $S$ (*Hari Pengaman*): Target hari persediaan pengaman / *safety stock* (hari, default = 3 hari).
- $C$ (*Cakupan Belanja*): Target jumlah hari penjualan yang ingin dipenuhi dalam sekali restok (hari, default = 7 hari).

### Formula ROP dan Safety Stock

1. **Rata-Rata Penjualan Harian ($D$)**:
   $$D = \frac{\text{Total Kuantitas Terjual Netto pada Periode Analisis}}{\text{Jumlah Hari Periode (misal: 30 hari)}}$$

2. **Persediaan Pengaman (*Safety Stock* / $SS$)**:
   $$SS = \text{ceil}(D \times S)$$

3. **Titik Pesan Ulang (*Reorder Point* / ROP)**:
   $$\text{ROP} = \text{ceil}((D \times L) + SS)$$

### Kriteria Status Rekomendasi Restok
Sistem membandingkan saldo persediaan saat ini ($\text{Stok}_{\text{saat\_ini}}$) terhadap ROP:
- Jika $\text{Stok}_{\text{saat\_ini}} \le \text{ROP}$: **Perlu Restok Segera**
- Jika $\text{Stok}_{\text{saat\_ini}} > \text{ROP}$: **Stok Masuk Kategori Cukup**

### Kuantitas Restok yang Disarankan ($Q$)
Jika barang berstatus perlu restok, sistem menghitung kuantitas belanja ideal agar persediaan mencukupi target hari belanja ($C$):

$$\text{Target Persediaan} = \text{ceil}(D \times C) + SS$$

$$Q = \max(0, \text{Target Persediaan} - \text{Stok}_{\text{saat\_ini}})$$

### Contoh Perhitungan ROP Nyata

- Barang: Minyak Goreng Kita 1 Liter
- Periode analisis: 30 hari terakhir.
- Terjual netto: 150 pouch.
- $D = \frac{150}{30} = 5\text{ pouch per hari}$.
- Parameter Kios: Lead Time $L = 2\text{ hari}$, Hari Pengaman $S = 3\text{ hari}$, Cakupan Belanja $C = 7\text{ hari}$.
- $SS = 5 \times 3 = 15\text{ pouch}$.
- $\text{ROP} = (5 \times 2) + 15 = 10 + 15 = \mathbf{25\text{ pouch}}$.
- Kondisi saat ini: Stok di rak tinggal **8 pouch**.
- Evaluasi: $8 \le 25 \rightarrow$ **PERLU RESTOK SEGERA**.
- Target Persediaan $= (5 \times 7) + 15 = 35 + 15 = 50\text{ pouch}$.
- Saran Pembelian $Q = 50 - 8 = \mathbf{42\text{ pouch}}$.
