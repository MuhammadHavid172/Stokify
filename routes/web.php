<?php

use App\Http\Controllers\Admin\AnalisisController;
use App\Http\Controllers\Admin\BarangController;
use App\Http\Controllers\Admin\BarangMasukController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KasirController as AdminKasirController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\KiosController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LogAktivitasController;
use App\Http\Controllers\Admin\PenjualanController as AdminPenjualanController;
use App\Http\Controllers\Admin\PenyesuaianStokController;
use App\Http\Controllers\Admin\ShiftKasirController as AdminShiftKasirController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing page: jika user sudah login, redirect sesuai perannya
Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->homeRoute());
    }
    return view('landing');
})->name('landing');

// Alias route /dashboard lama Breeze agar selalu mengarah ke homeRoute masing-masing
Route::get('/dashboard', function () {
    return redirect(auth()->user()->homeRoute());
})->middleware(['auth', 'active'])->name('dashboard');

// Admin Routes (hanya admin)
Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Kategori Barang
    Route::prefix('kategori')->name('kategori.')->group(function () {
        Route::get('/', [KategoriController::class, 'index'])->name('index');
        Route::post('/', [KategoriController::class, 'store'])->name('store');
        Route::put('/{kategori}', [KategoriController::class, 'update'])->name('update');
        Route::delete('/{kategori}', [KategoriController::class, 'destroy'])->name('destroy');
    });

    // Master Data Barang (tanpa hapus permanen/destroy)
    Route::prefix('barang')->name('barang.')->group(function () {
        Route::get('/', [BarangController::class, 'index'])->name('index');
        Route::get('/create', [BarangController::class, 'create'])->name('create');
        Route::get('/generate-kode', [BarangController::class, 'generateKodeApi'])->name('generate_kode');
        Route::post('/', [BarangController::class, 'store'])->name('store');
        Route::get('/{barang}', [BarangController::class, 'show'])->name('show');
        Route::get('/{barang}/edit', [BarangController::class, 'edit'])->name('edit');
        Route::put('/{barang}', [BarangController::class, 'update'])->name('update');
        Route::post('/{barang}/toggle', [BarangController::class, 'toggle'])->name('toggle');
    });

    // Master Supplier (tanpa hapus permanen/destroy)
    Route::prefix('supplier')->name('supplier.')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::post('/{supplier}/toggle', [SupplierController::class, 'toggle'])->name('toggle');
    });

    // Transaksi Barang Masuk (tanpa edit & tanpa destroy)
    Route::prefix('barang-masuk')->name('barang-masuk.')->group(function () {
        Route::get('/', [BarangMasukController::class, 'index'])->name('index');
        Route::get('/create', [BarangMasukController::class, 'create'])->name('create');
        Route::post('/', [BarangMasukController::class, 'store'])->name('store');
        Route::get('/{barangMasuk}', [BarangMasukController::class, 'show'])->name('show');
        Route::post('/{barangMasuk}/batal', [BarangMasukController::class, 'batal'])->name('batal');
    });

    // Penyesuaian Stok & Stok Opname (tanpa edit & tanpa destroy)
    Route::prefix('penyesuaian-stok')->name('penyesuaian.')->group(function () {
        Route::get('/', [PenyesuaianStokController::class, 'index'])->name('index');
        Route::get('/create', [PenyesuaianStokController::class, 'create'])->name('create');
        Route::post('/', [PenyesuaianStokController::class, 'store'])->name('store');
        Route::get('/opname', [PenyesuaianStokController::class, 'opname'])->name('opname');
        Route::post('/opname', [PenyesuaianStokController::class, 'storeOpname'])->name('storeOpname');
        Route::get('/{penyesuaianStok}', [PenyesuaianStokController::class, 'show'])->name('show');
    });

    // Kelola Kasir (CRUD tanpa hapus permanen)
    Route::prefix('kasir')->name('kasir.')->group(function () {
        Route::get('/', [AdminKasirController::class, 'index'])->name('index');
        Route::get('/create', [AdminKasirController::class, 'create'])->name('create');
        Route::post('/', [AdminKasirController::class, 'store'])->name('store');
        Route::get('/{kasir}/edit', [AdminKasirController::class, 'edit'])->name('edit');
        Route::put('/{kasir}', [AdminKasirController::class, 'update'])->name('update');
        Route::post('/{kasir}/toggle', [AdminKasirController::class, 'toggle'])->name('toggle');
        Route::post('/{kasir}/reset-password', [AdminKasirController::class, 'resetPassword'])->name('resetPassword');
    });

    // Riwayat Transaksi Penjualan Admin (tanpa update & tanpa destroy)
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/', [AdminPenjualanController::class, 'index'])->name('index');
        Route::get('/{penjualan}', [AdminPenjualanController::class, 'show'])->name('show');
        Route::post('/{penjualan}/batal', [AdminPenjualanController::class, 'batalkan'])->name('batal');
    });

    // Shift Kasir Admin (tanpa update & tanpa destroy)
    Route::prefix('shift')->name('shift.')->group(function () {
        Route::get('/', [AdminShiftKasirController::class, 'index'])->name('index');
        Route::get('/{shift}', [AdminShiftKasirController::class, 'show'])->name('show');
        Route::post('/{shift}/tutup-paksa', [AdminShiftKasirController::class, 'tutupPaksa'])->name('tutup_paksa');
    });

    // Laporan Kios Admin (tanpa route ubah data)
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [LaporanController::class, 'index'])->name('index');
        Route::get('/keuangan', [LaporanController::class, 'keuangan'])->name('keuangan');
        Route::get('/keuangan/pdf', [LaporanController::class, 'keuanganPdf'])->middleware('throttle:unduh.pdf')->name('keuangan.pdf');
        Route::get('/audit', [LaporanController::class, 'audit'])->name('audit');
        Route::get('/audit/pdf', [LaporanController::class, 'auditPdf'])->middleware('throttle:unduh.pdf')->name('audit.pdf');
        Route::get('/stok', [LaporanController::class, 'stok'])->name('stok');
        Route::get('/stok/pdf', [LaporanController::class, 'stokPdf'])->middleware('throttle:unduh.pdf')->name('stok.pdf');
    });

    // Analisis Performa Barang & Rekomendasi Restok (Tahap 10)
    Route::prefix('analisis')->name('analisis.')->group(function () {
        Route::get('/', [AnalisisController::class, 'index'])->name('index');
        Route::get('/rekomendasi', [AnalisisController::class, 'rekomendasi'])->name('rekomendasi');
        Route::get('/rekomendasi/pdf', [AnalisisController::class, 'unduhPdf'])->middleware('throttle:unduh.pdf')->name('pdf');
        Route::post('/prefill-barang-masuk', [AnalisisController::class, 'prefillBarangMasuk'])->name('prefill');
        Route::get('/pengaturan', [AnalisisController::class, 'pengaturan'])->name('pengaturan');
        Route::put('/pengaturan', [AnalisisController::class, 'updatePengaturan'])->name('pengaturan.update');
        Route::post('/pengaturan/reset', [AnalisisController::class, 'resetPengaturan'])->name('pengaturan.reset');
    });

    // Profil Kios (Tahap 11)
    Route::prefix('kios')->name('kios.')->group(function () {
        Route::get('/', [KiosController::class, 'index'])->name('index');
        Route::put('/', [KiosController::class, 'update'])->name('update');
    });

    // Penampil Log Aktivitas Kios (Tahap 11, hanya baca)
    Route::get('/log-aktivitas', [LogAktivitasController::class, 'index'])->name('log.index');
});

// Kasir POS Routes (dapat diakses role kasir maupun admin)
Route::middleware(['auth', 'active'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('index');
    Route::get('/barang/cari', [KasirController::class, 'cariBarang'])->middleware('throttle:kasir.barang.cari')->name('barang.cari');
    Route::post('/penjualan', [KasirController::class, 'store'])->middleware('throttle:kasir.penjualan.store')->name('penjualan.store');
    Route::get('/penjualan/{penjualan}/struk', [KasirController::class, 'struk'])->name('penjualan.struk');
    Route::post('/penjualan/{penjualan}/batal', [KasirController::class, 'batalkanPenjualan'])->name('penjualan.batal');
    Route::get('/riwayat', [KasirController::class, 'riwayat'])->name('riwayat');

    // Buka & Tutup Shift Kasir
    Route::post('/shift/buka', [KasirController::class, 'bukaShift'])->name('shift.buka');
    Route::get('/shift/tutup', [KasirController::class, 'tutupShiftForm'])->name('shift.tutup');
    Route::post('/shift/tutup', [KasirController::class, 'tutupShift'])->name('shift.tutup.post');
    Route::get('/shift/{shift}/rekap', [KasirController::class, 'rekapShift'])->name('shift.rekap');
});

// Profil Pengguna (admin & kasir) & Notifikasi Realtime
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/notifikasi-stok', [KasirController::class, 'notifikasiStok'])->name('notifikasi.stok');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
