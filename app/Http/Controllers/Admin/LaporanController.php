<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\LogAktivitas;
use App\Services\LaporanService;
use App\ValueObjects\PeriodeLaporan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LaporanController extends Controller
{
    protected LaporanService $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    /**
     * Halaman Utama Menu Laporan: Tiga kartu navigasi laporan.
     */
    public function index(): View
    {
        return view('admin.laporan.index');
    }

    /**
     * Tampilan Layar Laporan Ringkasan Keuangan.
     */
    public function keuangan(Request $request): View
    {
        $user = $request->user();
        $periode = PeriodeLaporan::fromRequest($request, maxRentangHari: 366, defaultJenis: 'bulanan');

        $data = $this->laporanService->keuangan($periode, $user->kios_id, $user);
        $data['periode'] = $periode;

        return view('admin.laporan.keuangan', $data);
    }

    /**
     * Unduh PDF Laporan Ringkasan Keuangan.
     */
    public function keuanganPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $user = $request->user();
        $periode = PeriodeLaporan::fromRequest($request, maxRentangHari: 366, defaultJenis: 'bulanan');

        $data = $this->laporanService->keuangan($periode, $user->kios_id, $user);
        $data['periode'] = $periode;

        LogAktivitas::catat(
            'laporan.cetak',
            "Mengunduh PDF Laporan Ringkasan Keuangan periode {$periode->label}",
            [
                'jenis_laporan' => 'keuangan',
                'periode' => $periode->toQueryParams(),
            ]
        );

        $kiosSlug = Str::slug($user->kios->nama_kios ?? 'kios');
        $namaFile = "Laporan-Keuangan_{$kiosSlug}_{$periode->toFileDateSlug()}.pdf";

        $pdf = Pdf::loadView('admin.laporan.pdf.keuangan', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download($namaFile);
    }

    /**
     * Tampilan Layar Laporan Audit Transaksi dan Shift.
     */
    public function audit(Request $request): View
    {
        $user = $request->user();
        $periode = PeriodeLaporan::fromRequest($request, maxRentangHari: 92, defaultJenis: 'bulanan');

        $data = $this->laporanService->audit($periode, $user->kios_id, $user);
        $data['periode'] = $periode;

        return view('admin.laporan.audit', $data);
    }

    /**
     * Unduh PDF Laporan Audit Transaksi dan Shift.
     */
    public function auditPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $user = $request->user();
        $periode = PeriodeLaporan::fromRequest($request, maxRentangHari: 92, defaultJenis: 'bulanan');

        $data = $this->laporanService->audit($periode, $user->kios_id, $user);
        $data['periode'] = $periode;

        LogAktivitas::catat(
            'laporan.cetak',
            "Mengunduh PDF Laporan Audit Transaksi dan Shift periode {$periode->label}",
            [
                'jenis_laporan' => 'audit',
                'periode' => $periode->toQueryParams(),
            ]
        );

        $kiosSlug = Str::slug($user->kios->nama_kios ?? 'kios');
        $namaFile = "Laporan-Audit_{$kiosSlug}_{$periode->toFileDateSlug()}.pdf";

        $pdf = Pdf::loadView('admin.laporan.pdf.audit', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download($namaFile);
    }

    /**
     * Tampilan Layar Laporan Mutasi dan Sisa Stok.
     */
    public function stok(Request $request): View
    {
        $user = $request->user();

        // Validasi filter kategori harus milik kios yang login
        $request->validate([
            'kategori_id' => [
                'nullable',
                Rule::exists('kategori', 'id')->where('kios_id', $user->kios_id),
            ],
        ], [
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid atau bukan milik kios Anda.',
        ]);

        $periode = PeriodeLaporan::fromRequest($request, maxRentangHari: 92, defaultJenis: 'bulanan');

        $filters = [
            'q' => $request->query('q'),
            'kategori_id' => $request->query('kategori_id'),
            'hanya_bergerak' => $request->boolean('hanya_bergerak'),
            'hanya_perlu_kulakan' => $request->boolean('hanya_perlu_kulakan'),
        ];

        $data = $this->laporanService->stok($periode, $user->kios_id, $user, $filters);
        $data['periode'] = $periode;
        $data['filters'] = $filters;
        $data['kategoriOptions'] = Kategori::where('kios_id', $user->kios_id)->orderBy('nama')->get();

        // Paginasi 25 item di layar
        $currentPage = LengthAwarePaginator::resolveCurrentPage('page');
        $perPage = 25;
        $itemsCollection = collect($data['items']);
        $currentPageItems = $itemsCollection->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $data['paginated_items'] = new LengthAwarePaginator(
            $currentPageItems,
            $itemsCollection->count(),
            $perPage,
            $currentPage,
            [
                'path' => route('admin.laporan.stok'),
                'query' => $request->query(),
            ]
        );

        return view('admin.laporan.stok', $data);
    }

    /**
     * Unduh PDF Laporan Mutasi dan Sisa Stok.
     */
    public function stokPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $user = $request->user();

        $request->validate([
            'kategori_id' => [
                'nullable',
                Rule::exists('kategori', 'id')->where('kios_id', $user->kios_id),
            ],
        ], [
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid atau bukan milik kios Anda.',
        ]);

        $periode = PeriodeLaporan::fromRequest($request, maxRentangHari: 92, defaultJenis: 'bulanan');

        $filters = [
            'q' => $request->query('q'),
            'kategori_id' => $request->query('kategori_id'),
            'hanya_bergerak' => $request->boolean('hanya_bergerak'),
            'hanya_perlu_kulakan' => $request->boolean('hanya_perlu_kulakan'),
        ];

        $data = $this->laporanService->stok($periode, $user->kios_id, $user, $filters);
        $data['periode'] = $periode;
        $data['filters'] = $filters;

        LogAktivitas::catat(
            'laporan.cetak',
            "Mengunduh PDF Laporan Mutasi dan Sisa Stok periode {$periode->label}",
            [
                'jenis_laporan' => 'stok',
                'periode' => $periode->toQueryParams(),
            ]
        );

        $kiosSlug = Str::slug($user->kios->nama_kios ?? 'kios');
        $namaFile = "Laporan-Stok_{$kiosSlug}_{$periode->toFileDateSlug()}.pdf";

        $pdf = Pdf::loadView('admin.laporan.pdf.stok', $data)
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download($namaFile);
    }
}
