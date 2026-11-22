<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Ringkasan Keuangan - Stokify</title>
    <style>
        @page {
            margin: 15mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5pt;
            line-height: 1.35;
            color: #222;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #166534;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }
        .header-logo {
            font-size: 16pt;
            font-weight: bold;
            color: #166534;
            letter-spacing: 1px;
        }
        .header-title {
            font-size: 13pt;
            font-weight: bold;
            color: #111;
            margin-top: 2px;
        }
        .header-meta {
            font-size: 8pt;
            color: #555;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .summary-card {
            border: 1px solid #d0d7d5;
            background-color: #f7faf9;
            padding: 8px 10px;
            vertical-align: top;
        }
        .summary-label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #444;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 12pt;
            font-weight: bold;
            color: #166534;
            margin-top: 3px;
        }
        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #166534;
            border-bottom: 1px solid #166534;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table thead {
            display: table-header-group;
        }
        .data-table th {
            background-color: #eaf1ee;
            color: #166534;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 6px;
            border: 1px solid #ccc;
        }
        .data-table td {
            padding: 4.5px 6px;
            border: 1px solid #e0e0e0;
            font-size: 8pt;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .data-table tr.total-row {
            background-color: #f2f6f4;
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-danger { color: #c026d3; }
        .negative-num { color: #dc2626; font-weight: bold; }
        .footnote {
            font-size: 7pt;
            color: #666;
            margin-top: 8px;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}   |   Dicetak oleh {{ $kepala['nama_pencetak'] }} pada {{ $kepala['waktu_cetak'] }} - Stokify";
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $size = 7.5;
            $color = array(0.35, 0.35, 0.35);
            $pdf->page_text(42, $pdf->get_height() - 30, $text, $font, $size, $color);
        }
    </script>

    <!-- Header Dokumen -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="header-logo">STOKIFY</div>
                <div class="header-title">Laporan Ringkasan Keuangan</div>
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

    <!-- Ringkasan Angka Utama -->
    <table class="summary-table">
        <tr>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Omzet Penjualan</div>
                <div class="summary-value">{{ rupiah($ringkasan['omzet']) }}</div>
                <div style="font-size: 7pt; color: #666; margin-top: 2px;">{{ $ringkasan['jumlah_transaksi'] }} transaksi selesai</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">HPP (Modal Terjual)</div>
                <div class="summary-value" style="color: #854d0e;">{{ rupiah($ringkasan['hpp']) }}</div>
                <div style="font-size: 7pt; color: #666; margin-top: 2px;">Snapshot saat transaksi</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Laba Kotor *</div>
                <div class="summary-value" style="color: #15803d;">{{ rupiah($ringkasan['laba_kotor']) }}</div>
                <div style="font-size: 7pt; color: #666; margin-top: 2px;">Omzet dikurangi HPP</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Margin Laba Kotor</div>
                <div class="summary-value" style="color: #0369a1;">{{ $ringkasan['margin_laba_kotor'] }}</div>
                <div style="font-size: 7pt; color: #666; margin-top: 2px;">Rata-rata: {{ rupiah($ringkasan['rata_rata_transaksi']) }}</div>
            </td>
        </tr>
    </table>

    <!-- Informasi Pendukung Operasional -->
    <table class="data-table" style="margin-bottom: 14px;">
        <thead>
            <tr>
                <th class="text-left">Parameter Pendukung</th>
                <th class="text-right">Nominal / Keterangan</th>
                <th class="text-left">Catatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Barang Masuk</td>
                <td class="text-right" style="font-weight: bold;">{{ rupiah($ringkasan['total_kulakan']) }}</td>
                <td style="color: #666;">Transaksi pengadaan barang masuk berstatus selesai</td>
            </tr>
            <tr>
                <td>Kerugian Penyesuaian Stok</td>
                <td class="text-right" style="color: #dc2626; font-weight: bold;">{{ rupiah($ringkasan['kerugian_stok']) }}</td>
                <td style="color: #666;">Nilai kerugian barang rusak, hilang, atau selisih opname</td>
            </tr>
            <tr style="background-color: #f7faf9; font-weight: bold;">
                <td>Laba Kotor Setelah Kerugian Stok **</td>
                <td class="text-right" style="color: #166534; font-size: 9pt;">{{ rupiah($ringkasan['laba_kotor_setelah_kerugian_stok']) }}</td>
                <td style="color: #15803d;">Laba kotor dikurangi total kerugian stok fisik</td>
            </tr>
            @if($ringkasan['jumlah_nota_batal'] > 0)
                <tr>
                    <td>Nota Dibatalkan</td>
                    <td class="text-right" style="color: #666;">{{ $ringkasan['jumlah_nota_batal'] }} nota ({{ rupiah($ringkasan['nilai_nota_batal']) }})</td>
                    <td style="color: #666;">Tidak dihitung ke dalam omzet maupun laba</td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Rincian Penjualan per Hari -->
    <div class="section-title">Rincian Penjualan per Hari</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;" class="text-left">Tanggal</th>
                <th style="width: 15%;" class="text-center">Jumlah Transaksi</th>
                <th style="width: 20%;" class="text-right">Omzet</th>
                <th style="width: 20%;" class="text-right">HPP</th>
                <th style="width: 20%;" class="text-right">Laba Kotor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rincian_harian as $row)
                <tr>
                    <td>{{ $row['tanggal_label'] }}</td>
                    <td class="text-center">{{ $row['jumlah_transaksi'] }}</td>
                    <td class="text-right">{{ rupiah($row['omzet']) }}</td>
                    <td class="text-right">{{ rupiah($row['hpp']) }}</td>
                    <td class="text-right" style="font-weight: bold;">{{ rupiah($row['laba_kotor']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 10px; color: #666;">
                        Tidak ada transaksi penjualan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rincian_harian) > 0)
            <tfoot>
                <tr class="total-row">
                    <td>TOTAL PERIODE</td>
                    <td class="text-center">{{ $total_rincian['jumlah_transaksi'] }}</td>
                    <td class="text-right">{{ rupiah($total_rincian['omzet']) }}</td>
                    <td class="text-right">{{ rupiah($total_rincian['hpp']) }}</td>
                    <td class="text-right">{{ rupiah($total_rincian['laba_kotor']) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Rekap Kasir & 10 Barang Terlaris -->
    <div style="page-break-inside: avoid; margin-top: 10px;">
        <div class="section-title">Rekapitulasi Penjualan per Kasir</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">Nama Kasir</th>
                    <th class="text-center" style="width: 25%;">Jumlah Transaksi</th>
                    <th class="text-right" style="width: 30%;">Total Omzet</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekap_kasir as $rk)
                    <tr>
                        <td style="font-weight: bold;">{{ $rk['nama_kasir'] }}</td>
                        <td class="text-center">{{ $rk['jumlah_transaksi'] }}</td>
                        <td class="text-right">{{ rupiah($rk['omzet']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center" style="color: #666;">Tidak ada transaksi kasir pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 10 Barang Paling Banyak Terjual -->
    <div style="page-break-inside: avoid; margin-top: 10px;">
        <div class="section-title">10 Barang Paling Banyak Terjual</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;" class="text-left">Kode</th>
                    <th class="text-left">Nama Barang</th>
                    <th style="width: 15%;" class="text-center">Jumlah Terjual</th>
                    <th style="width: 20%;" class="text-right">Total Omzet</th>
                    <th style="width: 20%;" class="text-right">Laba Kotor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sepuluh_paling_laku as $b)
                    <tr>
                        <td>{{ $b['kode'] }}</td>
                        <td style="font-weight: bold;">{{ $b['nama'] }}</td>
                        <td class="text-center">{{ $b['jumlah'] }} {{ $b['satuan'] }}</td>
                        <td class="text-right">{{ rupiah($b['omzet']) }}</td>
                        <td class="text-right">{{ rupiah($b['laba_kotor']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="color: #666;">Tidak ada penjualan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 10 Barang dengan Laba Kotor Terbesar -->
    <div style="page-break-inside: avoid; margin-top: 10px;">
        <div class="section-title">10 Barang dengan Laba Kotor Terbesar</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;" class="text-left">Kode</th>
                    <th class="text-left">Nama Barang</th>
                    <th style="width: 15%;" class="text-center">Jumlah Terjual</th>
                    <th style="width: 20%;" class="text-right">Total Omzet</th>
                    <th style="width: 20%;" class="text-right">Laba Kotor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sepuluh_laba_terbesar as $b)
                    <tr>
                        <td>{{ $b['kode'] }}</td>
                        <td style="font-weight: bold;">{{ $b['nama'] }}</td>
                        <td class="text-center">{{ $b['jumlah'] }} {{ $b['satuan'] }}</td>
                        <td class="text-right">{{ rupiah($b['omzet']) }}</td>
                        <td class="text-right" style="font-weight: bold; color: #15803d;">{{ rupiah($b['laba_kotor']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="color: #666;">Tidak ada penjualan pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Catatan Kaki Dokumen -->
    <div class="footnote">
        <p>* {{ $catatan_kaki['laba_kotor'] }}</p>
        <p>** {{ $catatan_kaki['laba_setelah_kerugian'] }}</p>
    </div>
</body>
</html>
