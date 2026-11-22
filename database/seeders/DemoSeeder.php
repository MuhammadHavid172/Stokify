<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\LogAktivitas;
use App\Models\Supplier;
use App\Models\User;
use App\Services\BarangMasukService;
use App\Services\PenjualanService;
use App\Services\PenyesuaianStokService;
use App\Services\ShiftService;
use App\Services\StokService;
use App\Services\VoidService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds for demo & testing.
     */
    public function run(): void
    {
        $stokService = app(StokService::class);
        $barangMasukService = app(BarangMasukService::class);
        $penyesuaianService = app(PenyesuaianStokService::class);
        $penjualanService = app(PenjualanService::class);
        $shiftService = app(ShiftService::class);
        $voidService = app(VoidService::class);

        // ==========================================
        // 1. Kios Maju (Data Demo Kios A)
        // ==========================================
        $kiosMaju = Kios::create([
            'nama_kios' => 'Kios Maju',
            'alamat' => 'Jl. Merdeka No. 10, Jayapura',
            'no_telepon' => '081234567890',
            'status' => 'aktif',
        ]);

        $adminMaju = User::create([
            'kios_id' => $kiosMaju->id,
            'name' => 'Admin Maju',
            'username' => 'admin_maju',
            'email' => 'admin_maju@kios.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $kasirMaju = User::create([
            'kios_id' => $kiosMaju->id,
            'name' => 'Kasir Maju',
            'username' => 'kasir_maju',
            'email' => null,
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // Kategori Kios Maju
        $katMajuSembako = Kategori::create([
            'kios_id' => $kiosMaju->id,
            'nama' => 'Sembako & Beras',
        ]);
        $katMajuMakanan = Kategori::create([
            'kios_id' => $kiosMaju->id,
            'nama' => 'Makanan & Minuman',
        ]);
        $katMajuHarian = Kategori::create([
            'kios_id' => $kiosMaju->id,
            'nama' => 'Kebutuhan Harian & Rokok',
        ]);

        // Barang Kios Maju (12 item realistis)
        $barangMajuItems = [
            ['kode' => 'BRG-0001', 'nama' => 'Beras Premium Ramos 5kg', 'kategori_id' => $katMajuSembako->id, 'satuan' => 'karung', 'harga_modal' => 68000, 'harga_jual' => 75000, 'stok_awal' => 25, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0002', 'nama' => 'Minyak Goreng Sawit 1L', 'kategori_id' => $katMajuSembako->id, 'satuan' => 'botol', 'harga_modal' => 15500, 'harga_jual' => 18000, 'stok_awal' => 30, 'stok_min' => 10, 'aktif' => true],
            ['kode' => 'BRG-0003', 'nama' => 'Gula Pasir Putih 1kg', 'kategori_id' => $katMajuSembako->id, 'satuan' => 'kg', 'harga_modal' => 14000, 'harga_jual' => 16500, 'stok_awal' => 20, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0004', 'nama' => 'Telur Ayam Negeri 1kg', 'kategori_id' => $katMajuSembako->id, 'satuan' => 'kg', 'harga_modal' => 26000, 'harga_jual' => 28000, 'stok_awal' => 15, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0005', 'nama' => 'Tepung Terigu Segitiga Biru 1kg', 'kategori_id' => $katMajuSembako->id, 'satuan' => 'bungkus', 'harga_modal' => 10500, 'harga_jual' => 12500, 'stok_awal' => 3, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0006', 'nama' => 'Indomie Goreng Spesial 85g', 'kategori_id' => $katMajuMakanan->id, 'satuan' => 'bungkus', 'harga_modal' => 2800, 'harga_jual' => 3500, 'stok_awal' => 80, 'stok_min' => 20, 'aktif' => true],
            ['kode' => 'BRG-0007', 'nama' => 'Indomie Kuah Ayam Bawang 75g', 'kategori_id' => $katMajuMakanan->id, 'satuan' => 'bungkus', 'harga_modal' => 2700, 'harga_jual' => 3500, 'stok_awal' => 0, 'stok_min' => 15, 'aktif' => true],
            ['kode' => 'BRG-0008', 'nama' => 'Air Mineral Botol 600ml', 'kategori_id' => $katMajuMakanan->id, 'satuan' => 'botol', 'harga_modal' => 2500, 'harga_jual' => 4000, 'stok_awal' => 48, 'stok_min' => 12, 'aktif' => true],
            ['kode' => 'BRG-0009', 'nama' => 'Teh Botol Sosro Kotak 250ml', 'kategori_id' => $katMajuMakanan->id, 'satuan' => 'kotak', 'harga_modal' => 3000, 'harga_jual' => 4500, 'stok_awal' => 2, 'stok_min' => 10, 'aktif' => true],
            ['kode' => 'BRG-0010', 'nama' => 'Kopi Kapal Api Sachet 24g', 'kategori_id' => $katMajuMakanan->id, 'satuan' => 'sachet', 'harga_modal' => 1200, 'harga_jual' => 2000, 'stok_awal' => 60, 'stok_min' => 15, 'aktif' => true],
            ['kode' => 'BRG-0011', 'nama' => 'Rokok Sampoerna Mild 16', 'kategori_id' => $katMajuHarian->id, 'satuan' => 'bungkus', 'harga_modal' => 29000, 'harga_jual' => 33000, 'stok_awal' => 20, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0012', 'nama' => 'Sabun Cuci Piring Sunlight 210ml', 'kategori_id' => $katMajuHarian->id, 'satuan' => 'botol', 'harga_modal' => 4500, 'harga_jual' => 6000, 'stok_awal' => 10, 'stok_min' => 5, 'aktif' => false],
            ['kode' => 'BRG-0013', 'nama' => 'Garam Dapur Beriodium 250g', 'kategori_id' => $katMajuSembako->id, 'satuan' => 'bungkus', 'harga_modal' => 2000, 'harga_jual' => 3000, 'stok_awal' => 150, 'stok_min' => 10, 'aktif' => true],
            ['kode' => 'BRG-0014', 'nama' => 'Saus Sambal Ekstra Pedas 135ml', 'kategori_id' => $katMajuMakanan->id, 'satuan' => 'botol', 'harga_modal' => 6000, 'harga_jual' => 8000, 'stok_awal' => 15, 'stok_min' => 5, 'aktif' => true],
        ];

        Auth::login($adminMaju);
        $savedBarangMaju = [];
        foreach ($barangMajuItems as $item) {
            $barang = Barang::create([
                'kios_id' => $kiosMaju->id,
                'kategori_id' => $item['kategori_id'],
                'kode' => $item['kode'],
                'nama' => $item['nama'],
                'satuan' => $item['satuan'],
                'harga_modal' => $item['harga_modal'],
                'harga_jual' => $item['harga_jual'],
                'stok' => 0,
                'stok_minimum' => $item['stok_min'],
                'is_aktif' => $item['aktif'],
                'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
                'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            ]);

            if ($item['stok_awal'] > 0) {
                $stokService->catat($barang, 'stok_awal', $item['stok_awal'], 'Stok awal barang');
            }

            LogAktivitas::catat('barang.tambah', "Menambahkan barang awal {$barang->nama} ({$barang->kode})");
            $savedBarangMaju[$item['kode']] = $barang;
        }

        $barangMaju15 = Barang::create([
            'kios_id' => $kiosMaju->id,
            'kategori_id' => $katMajuMakanan->id,
            'kode' => 'BRG-0015',
            'nama' => 'Keripik Singkong Balado 150g',
            'satuan' => 'bungkus',
            'harga_modal' => 8000,
            'harga_jual' => 10000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(3),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(3),
        ]);
        $stokService->catat($barangMaju15, 'stok_awal', 4, 'Stok awal pengenalan produk baru');
        $savedBarangMaju['BRG-0015'] = $barangMaju15;

        // Supplier Kios Maju
        $supMaju1 = Supplier::create([
            'kios_id' => $kiosMaju->id,
            'nama' => 'CV Sumber Berkah Jaya',
            'no_telepon' => '081234567801',
            'alamat' => 'Jl. Pasar Sentral No. 12, Jayapura',
            'is_aktif' => true,
        ]);
        $supMaju2 = Supplier::create([
            'kios_id' => $kiosMaju->id,
            'nama' => 'Distributor Sembako Nusantara',
            'no_telepon' => '081234567802',
            'alamat' => 'Kompleks Pergudangan Blok B3, Jayapura',
            'is_aktif' => true,
        ]);
        $supMaju3 = Supplier::create([
            'kios_id' => $kiosMaju->id,
            'nama' => 'Agen Minuman Segar Abadi',
            'no_telepon' => '081234567803',
            'alamat' => 'Jl. Sam Ratulangi No. 8, Jayapura',
            'is_aktif' => true,
        ]);

        // Transaksi Barang Masuk Kios Maju
        $barangMasukService->simpan([
            'tanggal' => Carbon::yesterday()->toDateString(),
            'supplier_id' => $supMaju1->id,
            'catatan' => 'Barang masuk beras & minyak goreng stok mingguan',
            'items' => [
                ['barang_id' => $savedBarangMaju['BRG-0001']->id, 'jumlah' => 10, 'harga_beli' => 67000],
                ['barang_id' => $savedBarangMaju['BRG-0002']->id, 'jumlah' => 20, 'harga_beli' => 15000],
            ],
        ], $adminMaju);

        $barangMasukService->simpan([
            'tanggal' => Carbon::today()->toDateString(),
            'supplier_id' => $supMaju3->id,
            'catatan' => 'Restock minuman kemasan',
            'items' => [
                ['barang_id' => $savedBarangMaju['BRG-0008']->id, 'jumlah' => 24, 'harga_beli' => 2400],
                ['barang_id' => $savedBarangMaju['BRG-0009']->id, 'jumlah' => 20, 'harga_beli' => 2900],
            ],
        ], $adminMaju);

        $notaMajuBatal = $barangMasukService->simpan([
            'tanggal' => Carbon::today()->toDateString(),
            'supplier_id' => null,
            'catatan' => 'Barang masuk mie instan pasar grosir (salah entri)',
            'items' => [
                ['barang_id' => $savedBarangMaju['BRG-0006']->id, 'jumlah' => 40, 'harga_beli' => 2750],
            ],
        ], $adminMaju);
        $barangMasukService->batalkan($notaMajuBatal, 'Salah input jumlah item barang masuk saat penerimaan barang', $adminMaju);

        // Penyesuaian Stok & Opname Kios Maju
        $penyesuaianService->simpanManual([
            'tanggal' => Carbon::today()->toDateString(),
            'catatan' => 'Pembersihan dan pengecekan rak display pagi',
            'items' => [
                [
                    'barang_id' => $savedBarangMaju['BRG-0004']->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 2,
                    'alasan' => 'Pecah saat pemindahan krat di rak display',
                ],
            ],
        ], $adminMaju);

        $penyesuaianService->simpanManual([
            'tanggal' => Carbon::today()->toDateString(),
            'catatan' => 'Audit stok masa berlaku produk',
            'items' => [
                [
                    'barang_id' => $savedBarangMaju['BRG-0006']->id,
                    'jenis' => 'kedaluwarsa',
                    'arah' => 'kurang',
                    'jumlah' => 4,
                    'alasan' => 'Kemasan bocor dan melewati batas konsumsi',
                ],
            ],
        ], $adminMaju);

        $bMaju2 = $savedBarangMaju['BRG-0002']->fresh();
        $bMaju3 = $savedBarangMaju['BRG-0003']->fresh();
        $penyesuaianService->simpanOpname([
            'tanggal' => Carbon::today()->toDateString(),
            'catatan' => 'Stok opname mingguan rak sembako',
            'items' => [
                [
                    'barang_id' => $bMaju2->id,
                    'stok_sistem' => $bMaju2->stok,
                    'stok_fisik' => $bMaju2->stok - 1,
                    'alasan' => 'Selisih stok opname berkala rak minyak',
                ],
                [
                    'barang_id' => $bMaju3->id,
                    'stok_sistem' => $bMaju3->stok,
                    'stok_fisik' => $bMaju3->stok + 2,
                    'alasan' => 'Kelebihan fisik saat penghitungan ulang rak sembako',
                ],
            ],
        ], $adminMaju);

        // Shift & Penjualan Kios Maju
        $waktuBukaMaju1 = Carbon::now('Asia/Jayapura')->subDays(5)->setHour(8)->setMinute(0);
        $shiftMaju1 = $shiftService->buka($kasirMaju, 100000, $waktuBukaMaju1);

        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0006']->id, 'jumlah' => 5]],
            bayar: 20000,
            token: 'seed_maju_s1_1',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju1)->addHours(2)
        );
        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0001']->id, 'jumlah' => 1], ['barang_id' => $savedBarangMaju['BRG-0002']->id, 'jumlah' => 1]],
            bayar: 110000,
            token: 'seed_maju_s1_2',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju1)->addHours(4)
        );
        $penjualanService->buat(
            items: [
                ['barang_id' => $savedBarangMaju['BRG-0010']->id, 'jumlah' => 5],
                ['barang_id' => $savedBarangMaju['BRG-0013']->id, 'jumlah' => 1],
            ],
            bayar: 20000,
            token: 'seed_maju_s1_3',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju1)->addHours(6)
        );

        $totalTunaiMaju1 = (int) $shiftMaju1->penjualan()->where('status', 'selesai')->sum('total');
        $shiftService->tutup(
            shift: $shiftMaju1,
            uangFisik: $shiftMaju1->modal_awal + $totalTunaiMaju1,
            catatan: null,
            penutup: $kasirMaju,
            paksa: false,
            waktuTutup: (clone $waktuBukaMaju1)->setHour(17)->setMinute(0)
        );

        $waktuBukaMaju2 = Carbon::now('Asia/Jayapura')->subDays(3)->setHour(8)->setMinute(30);
        $shiftMaju2 = $shiftService->buka($kasirMaju, 150000, $waktuBukaMaju2);

        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0011']->id, 'jumlah' => 2], ['barang_id' => $savedBarangMaju['BRG-0010']->id, 'jumlah' => 2]],
            bayar: 100000,
            token: 'seed_maju_s2_1',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju2)->addHours(2)
        );
        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0004']->id, 'jumlah' => 1], ['barang_id' => $savedBarangMaju['BRG-0003']->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'seed_maju_s2_2',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju2)->addHours(4)
        );
        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0008']->id, 'jumlah' => 3]],
            bayar: 50000,
            token: 'seed_maju_s2_3',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju2)->addHours(6)
        );

        $totalTunaiMaju2 = (int) $shiftMaju2->penjualan()->where('status', 'selesai')->sum('total');
        $shiftService->tutup(
            shift: $shiftMaju2,
            uangFisik: $shiftMaju2->modal_awal + $totalTunaiMaju2 - 10000,
            catatan: 'Uang fisik di laci kurang Rp 10.000 karena selisih uang receh kembalian kasir.',
            penutup: $kasirMaju,
            paksa: false,
            waktuTutup: (clone $waktuBukaMaju2)->setHour(18)->setMinute(0)
        );

        $waktuBukaMaju3 = Carbon::now('Asia/Jayapura')->subDays(1)->setHour(8)->setMinute(0);
        $shiftMaju3 = $shiftService->buka($kasirMaju, 200000, $waktuBukaMaju3);

        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0002']->id, 'jumlah' => 2]],
            bayar: 80000,
            token: 'seed_maju_s3_1',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju3)->addHours(1)
        );
        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0006']->id, 'jumlah' => 5], ['barang_id' => $savedBarangMaju['BRG-0004']->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'seed_maju_s3_2',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju3)->addHours(2)
        );
        $notaVoidMaju = $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0003']->id, 'jumlah' => 2]],
            bayar: 40000,
            token: 'seed_maju_s3_void',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju3)->addHours(3)
        );
        $penjualanService->batalkan(
            penjualan: $notaVoidMaju,
            alasan: 'Pelanggan membatalkan belanja gula karena salah membawa uang tunai.',
            user: $kasirMaju
        );

        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0001']->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'seed_maju_s3_3',
            user: $kasirMaju,
            waktuTransaksi: (clone $waktuBukaMaju3)->addHours(5)
        );

        $totalTunaiMaju3 = (int) $shiftMaju3->penjualan()->where('status', 'selesai')->sum('total');
        $shiftService->tutup(
            shift: $shiftMaju3,
            uangFisik: $shiftMaju3->modal_awal + $totalTunaiMaju3 + 5000,
            catatan: 'Uang fisik lebih Rp 5.000 dari pembulatan pecahan kecil belanja pelanggan.',
            penutup: $kasirMaju,
            paksa: false,
            waktuTutup: (clone $waktuBukaMaju3)->setHour(19)->setMinute(0)
        );

        $waktuBukaMaju4 = Carbon::now('Asia/Jayapura')->setHour(8)->setMinute(0);
        $shiftMaju4 = $shiftService->buka($adminMaju, 100000, $waktuBukaMaju4);

        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0001']->id, 'jumlah' => 1], ['barang_id' => $savedBarangMaju['BRG-0004']->id, 'jumlah' => 1]],
            bayar: 110000,
            token: 'seed_maju_s4_1',
            user: $adminMaju,
            waktuTransaksi: (clone $waktuBukaMaju4)->addHours(1)
        );
        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0002']->id, 'jumlah' => 1], ['barang_id' => $savedBarangMaju['BRG-0006']->id, 'jumlah' => 5]],
            bayar: 50000,
            token: 'seed_maju_s4_2',
            user: $adminMaju,
            waktuTransaksi: (clone $waktuBukaMaju4)->addHours(2)
        );
        $penjualanService->buat(
            items: [['barang_id' => $savedBarangMaju['BRG-0010']->id, 'jumlah' => 5], ['barang_id' => $savedBarangMaju['BRG-0006']->id, 'jumlah' => 2]],
            bayar: 20000,
            token: 'seed_maju_s4_3',
            user: $adminMaju,
            waktuTransaksi: (clone $waktuBukaMaju4)->addHours(3)
        );

        $totalTunaiMaju4 = (int) $shiftMaju4->penjualan()->where('status', 'selesai')->sum('total');
        $shiftService->tutup(
            shift: $shiftMaju4,
            uangFisik: $shiftMaju4->modal_awal + $totalTunaiMaju4,
            catatan: null,
            penutup: $adminMaju,
            paksa: false,
            waktuTutup: (clone $waktuBukaMaju4)->setHour(12)->setMinute(30)
        );

        Auth::logout();

        // ==========================================
        // 2. Kios Sejahtera (Data Demo Kios B)
        // ==========================================
        $kiosSejahtera = Kios::create([
            'nama_kios' => 'Kios Sejahtera',
            'alamat' => 'Jl. Ahmad Yani No. 45, Jayapura',
            'no_telepon' => '089876543210',
            'status' => 'aktif',
        ]);

        $adminSejahtera = User::create([
            'kios_id' => $kiosSejahtera->id,
            'name' => 'Admin Sejahtera',
            'username' => 'admin_sejahtera',
            'email' => 'admin_sejahtera@kios.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $kasirSejahtera = User::create([
            'kios_id' => $kiosSejahtera->id,
            'name' => 'Kasir Sejahtera',
            'username' => 'kasir_sejahtera',
            'email' => null,
            'password' => Hash::make('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        $katSejahteraSembako = Kategori::create([
            'kios_id' => $kiosSejahtera->id,
            'nama' => 'Sembako Utama',
        ]);
        $katSejahteraMinuman = Kategori::create([
            'kios_id' => $kiosSejahtera->id,
            'nama' => 'Minuman Kemasan',
        ]);
        $katSejahteraSnack = Kategori::create([
            'kios_id' => $kiosSejahtera->id,
            'nama' => 'Snack & Biskuit',
        ]);

        $barangSejahteraItems = [
            ['kode' => 'BRG-0001', 'nama' => 'Beras Rojo Lele 5kg', 'kategori_id' => $katSejahteraSembako->id, 'satuan' => 'karung', 'harga_modal' => 67000, 'harga_jual' => 74000, 'stok_awal' => 20, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0002', 'nama' => 'Minyak Goreng Bimoli 1L', 'kategori_id' => $katSejahteraSembako->id, 'satuan' => 'botol', 'harga_modal' => 16000, 'harga_jual' => 18500, 'stok_awal' => 4, 'stok_min' => 10, 'aktif' => true],
            ['kode' => 'BRG-0003', 'nama' => 'Gula Pasir Gulaku 1kg', 'kategori_id' => $katSejahteraSembako->id, 'satuan' => 'kg', 'harga_modal' => 15000, 'harga_jual' => 17500, 'stok_awal' => 15, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0004', 'nama' => 'Kopi ABC Susu Sachet', 'kategori_id' => $katSejahteraMinuman->id, 'satuan' => 'sachet', 'harga_modal' => 1900, 'harga_jual' => 2050, 'stok_awal' => 40, 'stok_min' => 10, 'aktif' => true],
            ['kode' => 'BRG-0005', 'nama' => 'Air Mineral Le Minerale 600ml', 'kategori_id' => $katSejahteraMinuman->id, 'satuan' => 'botol', 'harga_modal' => 2600, 'harga_jual' => 4000, 'stok_awal' => 0, 'stok_min' => 12, 'aktif' => true],
            ['kode' => 'BRG-0006', 'nama' => 'Chitato Sapi Panggang 68g', 'kategori_id' => $katSejahteraSnack->id, 'satuan' => 'bungkus', 'harga_modal' => 8500, 'harga_jual' => 11000, 'stok_awal' => 25, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0007', 'nama' => 'Oreo Vanilla 133g', 'kategori_id' => $katSejahteraSnack->id, 'satuan' => 'bungkus', 'harga_modal' => 7500, 'harga_jual' => 9500, 'stok_awal' => 30, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0008', 'nama' => 'Mie Sedaap Goreng 90g', 'kategori_id' => $katSejahteraSnack->id, 'satuan' => 'bungkus', 'harga_modal' => 2700, 'harga_jual' => 3500, 'stok_awal' => 50, 'stok_min' => 15, 'aktif' => true],
            ['kode' => 'BRG-0009', 'nama' => 'Teh Pucuk Harum 350ml', 'kategori_id' => $katSejahteraMinuman->id, 'satuan' => 'botol', 'harga_modal' => 3000, 'harga_jual' => 4500, 'stok_awal' => 35, 'stok_min' => 10, 'aktif' => true],
            ['kode' => 'BRG-0010', 'nama' => 'Susu Kental Manis Frisian Flag Gold 370g', 'kategori_id' => $katSejahteraMinuman->id, 'satuan' => 'kaleng', 'harga_modal' => 14000, 'harga_jual' => 16500, 'stok_awal' => 1, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0011', 'nama' => 'Garam Dapur Beryodium 250g', 'kategori_id' => $katSejahteraSembako->id, 'satuan' => 'bungkus', 'harga_modal' => 2000, 'harga_jual' => 3000, 'stok_awal' => 20, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0012', 'nama' => 'Biskuit Roma Kelapa 300g', 'kategori_id' => $katSejahteraSnack->id, 'satuan' => 'bungkus', 'harga_modal' => 9000, 'harga_jual' => 11500, 'stok_awal' => 10, 'stok_min' => 5, 'aktif' => false],
            ['kode' => 'BRG-0013', 'nama' => 'Beras Ketan Putih 1kg', 'kategori_id' => $katSejahteraSembako->id, 'satuan' => 'kg', 'harga_modal' => 16000, 'harga_jual' => 20000, 'stok_awal' => 100, 'stok_min' => 5, 'aktif' => true],
            ['kode' => 'BRG-0014', 'nama' => 'Susu UHT Cokelat 200ml', 'kategori_id' => $katSejahteraMinuman->id, 'satuan' => 'kotak', 'harga_modal' => 4500, 'harga_jual' => 6000, 'stok_awal' => 12, 'stok_min' => 5, 'aktif' => true],
        ];

        Auth::login($adminSejahtera);
        $savedBarangSejahtera = [];
        foreach ($barangSejahteraItems as $item) {
            $barang = Barang::create([
                'kios_id' => $kiosSejahtera->id,
                'kategori_id' => $item['kategori_id'],
                'kode' => $item['kode'],
                'nama' => $item['nama'],
                'satuan' => $item['satuan'],
                'harga_modal' => $item['harga_modal'],
                'harga_jual' => $item['harga_jual'],
                'stok' => 0,
                'stok_minimum' => $item['stok_min'],
                'is_aktif' => $item['aktif'],
                'created_at' => Carbon::now('Asia/Jayapura')->subDays(20),
                'updated_at' => Carbon::now('Asia/Jayapura')->subDays(20),
            ]);

            if ($item['stok_awal'] > 0) {
                $stokService->catat($barang, 'stok_awal', $item['stok_awal'], 'Stok awal barang');
            }

            LogAktivitas::catat('barang.tambah', "Menambahkan barang awal {$barang->nama} ({$barang->kode})");
            $savedBarangSejahtera[$item['kode']] = $barang;
        }

        $barangSejahtera15 = Barang::create([
            'kios_id' => $kiosSejahtera->id,
            'kategori_id' => $katSejahteraSnack->id,
            'kode' => 'BRG-0015',
            'nama' => 'Wafer Cokelat Crispy 100g',
            'satuan' => 'bungkus',
            'harga_modal' => 5000,
            'harga_jual' => 6500,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
            'created_at' => Carbon::now('Asia/Jayapura')->subDays(3),
            'updated_at' => Carbon::now('Asia/Jayapura')->subDays(3),
        ]);
        $stokService->catat($barangSejahtera15, 'stok_awal', 3, 'Stok pengenalan produk baru');
        $savedBarangSejahtera['BRG-0015'] = $barangSejahtera15;

        $supSejahtera1 = Supplier::create([
            'kios_id' => $kiosSejahtera->id,
            'nama' => 'PT Pangan Makmur Bersama',
            'no_telepon' => '089876543201',
            'alamat' => 'Jl. Raya Abepura No. 20, Jayapura',
            'is_aktif' => true,
        ]);
        $supSejahtera2 = Supplier::create([
            'kios_id' => $kiosSejahtera->id,
            'nama' => 'UD Surya Jaya Logistik',
            'no_telepon' => '089876543202',
            'alamat' => 'Jl. Raya Sentani No. 5, Jayapura',
            'is_aktif' => true,
        ]);
        $supSejahtera3 = Supplier::create([
            'kios_id' => $kiosSejahtera->id,
            'nama' => 'Grosir Snack Papuan Prima',
            'no_telepon' => '089876543203',
            'alamat' => 'Kawasan Niaga Sentani Blok C-4, Jayapura',
            'is_aktif' => true,
        ]);

        $barangMasukService->simpan([
            'tanggal' => Carbon::yesterday()->toDateString(),
            'supplier_id' => $supSejahtera1->id,
            'catatan' => 'Barang masuk beras & minyak goreng',
            'items' => [
                ['barang_id' => $savedBarangSejahtera['BRG-0001']->id, 'jumlah' => 10, 'harga_beli' => 66000],
                ['barang_id' => $savedBarangSejahtera['BRG-0002']->id, 'jumlah' => 15, 'harga_beli' => 15800],
            ],
        ], $adminSejahtera);

        $barangMasukService->simpan([
            'tanggal' => Carbon::today()->toDateString(),
            'supplier_id' => $supSejahtera3->id,
            'catatan' => 'Restock aneka snack dan camilan',
            'items' => [
                ['barang_id' => $savedBarangSejahtera['BRG-0006']->id, 'jumlah' => 20, 'harga_beli' => 8200],
                ['barang_id' => $savedBarangSejahtera['BRG-0007']->id, 'jumlah' => 20, 'harga_beli' => 7300],
            ],
        ], $adminSejahtera);

        $notaSejahteraBatal = $barangMasukService->simpan([
            'tanggal' => Carbon::today()->toDateString(),
            'supplier_id' => $supSejahtera2->id,
            'catatan' => 'Barang masuk kopi kemasan sachet (salah faktur)',
            'items' => [
                ['barang_id' => $savedBarangSejahtera['BRG-0004']->id, 'jumlah' => 30, 'harga_beli' => 1250],
            ],
        ], $adminSejahtera);
        $barangMasukService->batalkan($notaSejahteraBatal, 'Faktur keliru dengan toko sebelah', $adminSejahtera);

        $penyesuaianService->simpanManual([
            'tanggal' => Carbon::today()->toDateString(),
            'catatan' => 'Pemeriksaan rak sachet minuman kasir',
            'items' => [
                [
                    'barang_id' => $savedBarangSejahtera['BRG-0004']->id,
                    'jenis' => 'hilang',
                    'arah' => 'kurang',
                    'jumlah' => 3,
                    'alasan' => 'Selisih kehilangan di rak display depan kasir',
                ],
            ],
        ], $adminSejahtera);

        $penyesuaianService->simpanManual([
            'tanggal' => Carbon::today()->toDateString(),
            'catatan' => 'Penyortiran barang snack rentan kempes',
            'items' => [
                [
                    'barang_id' => $savedBarangSejahtera['BRG-0006']->id,
                    'jenis' => 'rusak',
                    'arah' => 'kurang',
                    'jumlah' => 2,
                    'alasan' => 'Kemasan kembung bocor saat ditata di rak',
                ],
            ],
        ], $adminSejahtera);

        $bSejahtera2 = $savedBarangSejahtera['BRG-0002']->fresh();
        $penyesuaianService->simpanOpname([
            'tanggal' => Carbon::today()->toDateString(),
            'catatan' => 'Stok opname berkala minyak goreng',
            'items' => [
                [
                    'barang_id' => $bSejahtera2->id,
                    'stok_sistem' => $bSejahtera2->stok,
                    'stok_fisik' => $bSejahtera2->stok - 1,
                    'alasan' => 'Selisih stok fisik rak etalase',
                ],
            ],
        ], $adminSejahtera);

        $waktuBukaSej1 = Carbon::now('Asia/Jayapura')->subDays(3)->setHour(8)->setMinute(0);
        $shiftSej1 = $shiftService->buka($kasirSejahtera, 100000, $waktuBukaSej1);

        $penjualanService->buat(
            items: [['barang_id' => $savedBarangSejahtera['BRG-0002']->id, 'jumlah' => 1]],
            bayar: 50000,
            token: 'seed_sej_s1_1',
            user: $kasirSejahtera,
            waktuTransaksi: (clone $waktuBukaSej1)->addHours(2)
        );
        $penjualanService->buat(
            items: [
                ['barang_id' => $savedBarangSejahtera['BRG-0008']->id, 'jumlah' => 2],
                ['barang_id' => $savedBarangSejahtera['BRG-0013']->id, 'jumlah' => 1],
            ],
            bayar: 50000,
            token: 'seed_sej_s1_2',
            user: $kasirSejahtera,
            waktuTransaksi: (clone $waktuBukaSej1)->addHours(4)
        );

        $totalTunaiSej1 = (int) $shiftSej1->penjualan()->where('status', 'selesai')->sum('total');
        $shiftService->tutup(
            shift: $shiftSej1,
            uangFisik: $shiftSej1->modal_awal + $totalTunaiSej1,
            catatan: null,
            penutup: $kasirSejahtera,
            paksa: false,
            waktuTutup: (clone $waktuBukaSej1)->setHour(17)->setMinute(0)
        );

        $waktuBukaSej2 = Carbon::now('Asia/Jayapura')->subDays(1)->setHour(8)->setMinute(30);
        $shiftSej2 = $shiftService->buka($kasirSejahtera, 150000, $waktuBukaSej2);

        $penjualanService->buat(
            items: [['barang_id' => $savedBarangSejahtera['BRG-0001']->id, 'jumlah' => 1]],
            bayar: 100000,
            token: 'seed_sej_s2_1',
            user: $kasirSejahtera,
            waktuTransaksi: (clone $waktuBukaSej2)->addHours(1)
        );
        $notaVoidSej = $penjualanService->buat(
            items: [['barang_id' => $savedBarangSejahtera['BRG-0007']->id, 'jumlah' => 2]],
            bayar: 20000,
            token: 'seed_sej_s2_void',
            user: $kasirSejahtera,
            waktuTransaksi: (clone $waktuBukaSej2)->addHours(2)
        );
        $penjualanService->batalkan(
            penjualan: $notaVoidSej,
            alasan: 'Pelanggan membatalkan pembelian snack karena salah rasa.',
            user: $kasirSejahtera
        );

        $penjualanService->buat(
            items: [['barang_id' => $savedBarangSejahtera['BRG-0004']->id, 'jumlah' => 2]],
            bayar: 30000,
            token: 'seed_sej_s2_2',
            user: $kasirSejahtera,
            waktuTransaksi: (clone $waktuBukaSej2)->addHours(4)
        );

        $totalTunaiSej2 = (int) $shiftSej2->penjualan()->where('status', 'selesai')->sum('total');
        $shiftService->tutup(
            shift: $shiftSej2,
            uangFisik: $shiftSej2->modal_awal + $totalTunaiSej2 - 5000,
            catatan: 'Uang fisik kurang Rp 5.000 dari pembulatan pecahan receh kembalian.',
            penutup: $kasirSejahtera,
            paksa: false,
            waktuTutup: (clone $waktuBukaSej2)->setHour(18)->setMinute(0)
        );

        // Shift 3 Kios Sejahtera (Sedang Berjalan)
        $waktuBukaSej3 = Carbon::now('Asia/Jayapura')->subHours(4);
        $shiftSej3 = $shiftService->buka($kasirSejahtera, 200000, $waktuBukaSej3);

        $penjualanService->buat(
            items: [['barang_id' => $savedBarangSejahtera['BRG-0006']->id, 'jumlah' => 3]],
            bayar: 50000,
            token: 'seed_sej_s3_1',
            user: $kasirSejahtera,
            waktuTransaksi: (clone $waktuBukaSej3)->addHour()
        );
        $penjualanService->buat(
            items: [['barang_id' => $savedBarangSejahtera['BRG-0003']->id, 'jumlah' => 1]],
            bayar: 30000,
            token: 'seed_sej_s3_2',
            user: $kasirSejahtera,
            waktuTransaksi: (clone $waktuBukaSej3)->addHours(2)
        );

        // ==========================================
        // 3. Kios Apiid (Akun Utama Pengguna)
        // ==========================================
        $kiosApiid = Kios::create([
            'nama_kios' => 'Kios Apiid',
            'alamat' => 'Jl. Kios Usaha No. 01',
            'no_telepon' => '081234567899',
            'status' => 'aktif',
        ]);

        $adminApiid = User::create([
            'kios_id' => $kiosApiid->id,
            'name' => 'Hafid Riski',
            'username' => 'hafid_riski',
            'email' => 'hafidriski.ha@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $kasirApiid = User::create([
            'kios_id' => $kiosApiid->id,
            'name' => 'Kasir Apiid',
            'username' => 'kasir_apiid',
            'email' => null,
            'password' => Hash::make('12345678'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        $katApiidSembako = Kategori::create([
            'kios_id' => $kiosApiid->id,
            'nama' => 'Sembako',
        ]);
        $katApiidMakanan = Kategori::create([
            'kios_id' => $kiosApiid->id,
            'nama' => 'Makanan & Minuman',
        ]);

        Auth::login($adminApiid);
        $barangApiid1 = Barang::create([
            'kios_id' => $kiosApiid->id,
            'kategori_id' => $katApiidSembako->id,
            'kode' => 'BRG-0001',
            'nama' => 'Beras Pandan Wangi 5kg',
            'satuan' => 'karung',
            'harga_modal' => 65000,
            'harga_jual' => 74000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);
        $stokService->catat($barangApiid1, 'stok_awal', 20, 'Saldo stok awal sistem Kios Apiid');

        $barangApiid2 = Barang::create([
            'kios_id' => $kiosApiid->id,
            'kategori_id' => $katApiidMakanan->id,
            'kode' => 'BRG-0002',
            'nama' => 'Minyak Goreng 2L',
            'satuan' => 'botol',
            'harga_modal' => 30000,
            'harga_jual' => 35000,
            'stok' => 0,
            'stok_minimum' => 5,
            'is_aktif' => true,
        ]);
        $stokService->catat($barangApiid2, 'stok_awal', 15, 'Saldo stok awal sistem Kios Apiid');

        Auth::logout();
    }
}
