<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Kios;
use App\Models\LogAktivitas;
use App\Services\AnalisisBarangService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AnalisisController extends Controller
{
    public function __construct(
        protected AnalisisBarangService $analisisService
    ) {}

    /**
     * Tab 1: Ringkasan Performa Barang (Fast / Normal / Slow / Tidak Laku).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $periode = $this->validasiPeriode($request);

        $items = $this->analisisService->hitung($periode, $user->kios_id, $user);
        $ringkasan = $this->analisisService->ringkasan($items, $user->kios);

        // 10 Barang Terlaris (menurut frekuensi penjualan selesai, tie-break terjual netto)
        $sepuluhTerlaris = $items->filter(fn($row) => $row['frekuensi'] > 0)
            ->sort(function ($a, $b) {
                if ($b['frekuensi'] !== $a['frekuensi']) {
                    return $b['frekuensi'] <=> $a['frekuensi'];
                }
                if ($b['terjual_netto'] !== $a['terjual_netto']) {
                    return $b['terjual_netto'] <=> $a['terjual_netto'];
                }
                return strcasecmp($a['nama'], $b['nama']);
            })
            ->take(10)
            ->values();

        // Barang Kurang Laku (Slow Moving dan Tidak Laku) diurutkan berdasarkan modal tertahan terbesar
        $barangKurangLaku = $items->filter(function ($row) {
            return in_array($row['kelas'], ['slow', 'tidak_laku'], true);
        })
            ->sortByDesc('modal_tertahan')
            ->values();

        // Barang Margin Tipis (margin < ambang margin kios)
        $barangMarginTipis = $items->filter(fn($row) => $row['is_margin_tipis'])
            ->sortBy('margin_persen')
            ->values();

        return view('admin.analisis.index', [
            'periode' => $periode,
            'ringkasan' => $ringkasan,
            'sepuluhTerlaris' => $sepuluhTerlaris,
            'barangKurangLaku' => $barangKurangLaku,
            'barangMarginTipis' => $barangMarginTipis,
            'totalItemsCount' => $items->count(),
            'kios' => $user->kios,
        ]);
    }

    /**
     * Tab 2: Rekomendasi Kulakan (Reorder Point & Estimasi Belanja).
     */
    public function rekomendasi(Request $request): View
    {
        $user = $request->user();
        $periode = $this->validasiPeriode($request);

        // Validasi filter kategori harus milik kios yang login
        $request->validate([
            'kategori_id' => [
                'nullable',
                Rule::exists('kategori', 'id')->where('kios_id', $user->kios_id),
            ],
            'status' => [
                'nullable',
                'string',
                Rule::in(['kulakan_banyak', 'kulakan_secukupnya', 'tahan_kulakan', 'stok_cukup', 'perlu_kulakan']),
            ],
        ], [
            'kategori_id.exists' => 'Kategori yang dipilih tidak valid atau bukan milik kios Anda.',
        ]);

        $filterStatus = $request->query('status');
        $filterKategoriId = $request->query('kategori_id');
        $keyword = trim((string) $request->query('q', ''));

        $items = $this->analisisService->hitung($periode, $user->kios_id, $user);
        $ringkasan = $this->analisisService->ringkasan($items, $user->kios);

        // Penerapan filter
        $filtered = $items->filter(function ($row) use ($filterStatus, $filterKategoriId, $keyword) {
            // Filter kategori
            if (! empty($filterKategoriId) && (int) $row['kategori_id'] !== (int) $filterKategoriId) {
                return false;
            }

            // Filter status rekomendasi
            if (! empty($filterStatus)) {
                if ($filterStatus === 'perlu_kulakan') {
                    if ($row['jumlah_rekomendasi'] <= 0) {
                        return false;
                    }
                } elseif ($row['status_rekomendasi'] !== $filterStatus) {
                    return false;
                }
            }

            // Filter pencarian kode / nama
            if ($keyword !== '') {
                $needle = strtolower($keyword);
                $matchNama = str_contains(strtolower($row['nama']), $needle);
                $matchKode = str_contains(strtolower($row['kode']), $needle);
                if (! $matchNama && ! $matchKode) {
                    return false;
                }
            }

            return true;
        });

        // Pengurutan bawaan: Prioritas status rekomendasi lalu cakupan hari terkecil
        // Prioritas status:
        // 1 = Kulakan Banyak
        // 2 = Kulakan Secukupnya
        // 3 = Tahan Kulakan
        // 4 = Stok Cukup
        $sorted = $filtered->sort(function ($a, $b) {
            $prioritas = [
                'kulakan_banyak' => 1,
                'kulakan_secukupnya' => 2,
                'tahan_kulakan' => 3,
                'stok_cukup' => 4,
            ];

            $pA = $prioritas[$a['status_rekomendasi']] ?? 99;
            $pB = $prioritas[$b['status_rekomendasi']] ?? 99;

            if ($pA !== $pB) {
                return $pA <=> $pB;
            }

            // Tie-break: Cakupan hari terkecil (stok yang mau habis duluan)
            $cA = $a['cakupan_hari'] ?? 999999;
            $cB = $b['cakupan_hari'] ?? 999999;

            if ($cA !== $cB) {
                return $cA <=> $cB;
            }

            return strcasecmp($a['nama'], $b['nama']);
        })->values();

        // Paginasi manual untuk Collection (25 item per halaman)
        $perPage = 25;
        $page = (int) $request->query('page', 1);
        $paginatedItems = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage)->values(),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => route('admin.analisis.rekomendasi'), 'query' => $request->query()]
        );

        $kategoriList = Kategori::where('kios_id', $user->kios_id)->orderBy('nama')->get();

        return view('admin.analisis.rekomendasi', [
            'periode' => $periode,
            'ringkasan' => $ringkasan,
            'items' => $paginatedItems,
            'kategoriList' => $kategoriList,
            'filterStatus' => $filterStatus,
            'filterKategoriId' => $filterKategoriId,
            'keyword' => $keyword,
            'kios' => $user->kios,
        ]);
    }

    /**
     * Unduh PDF Daftar Rekomendasi Kulakan.
     */
    public function unduhPdf(Request $request)
    {
        $user = $request->user();
        $periode = $this->validasiPeriode($request);

        ini_set('memory_limit', '512M');
        set_time_limit(120);

        // Ambil data analisis dan hanya tampilkan barang yang butuh kulakan (rekomendasi > 0)
        $items = $this->analisisService->hitung($periode, $user->kios_id, $user)
            ->filter(fn($row) => $row['jumlah_rekomendasi'] > 0)
            ->sortBy('nama')
            ->values();

        $totalEstimasiBiaya = (int) $items->sum('estimasi_biaya');
        $kios = $user->kios;

        // Catat Log Aktivitas
        LogAktivitas::catat(
            'analisis.cetak',
            "Mengunduh PDF Daftar Belanja Stok untuk periode {$periode} hari terakhir."
        );

        $kiosSlug = Str::slug($kios->nama_kios ?? 'kios');
        $todayStr = Carbon::today('Asia/Jayapura')->format('Y-m-d');
        $namaFile = "Daftar-Belanja-Stok_{$kiosSlug}_{$todayStr}.pdf";

        $pdf = Pdf::loadView('admin.analisis.pdf.kulakan', [
            'kios' => $kios,
            'periode' => $periode,
            'items' => $items,
            'totalEstimasiBiaya' => $totalEstimasiBiaya,
            'waktuCetak' => Carbon::now('Asia/Jayapura')->translatedFormat('d F Y, H:i') . ' WIT',
            'pencetak' => $user->name,
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        return $pdf->download($namaFile);
    }

    /**
     * Fitur "Buat Barang Masuk dari Pilihan":
     * Menerima daftar barang_id terpilih, menghitung ulang secara aman di server,
     * menyimpan ke session flash 'prefill_barang_masuk', lalu mengarahkan ke form Barang Masuk.
     */
    public function prefillBarangMasuk(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'barang_ids' => ['required', 'array', 'min:1'],
            'barang_ids.*' => [
                'required',
                'integer',
                Rule::exists('barang', 'id')
                    ->where('kios_id', $user->kios_id)
                    ->where('is_aktif', true),
            ],
            'periode' => ['nullable', 'integer', Rule::in([7, 14, 30, 60, 90])],
        ], [
            'barang_ids.required' => 'Pilih minimal satu barang untuk dibuat draf barang masuk.',
            'barang_ids.min' => 'Pilih minimal satu barang untuk dibuat draf barang masuk.',
            'barang_ids.*.exists' => 'Barang yang dipilih tidak valid atau bukan milik kios Anda.',
        ]);

        $periode = (int) ($validated['periode'] ?? 30);
        $selectedIds = array_map('intval', $validated['barang_ids']);

        // Hitung ulang rekomendasi di server untuk keamanan (tidak mempercayai input client)
        $allItems = $this->analisisService->hitung($periode, $user->kios_id, $user);
        $matchedItems = $allItems->whereIn('barang_id', $selectedIds);

        if ($matchedItems->isEmpty()) {
            return back()->with('error', 'Tidak ada barang valid yang terpilih untuk restok.');
        }

        $prefill = [];
        foreach ($matchedItems as $item) {
            $prefill[] = [
                'barang_id' => $item['barang_id'],
                'nama' => $item['nama'],
                'kode' => $item['kode'],
                'satuan' => $item['satuan'],
                'stok_sekarang' => $item['stok'],
                'modal_sekarang' => $item['harga_modal'],
                'jumlah' => max(1, (int) $item['jumlah_rekomendasi']),
                'harga_beli' => (int) $item['harga_modal'],
            ];
        }

        // Simpan ke session flash agar dibaca oleh form Barang Masuk (Tahap 5)
        session()->flash('prefill_barang_masuk', $prefill);

        $count = count($prefill);
        return redirect()->route('admin.barang-masuk.create')
            ->with('success', "Draf barang masuk berhasil disiapkan dengan {$count} barang dari rekomendasi. Silakan periksa, pilih supplier, dan simpan.");
    }

    /**
     * Tab 3: Pengaturan Parameter Kulakan & Analisis.
     */
    public function pengaturan(Request $request): View
    {
        $user = $request->user();
        return view('admin.analisis.pengaturan', [
            'kios' => $user->kios,
        ]);
    }

    /**
     * Simpan Perubahan Pengaturan Parameter Kulakan Kios.
     */
    public function updatePengaturan(Request $request): RedirectResponse
    {
        $user = $request->user();
        $kios = $user->kios;

        $validated = $request->validate([
            'lead_time_hari' => ['required', 'integer', 'min:0', 'max:30'],
            'hari_pengaman' => ['required', 'integer', 'min:0', 'max:30'],
            'cakupan_kulakan_hari' => ['required', 'integer', 'min:1', 'max:60'],
            'batas_stok_berlebih_hari' => ['required', 'integer', 'min:14', 'max:365'],
            'margin_tipis_persen' => ['required', 'integer', 'min:0', 'max:100'],
        ], [
            'lead_time_hari.min' => 'Lead time minimal 0 hari.',
            'lead_time_hari.max' => 'Lead time maksimal 30 hari.',
            'hari_pengaman.min' => 'Hari pengaman minimal 0 hari.',
            'hari_pengaman.max' => 'Hari pengaman maksimal 30 hari.',
            'cakupan_kulakan_hari.min' => 'Target stok minimal 1 hari.',
            'cakupan_kulakan_hari.max' => 'Target stok maksimal 60 hari.',
            'batas_stok_berlebih_hari.min' => 'Batas stok berlebih minimal 14 hari.',
            'batas_stok_berlebih_hari.max' => 'Batas stok berlebih maksimal 365 hari.',
            'margin_tipis_persen.min' => 'Batas margin tipis minimal 0%.',
            'margin_tipis_persen.max' => 'Batas margin tipis maksimal 100%.',
        ]);

        $dataLama = [
            'lead_time_hari' => $kios->lead_time_hari,
            'hari_pengaman' => $kios->hari_pengaman,
            'cakupan_kulakan_hari' => $kios->cakupan_kulakan_hari,
            'batas_stok_berlebih_hari' => $kios->batas_stok_berlebih_hari,
            'margin_tipis_persen' => $kios->margin_tipis_persen,
        ];

        $kios->update($validated);

        LogAktivitas::catat(
            'analisis.ubah_pengaturan',
            'Memperbarui parameter model restok dan analisis performa barang.',
            $dataLama,
            $validated
        );

        return redirect()->route('admin.analisis.pengaturan')
            ->with('success', 'Pengaturan parameter analisis restok berhasil disimpan.');
    }

    /**
     * Kembalikan Parameter Kulakan ke Nilai Bawaan.
     */
    public function resetPengaturan(Request $request): RedirectResponse
    {
        $user = $request->user();
        $kios = $user->kios;

        $dataLama = [
            'lead_time_hari' => $kios->lead_time_hari,
            'hari_pengaman' => $kios->hari_pengaman,
            'cakupan_kulakan_hari' => $kios->cakupan_kulakan_hari,
            'batas_stok_berlebih_hari' => $kios->batas_stok_berlebih_hari,
            'margin_tipis_persen' => $kios->margin_tipis_persen,
        ];

        $defaultData = [
            'lead_time_hari' => 2,
            'hari_pengaman' => 3,
            'cakupan_kulakan_hari' => 7,
            'batas_stok_berlebih_hari' => 60,
            'margin_tipis_persen' => 10,
        ];

        $kios->update($defaultData);

        LogAktivitas::catat(
            'analisis.ubah_pengaturan',
            'Mengembalikan parameter restok ke nilai bawaan sistem.',
            $dataLama,
            $defaultData
        );

        return redirect()->route('admin.analisis.pengaturan')
            ->with('success', 'Pengaturan restok telah dikembalikan ke nilai bawaan.');
    }

    /**
     * Validasi input periode dengan nilai yang diizinkan (7, 14, 30, 60, 90).
     */
    protected function validasiPeriode(Request $request): int
    {
        $raw = $request->query('periode', 30);
        $periode = (int) $raw;

        if (! in_array($periode, [7, 14, 30, 60, 90], true) || ! is_numeric($raw)) {
            throw ValidationException::withMessages([
                'periode' => 'Pilihan periode analisis tidak valid. Pilih salah satu dari: 7, 14, 30, 60, atau 90 hari.',
            ]);
        }

        return $periode;
    }
}
