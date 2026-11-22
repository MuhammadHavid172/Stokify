<?php

namespace App\ValueObjects;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PeriodeLaporan
{
    public string $jenis;
    public Carbon $tanggalMulai;
    public Carbon $tanggalAkhir;
    public string $label;
    public bool $isBerjalan;
    public int $rentangHari;

    // Nilai input form asli untuk mempertahankan state query string
    public ?string $tanggalHarian = null;
    public ?string $tanggalMingguan = null;
    public ?int $bulan = null;
    public ?int $tahun = null;
    public ?string $tanggalKustomMulai = null;
    public ?string $tanggalKustomAkhir = null;

    public function __construct(
        string $jenis,
        Carbon $tanggalMulai,
        Carbon $tanggalAkhir,
        string $label,
        bool $isBerjalan,
        int $rentangHari,
        array $inputState = []
    ) {
        $this->jenis = $jenis;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalAkhir = $tanggalAkhir;
        $this->label = $label;
        $this->isBerjalan = $isBerjalan;
        $this->rentangHari = $rentangHari;

        $this->tanggalHarian = $inputState['tanggal_harian'] ?? null;
        $this->tanggalMingguan = $inputState['tanggal_mingguan'] ?? null;
        $this->bulan = isset($inputState['bulan']) ? (int) $inputState['bulan'] : null;
        $this->tahun = isset($inputState['tahun']) ? (int) $inputState['tahun'] : null;
        $this->tanggalKustomMulai = $inputState['tanggal_kustom_mulai'] ?? null;
        $this->tanggalKustomAkhir = $inputState['tanggal_kustom_akhir'] ?? null;
    }

    /**
     * Buat PeriodeLaporan dari HTTP Request dengan validasi ketat.
     *
     * @param Request $request
     * @param int $maxRentangHari (366 hari untuk keuangan, 92 hari untuk audit/stok)
     * @param string $defaultJenis
     * @return self
     * @throws ValidationException
     */
    public static function fromRequest(Request $request, int $maxRentangHari = 366, string $defaultJenis = 'bulanan'): self
    {
        $today = Carbon::today('Asia/Jayapura');
        $jenis = strtolower((string) $request->input('jenis', $defaultJenis));

        if (! in_array($jenis, ['harian', 'mingguan', 'bulanan', 'kustom'], true)) {
            $jenis = $defaultJenis;
        }

        $inputState = [];
        $isBerjalan = false;

        switch ($jenis) {
            case 'harian':
                $tglStr = $request->input('tanggal_harian', $request->input('tanggal'));
                if (! $tglStr) {
                    $tgl = $today->copy();
                } else {
                    try {
                        $tgl = Carbon::parse($tglStr, 'Asia/Jayapura')->startOfDay();
                    } catch (\Exception $e) {
                        throw ValidationException::withMessages([
                            'tanggal_harian' => 'Format tanggal harian tidak valid.',
                        ]);
                    }
                }

                if ($tgl->greaterThan($today)) {
                    throw ValidationException::withMessages([
                        'tanggal_harian' => 'Tanggal laporan tidak boleh melebihi hari ini.',
                    ]);
                }

                $tanggalMulai = $tgl->copy()->startOfDay();
                $tanggalAkhir = $tgl->copy()->endOfDay();
                $isBerjalan = $tgl->equalTo($today);

                $label = self::formatTanggalIndonesia($tgl);
                if ($isBerjalan) {
                    $label .= ' (sampai saat ini)';
                }

                $inputState['tanggal_harian'] = $tgl->toDateString();
                break;

            case 'mingguan':
                $acuanStr = $request->input('tanggal_mingguan', $request->input('tanggal_acuan'));
                if (! $acuanStr) {
                    $acuan = $today->copy();
                } else {
                    try {
                        $acuan = Carbon::parse($acuanStr, 'Asia/Jayapura')->startOfDay();
                    } catch (\Exception $e) {
                        throw ValidationException::withMessages([
                            'tanggal_mingguan' => 'Format tanggal acuan mingguan tidak valid.',
                        ]);
                    }
                }

                if ($acuan->greaterThan($today)) {
                    throw ValidationException::withMessages([
                        'tanggal_mingguan' => 'Tanggal acuan mingguan tidak boleh melebihi hari ini.',
                    ]);
                }

                // Periode Senin sampai Minggu pada minggu tanggal tersebut
                $tanggalMulai = $acuan->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                $tanggalAkhir = $acuan->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

                // Periode sedang berjalan jika tanggal akhir >= hari ini
                $isBerjalan = $tanggalAkhir->greaterThanOrEqualTo($today);

                $label = self::formatTanggalIndonesia($tanggalMulai) . ' s/d ' . self::formatTanggalIndonesia($tanggalAkhir);
                if ($isBerjalan) {
                    $label .= ' (sampai saat ini)';
                }

                $inputState['tanggal_mingguan'] = $acuan->toDateString();
                break;

            case 'bulanan':
                $bulan = (int) $request->input('bulan', $today->month);
                $tahun = (int) $request->input('tahun', $today->year);

                if ($bulan < 1 || $bulan > 12) {
                    throw ValidationException::withMessages([
                        'bulan' => 'Bulan harus antara 1 sampai 12.',
                    ]);
                }

                if ($tahun < 2020 || $tahun > 2100) {
                    throw ValidationException::withMessages([
                        'tahun' => 'Tahun tidak valid.',
                    ]);
                }

                // Cek masa depan
                if ($tahun > $today->year || ($tahun === $today->year && $bulan > $today->month)) {
                    throw ValidationException::withMessages([
                        'bulan' => 'Periode bulan tidak boleh melebihi bulan saat ini.',
                    ]);
                }

                $tglAwalBulan = Carbon::createFromDate($tahun, $bulan, 1, 'Asia/Jayapura')->startOfDay();
                $tanggalMulai = $tglAwalBulan->copy()->startOfMonth()->startOfDay();
                $tanggalAkhir = $tglAwalBulan->copy()->endOfMonth()->endOfDay();

                $isBerjalan = ($tahun === $today->year && $bulan === $today->month);

                $namaBulan = self::getNamaBulanIndonesia($bulan);
                $label = "{$namaBulan} {$tahun}";
                if ($isBerjalan) {
                    $label .= ' (sampai saat ini)';
                }

                $inputState['bulan'] = $bulan;
                $inputState['tahun'] = $tahun;
                break;

            case 'kustom':
            default:
                $mulaiStr = $request->input('tanggal_kustom_mulai', $request->input('tanggal_kustom_awal', $request->input('tanggal_mulai', $request->input('tanggal_awal'))));
                $akhirStr = $request->input('tanggal_kustom_akhir', $request->input('tanggal_akhir'));

                if (! $mulaiStr || ! $akhirStr) {
                    throw ValidationException::withMessages([
                        'tanggal_kustom_mulai' => 'Tanggal mulai dan tanggal akhir wajib diisi untuk periode kustom.',
                    ]);
                }

                try {
                    $tglMulai = Carbon::parse($mulaiStr, 'Asia/Jayapura')->startOfDay();
                    $tglAkhir = Carbon::parse($akhirStr, 'Asia/Jayapura')->endOfDay();
                } catch (\Exception $e) {
                    throw ValidationException::withMessages([
                        'tanggal_kustom_mulai' => 'Format tanggal kustom tidak valid.',
                    ]);
                }

                if ($tglMulai->greaterThan($today)) {
                    throw ValidationException::withMessages([
                        'tanggal_kustom_mulai' => 'Tanggal mulai tidak boleh melebihi hari ini.',
                    ]);
                }

                if ($tglAkhir->copy()->startOfDay()->greaterThan($today)) {
                    throw ValidationException::withMessages([
                        'tanggal_kustom_akhir' => 'Tanggal akhir tidak boleh melebihi hari ini.',
                    ]);
                }

                if ($tglAkhir->lessThan($tglMulai)) {
                    throw ValidationException::withMessages([
                        'tanggal_kustom_akhir' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
                    ]);
                }

                $tanggalMulai = $tglMulai->copy()->startOfDay();
                $tanggalAkhir = $tglAkhir->copy()->endOfDay();
                $isBerjalan = $tanggalAkhir->copy()->startOfDay()->equalTo($today);

                $label = self::formatTanggalIndonesia($tanggalMulai) . ' s/d ' . self::formatTanggalIndonesia($tanggalAkhir);
                if ($isBerjalan) {
                    $label .= ' (sampai saat ini)';
                }

                $inputState['tanggal_kustom_mulai'] = $tanggalMulai->toDateString();
                $inputState['tanggal_kustom_akhir'] = $tanggalAkhir->toDateString();
                break;
        }

        // Hitung selisih hari inklusif
        $rentangHari = (int) $tanggalMulai->diffInDays($tanggalAkhir) + 1;

        if ($rentangHari > $maxRentangHari) {
            throw ValidationException::withMessages([
                'rentang' => "Rentang periode terpilih ({$rentangHari} hari) melebihi batas maksimal yang diizinkan ({$maxRentangHari} hari). Silakan persempit rentang tanggal.",
            ]);
        }

        return new self(
            $jenis,
            $tanggalMulai,
            $tanggalAkhir,
            $label,
            $isBerjalan,
            $rentangHari,
            $inputState
        );
    }

    /**
     * Dapatkan parameter array untuk mempertahankan filter di tautan unduh PDF atau query string.
     */
    public function toQueryParams(): array
    {
        $params = ['jenis' => $this->jenis];

        switch ($this->jenis) {
            case 'harian':
                $params['tanggal_harian'] = $this->tanggalHarian;
                break;
            case 'mingguan':
                $params['tanggal_mingguan'] = $this->tanggalMingguan;
                break;
            case 'bulanan':
                $params['bulan'] = $this->bulan;
                $params['tahun'] = $this->tahun;
                break;
            case 'kustom':
                $params['tanggal_kustom_mulai'] = $this->tanggalKustomMulai;
                $params['tanggal_kustom_akhir'] = $this->tanggalKustomAkhir;
                break;
        }

        return $params;
    }

    /**
     * Format nama bulan dalam Bahasa Indonesia.
     */
    public static function getNamaBulanIndonesia(int $bulan): string
    {
        $daftar = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $daftar[$bulan] ?? '';
    }

    /**
     * Format tanggal Indonesia (contoh: 18 Nov 2026 atau 18 November 2026).
     */
    public static function formatTanggalIndonesia(Carbon $date, bool $singkat = true): string
    {
        $bulanSingkat = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $d = $date->day;
        $m = $singkat ? $bulanSingkat[$date->month] : self::getNamaBulanIndonesia($date->month);
        $y = $date->year;

        return "{$d} {$m} {$y}";
    }

    /**
     * Format slug tanggal untuk nama berkas unduhan PDF.
     */
    public function toFileDateSlug(): string
    {
        return $this->tanggalMulai->toDateString() . '_sd_' . $this->tanggalAkhir->toDateString();
    }
}
