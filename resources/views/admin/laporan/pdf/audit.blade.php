<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Audit Transaksi & Shift - Stokify</title>
    <style>
        @page {
            margin: 12mm 12mm 15mm 12mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #222;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #166534;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .header-logo {
            font-size: 15pt;
            font-weight: bold;
            color: #166534;
            letter-spacing: 1px;
        }
        .header-title {
            font-size: 12pt;
            font-weight: bold;
            color: #111;
            margin-top: 1px;
        }
        .header-meta {
            font-size: 7.5pt;
            color: #555;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .summary-card {
            border: 1px solid #d0d7d5;
            background-color: #f7faf9;
            padding: 6px 8px;
            vertical-align: top;
        }
        .summary-label {
            font-size: 7pt;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 10.5pt;
            font-weight: bold;
            color: #166534;
            margin-top: 2px;
        }
        .section-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #166534;
            border-bottom: 1px solid #166534;
            padding-bottom: 2px;
            margin-top: 10px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .data-table thead {
            display: table-header-group;
        }
        .data-table th {
            background-color: #eaf1ee;
            color: #166534;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 5px;
            border: 1px solid #ccc;
        }
        .data-table td {
            padding: 3.5px 5px;
            border: 1px solid #e0e0e0;
            font-size: 7.5pt;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .data-table tr.total-row {
            background-color: #f2f6f4;
            font-weight: bold;
        }
        .data-table tr.row-batal {
            color: #666;
            text-decoration: line-through;
            background-color: #fdf2f2;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .negative-num { color: #dc2626; font-weight: bold; }
        .positive-num { color: #854d0e; font-weight: bold; }
        .zero-num { color: #15803d; font-weight: bold; }
    </style>
</head>
<body>
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}   |   Dicetak oleh {{ $kepala['nama_pencetak'] }} pada {{ $kepala['waktu_cetak'] }} - Stokify";
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $size = 7;
            $color = array(0.35, 0.35, 0.35);
            $pdf->page_text(35, $pdf->get_height() - 25, $text, $font, $size, $color);
        }
    </script>

    <!-- Header Dokumen -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="header-logo">STOKIFY</div>
                <div class="header-title">Laporan Audit Transaksi & Shift</div>
                <div class="header-meta">
                    <strong>{{ $kepala['nama_kios'] }}</strong> &bull; {{ $kepala['alamat'] }}
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div class="header-meta">
                    <div>Periode: <strong>{{ $kepala['periode_label'] }}</strong></div>
                    <div>Waktu Cetak: {{ $kepala['waktu_cetak'] }}</div>
                    <div>Pencetak: {{ $kepala['nama_pencetak'] }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Angka Audit -->
    <table class="summary-table">
        <tr>
            <td class="summary-card" style="width: 16%;">
                <div class="summary-label">Nota Selesai</div>
                <div class="summary-value" style="color: #15803d;">{{ $ringkasan['jumlah_nota_selesai'] }}</div>
            </td>
            <td class="summary-card" style="width: 16%;">
                <div class="summary-label">Nota Batal</div>
                <div class="summary-value" style="color: #dc2626;">{{ $ringkasan['jumlah_nota_batal'] }}</div>
            </td>
            <td class="summary-card" style="width: 26%;">
                <div class="summary-label">Total Omzet Nota Selesai</div>
                <div class="summary-value">{{ rupiah($ringkasan['total_omzet']) }}</div>
            </td>
            <td class="summary-card" style="width: 16%;">
                <div class="summary-label">Jumlah Shift</div>
                <div class="summary-value">{{ $ringkasan['jumlah_shift'] }}</div>
            </td>
            <td class="summary-card" style="width: 26%;">
                <div class="summary-label">Total Selisih Kas Shift</div>
                <div class="summary-value {{ $ringkasan['total_selisih_kas'] < 0 ? 'negative-num' : ($ringkasan['total_selisih_kas'] > 0 ? 'positive-num' : 'zero-num') }}">
                    {{ rupiah($ringkasan['total_selisih_kas']) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. Rekapitulasi Shift Kasir -->
    <div class="section-title">Rekapitulasi Shift Kasir</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 14%;" class="text-left">Kasir & Shift</th>
                <th style="width: 16%;" class="text-left">Waktu Buka / Tutup</th>
                <th style="width: 10%;" class="text-right">Modal Awal</th>
                <th style="width: 12%;" class="text-right">Penjualan Tunai</th>
                <th style="width: 12%;" class="text-right">Uang Seharusnya</th>
                <th style="width: 11%;" class="text-right">Uang Fisik</th>
                <th style="width: 11%;" class="text-right">Selisih</th>
                <th style="width: 14%;" class="text-left">Status & Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rekap_shift as $s)
                <tr>
                    <td>
                        <strong>{{ $s['kasir'] }}</strong>
                        <span style="font-size: 6.5pt; color: #666; display: block;">Shift #{{ $s['id'] }}</span>
                    </td>
                    <td>
                        {{ $s['waktu_buka'] }}<br>
                        <span style="font-size: 6.5pt; color: #666;">s/d {{ $s['waktu_tutup'] }}</span>
                    </td>
                    <td class="text-right">{{ rupiah($s['modal_awal']) }}</td>
                    <td class="text-right">{{ rupiah($s['penjualan_tunai']) }}</td>
                    <td class="text-right">
                        {{ rupiah($s['uang_seharusnya']) }}
                        @if($s['status'] === 'berjalan')
                            <span style="font-size: 6.5pt; color: #854d0e;">(sementara)</span>
                        @endif
                    </td>
                    <td class="text-right">{{ $s['uang_fisik'] !== null ? rupiah($s['uang_fisik']) : '-' }}</td>
                    <td class="text-right font-bold">
                        @if($s['selisih'] !== null)
                            <span class="{{ $s['selisih'] < 0 ? 'negative-num' : ($s['selisih'] > 0 ? 'positive-num' : 'zero-num') }}">
                                {{ rupiah($s['selisih']) }}
                            </span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($s['status'] === 'berjalan')
                            <strong style="color: #15803d;">Berjalan</strong>
                        @else
                            Ditutup
                        @endif

                        @if($s['ditutup_paksa'])
                            <span style="color: #dc2626; font-size: 6.5pt;">(Tutup paksa oleh {{ $s['ditutup_oleh'] ?? 'Admin' }})</span>
                        @endif

                        @if($s['catatan'])
                            <div style="font-size: 6.5pt; color: #555; font-style: italic;">"{{ $s['catatan'] }}"</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="color: #666; padding: 8px;">Tidak ada data shift pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rekap_shift) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="6" class="text-right">TOTAL SELISIH KAS (SHIFT DITUTUP):</td>
                    <td class="text-right {{ $total_selisih_kas < 0 ? 'negative-num' : ($total_selisih_kas > 0 ? 'positive-num' : 'zero-num') }}">
                        {{ rupiah($total_selisih_kas) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- 2. Daftar Transaksi Penjualan Lengkap -->
    <div class="section-title">Daftar Transaksi Penjualan</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 16%;" class="text-left">No. Nota</th>
                <th style="width: 14%;" class="text-left">Waktu Transaksi</th>
                <th style="width: 14%;" class="text-left">Kasir</th>
                <th style="width: 8%;" class="text-center">Item</th>
                <th style="width: 16%;" class="text-right">Total Transaksi</th>
                <th style="width: 16%;" class="text-right">HPP</th>
                <th style="width: 16%;" class="text-right">Laba Kotor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftar_nota as $p)
                <tr class="{{ $p['status'] === 'dibatalkan' ? 'row-batal' : '' }}">
                    <td>
                        {{ $p['nomor'] }}
                        @if($p['status'] === 'dibatalkan')
                            <strong style="color: #dc2626;">(DIBATALKAN)</strong>
                        @endif
                    </td>
                    <td>{{ $p['waktu_transaksi'] }}</td>
                    <td>{{ $p['kasir'] }}</td>
                    <td class="text-center">{{ $p['jumlah_item'] }}</td>
                    <td class="text-right">{{ rupiah($p['total']) }}</td>
                    <td class="text-right">{{ rupiah($p['total_modal']) }}</td>
                    <td class="text-right">{{ rupiah($p['laba_kotor']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="color: #666; padding: 8px;">Tidak ada transaksi penjualan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($daftar_nota) > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right">TOTAL NOTA SELESAI:</td>
                    <td class="text-right">{{ rupiah($total_nota['omzet']) }}</td>
                    <td class="text-right">{{ rupiah($total_nota['hpp']) }}</td>
                    <td class="text-right">{{ rupiah($total_nota['laba_kotor']) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- 3. Daftar Pembatalan Nota -->
    <div style="page-break-inside: avoid; margin-top: 10px;">
        <div class="section-title" style="color: #b91c1c; border-color: #b91c1c;">Log Pembatalan Nota</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 16%;" class="text-left">No. Nota</th>
                    <th style="width: 14%;" class="text-left">Waktu Transaksi</th>
                    <th style="width: 14%;" class="text-left">Waktu Batal</th>
                    <th style="width: 13%;" class="text-right">Nominal</th>
                    <th style="width: 13%;" class="text-left">Kasir</th>
                    <th style="width: 13%;" class="text-left">Aktor Pembatal</th>
                    <th style="width: 17%;" class="text-left">Alasan Pembatalan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar_void as $v)
                    <tr>
                        <td style="font-weight: bold; color: #b91c1c;">{{ $v['nomor'] }}</td>
                        <td>{{ $v['waktu_transaksi'] }}</td>
                        <td>{{ $v['waktu_dibatalkan'] }}</td>
                        <td class="text-right">{{ rupiah($v['total']) }}</td>
                        <td>{{ $v['kasir'] }}</td>
                        <td><strong>{{ $v['pembatal'] }}</strong></td>
                        <td style="font-style: italic;">"{{ $v['alasan'] }}"</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="color: #666; padding: 6px;">Tidak ada transaksi yang dibatalkan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
