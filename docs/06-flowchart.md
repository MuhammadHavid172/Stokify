# 06. Flowchart Alur Proses Bisnis

Dokumen ini memvisualisasikan 7 diagram alur proses (*Flowchart*) utama sistem **Stokify** menggunakan notasi standar Mermaid (`flowchart TD`).

---

## 1. Flowchart Login dan Redirection Pengguna

Menggambarkan alur autentikasi fleksibel (email, username, atau nama kios) beserta pengalihan halaman berdasarkan hak peran (*role*).

```mermaid
flowchart TD
    Start([Mulai]) --> InputLogin[Pengguna Memasukkan Pengenal & Kata Sandi]
    InputLogin --> CekAuth{Kredensial Valid?}

    CekAuth -- Tidak --> ErrorLogin[Tampilkan Pesan Error: Username atau Password Salah]
    ErrorLogin --> EndGagal([Selesai])

    CekAuth -- Ya --> CekAktif{Akun Berstatus Aktif?}
    CekAktif -- Tidak --> ErrorNonaktif[Putus Sesi: Akun Anda Sedang Dinonaktifkan]
    ErrorNonaktif --> EndGagal

    CekAktif -- Ya --> CekRole{Peran Pengguna?}
    
    CekRole -- Admin --> KeDashboard[Arahkan ke Dashboard Admin: /admin/dashboard]
    
    CekRole -- Kasir --> CekShift{Punya Shift Berjalan?}
    CekShift -- Ya --> KeKasir[Arahkan ke Layar POS: /kasir]
    CekShift -- Tidak --> KeBukaShift[Arahkan ke Form Buka Shift: /kasir/shift/buka]

    KeDashboard --> EndSukses([Selesai])
    KeKasir --> EndSukses
    KeBukaShift --> EndSukses
```

---

## 2. Flowchart Transaksi Penjualan Kasir POS

Menggambarkan proses transaksi kasir dengan penguncian baris (`lockForUpdate`), verifikasi stok, pemotongan stok otomatis, dan pencetakan struk.

```mermaid
flowchart TD
    Start([Mulai Transaksi]) --> KasirPilih[Kasir Memilih Barang & Kuantitas]
    KasirPilih --> InputUang[Kasir Menginput Nominal Bayar Tunai]
    InputUang --> CekCukupBayar{Uang Bayar >= Total Belanja?}

    CekCukupBayar -- Tidak --> TolakBayar[Tolak Transaksi: Uang Bayar Kurang]
    TolakBayar --> InputUang

    CekCukupBayar -- Ya --> SubmitTrx[Kirim Transaksi Bersama Token Idempotensi]
    SubmitTrx --> BeginTx[Mulai Database Transaction]
    
    BeginTx --> CekToken{Token Pernah Diproses?}
    CekToken -- Ya --> ReturnExisting[Kembalikan Nota Sebelumnya Tanpa Potong Ulang]
    ReturnExisting --> EndTx[Commit Transaksi]

    CekToken -- Tidak --> LockBarang[Lock Baris Barang: SELECT ... FOR UPDATE]
    LockBarang --> CekStok{Saldo Stok >= Kuantitas Diminta?}

    CekStok -- Tidak --> RollbackStok[Rollback Transaksi: Stok Tidak Mencukupi]
    RollbackStok --> TampilPesanError[Tampilkan Nama Barang & Sisa Stok]
    TampilPesanError --> EndGagal([Batal])

    CekStok -- Ya --> PotongStok[Kurangi Saldo Stok Barang di Tabel barang]
    PotongStok --> AmbilSnapshot[Snapshot Harga Jual & Harga Modal HPP Saat Ini]
    AmbilSnapshot --> SimpanPenjualan[Simpan Header penjualan & detail_penjualan]
    SimpanPenjualan --> CatatMutasi[Catat Rekam mutasi_stok: Tipe penjualan]
    CatatMutasi --> EndTx
    EndTx --> CetakStruk[Tampilkan Pop-up Struk Kasir Siap Cetak]
    CetakStruk --> Selesai([Selesai])
```

---

## 3. Flowchart Pembatalan Transaksi Penjualan (Void)

Menggambarkan aturan bisnis pembatalan nota penjualan yang hanya diizinkan selama shift kasir masih aktif berjalan.

```mermaid
flowchart TD
    Start([Mulai Void]) --> PilihNota[Pengguna Memilih Nota Penjualan]
    PilihNota --> CekStatusNota{Status Nota Saat Ini?}

    CekStatusNota -- Dibatalkan --> TolakSudahBatal[Tolak: Nota Sudah Dibatalkan Sebelumnya]
    TolakSudahBatal --> EndVoid([Batal])

    CekStatusNota -- Selesai --> CekShift{Shift Terkait Masih Berjalan?}
    CekShift -- Tidak Ditutup --> TolakShiftTutup[Tolak: Shift Kasir Telah Ditutup, Nota Terkunci]
    TolakShiftTutup --> EndVoid

    CekShift -- Ya Berjalan --> CekHakAkses{Apakah Kasir Pemilik atau Admin?}
    CekHakAkses -- Tidak --> TolakHakAkses[Tolak: 403 Forbidden]
    TolakHakAkses --> EndVoid

    CekHakAkses -- Ya --> InputAlasan[Input Alasan Pembatalan: Min 5 Karakter]
    InputAlasan --> ValidasiAlasan{Alasan Valid?}
    ValidasiAlasan -- Tidak --> MintaAlasan[Tampilkan Pesan Validasi Alasan Wajib Diisi]
    MintaAlasan --> InputAlasan

    ValidasiAlasan -- Ya --> BeginVoid[Mulai Database Transaction]
    BeginVoid --> UpdateStatus[Ubah Status Nota Menjadi dibatalkan]
    UpdateStatus --> BalikkanStok[Tambah Kembali Kuantitas ke Saldo Stok Barang]
    BalikkanStok --> CatatMutasiVoid[Catat mutasi_stok: Tipe void_penjualan]
    CatatMutasiVoid --> CatatLog[Catat log_aktivitas: penjualan.void]
    CatatLog --> CommitVoid[Commit Transaksi]
    CommitVoid --> SuksesVoid[Tampilkan Flash Sukses: Stok Telah Dipulihkan]
    SuksesVoid --> EndVoidSukses([Selesai])
```

---

## 4. Flowchart Rekonsiliasi Kas dan Tutup Shift

Menggambarkan proses tutup shift dengan audit hitung buta uang fisik laci kasir.

```mermaid
flowchart TD
    Start([Mulai Tutup Shift]) --> BukaLaci[Kasir Membuka Laci Kasir]
    BukaLaci --> HitungButa[Kasir Menghitung Fisik Uang Tanpa Melihat Angka Sistem]
    HitungButa --> InputFisik[Kasir Menginput Nominal Uang Fisik]

    InputFisik --> HitungSistem[Sistem Mengkalkulasi: Modal Awal + Total Nota Selesai]
    HitungSistem --> HitungSelisih[Hitung Selisih: Uang Fisik - Uang Seharusnya]

    HitungSelisih --> CekSelisih{Apakah Ada Selisih Uang?}
    
    CekSelisih -- Ya Selisih != 0 --> CekCatatan{Kasir Mengisi Catatan Penjelasan?}
    CekCatatan -- Belum --> WajibCatatan[Wajib Mengisi Formulir Catatan Alasan Selisih]
    WajibCatatan --> InputFisik

    CekCatatan -- Sudah --> SimpanShift[Simpan Status ditutup & Catatan Selisih]
    CekSelisih -- Pas Selisih == 0 --> SimpanShift

    SimpanShift --> KunciNota[Kunci Seluruh Nota Penjualan pada Shift Ini]
    KunciNota --> CatatLogShift[Catat log_aktivitas: shift.tutup]
    CatatLogShift --> TampilRekap[Tampilkan Halaman Rekap Shift & Ringkasan Kas]
    TampilRekap --> SelesaiShift([Selesai])
```

---

## 5. Flowchart Penerimaan Barang Masuk & Moving Average HPP

Menggambarkan penerimaan pasokan supplier dan rekalkulasi otomatis HPP persediaan.

```mermaid
flowchart TD
    Start([Mulai Penerimaan]) --> AdminInput[Admin Memilih Supplier & Input Item Barang]
    AdminInput --> InputHargaJumlah[Input Kuantitas Masuk & Harga Beli Satuan]
    InputHargaJumlah --> SimpanFaktur[Admin Menekan Simpan Barang Masuk]

    SimpanFaktur --> BeginBM[Mulai Database Transaction]
    BeginBM --> LoopItem[Iterasi Setiap Baris Barang]

    LoopItem --> AmbilDataLama[Ambil Saldo Stok Lama & HPP Modal Lama]
    AmbilDataLama --> HitungHPPBaru[Hitung Moving Average: StokLama*HPPLama + Masuk*HargaBeli / StokTotal]
    HitungHPPBaru --> UpdateBarang[Update tabel barang: Tambah Stok & Set Harga Modal Baru]
    UpdateBarang --> CatatMutasiBM[Catat mutasi_stok: Tipe barang_masuk]
    CatatMutasiBM --> CekAdaItemLain{Masih Ada Item Berikutnya?}

    CekAdaItemLain -- Ya --> LoopItem
    CekAdaItemLain -- Tidak --> SimpanHeaderBM[Simpan Header barang_masuk & detail_barang_masuk]
    SimpanHeaderBM --> CatatLogBM[Catat log_aktivitas: barang_masuk.buat]
    CatatLogBM --> CommitBM[Commit Transaksi]
    CommitBM --> SelesaiBM([Selesai])
```

---

## 6. Flowchart Penyesuaian Stok & Stok Opname

Menggambarkan alur audit fisik stok dan penyesuaian kerugian persediaan.

```mermaid
flowchart TD
    Start([Mulai Audit Stok]) --> PilihMode{Pilih Mode Penyesuaian?}
    
    PilihMode -- Manual --> InputItemManual[Admin Pilih Barang, Jenis Rusak/Hilang, & Jumlah]
    PilihMode -- Opname --> InputFisikSemua[Admin Input Stok Fisik Seluruh Barang di Rak]

    InputFisikSemua --> BandingkanSistem[Sistem Bandingkan: Stok Fisik vs Stok Sistem]
    BandingkanSistem --> FilterSelisih[Ambil Hanya Barang yang Memiliki Selisih]
    FilterSelisih --> CekAdaSelisih{Ditemukan Selisih?}
    CekAdaSelisih -- Tidak --> TolakOpname[Tolak: Semua Stok Fisik Telah Cocok dengan Sistem]
    TolakOpname --> Batal([Batal])

    CekAdaSelisih -- Ya --> InputAlasanOpname[Admin Mengisi Keterangan Alasan Selisih]
    InputItemManual --> EksekusiPenyesuaian[Eksekusi Database Transaction]
    InputAlasanOpname --> EksekusiPenyesuaian

    EksekusiPenyesuaian --> UpdateStokFisik[Koreksi Saldo Stok Barang Menjadi Nilai Fisik]
    UpdateStokFisik --> HitungNilaiKerugian[Hitung Kerugian: Jumlah Selisih * Harga Modal HPP]
    HitungNilaiKerugian --> CatatMutasiAdj[Catat mutasi_stok: Tipe penyesuaian]
    CatatMutasiAdj --> SimpanHeaderAdj[Simpan penyesuaian_stok & detail_penyesuaian_stok]
    SimpanHeaderAdj --> SelesaiAdj([Selesai])
```

---

## 7. Flowchart Rekomendasi Restok (Pareto & ROP)

Menggambarkan mesin analitik persediaan barang dan saran pengadaan.

```mermaid
flowchart TD
    Start([Mulai Analisis]) --> AdminPilihPeriode[Admin Memilih Periode Hari: 7, 14, 30, 60, 90]
    AdminPilihPeriode --> AmbilDataTrx[Ambil Seluruh detail_penjualan Nota Selesai pada Periode]
    
    AmbilDataTrx --> HitungPerBarang[Hitung Total Kuantitas Terjual Netto & Omzet Per Barang]
    HitungPerBarang --> UrutkanOmzet[Urutkan Barang Berdasarkan Omzet Tertinggi ke Terendah]
    
    UrutkanOmzet --> HitungKumulatif[Kalkulasi Persentase Kontribusi Omzet Kumulatif]
    HitungKumulatif --> KlasifikasiPareto{Persentase Kumulatif?}
    
    KlasifikasiPareto -- <= 80% --> KategoriA[Kategori A: Fast Moving Produk Utama]
    KlasifikasiPareto -- 80% s/d 95% --> KategoriB[Kategori B: Medium Moving]
    KlasifikasiPareto -- > 95% --> KategoriC[Kategori C: Slow Moving]

    KategoriA --> HitungROP[Kalkulasi Titik Pesan Ulang ROP: PenjualanHarian * LeadTime + SafetyStock]
    KategoriB --> HitungROP
    KategoriC --> HitungROP

    HitungROP --> BandingkanStok{Saldo Stok Saat Ini <= ROP?}
    
    BandingkanStok -- Ya --> RekomendasiBeli[Status: Perlu Restok -> Hitung Kuantitas Belanja]
    BandingkanStok -- Tidak --> StokAman[Status: Stok Cukup]

    RekomendasiBeli --> TampilkanTabel[Tampilkan Tabel Analisis & Unduh Dokumen PDF]
    StokAman --> TampilkanTabel
    TampilkanTabel --> SelesaiROP([Selesai])
```
