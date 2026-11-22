<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Mutasi & Sisa Stok - Stokify</title>
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
            font-size: 11pt;
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
            padding: 4px 4px;
            border: 1px solid #ccc;
        }
        .data-table td {
            padding: 3.5px 4px;
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
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .badge-danger {
            color: #b91c1c;
            font-weight: bold;
        }
        .badge-safe {
            color: #15803d;
        }
        .footnote {
            font-size: 7pt;
            color: #666;
            margin-top: 6px;
            line-height: 1.3;
        }
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
                <div class="header-title">Laporan Mutasi & Sisa Stok</div>
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

    <!-- Ringkasan Angka Stok -->
    <table class="summary-table">
        <tr>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Jenis Barang Terdaftar</div>
                <div class="summary-value">{{ $ringkasan['jumlah_jenis_barang'] }} item</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Total Unit Masuk **</div>
                <div class="summary-value" style="color: #0284c7;">+{{ number_format($ringkasan['total_unit_masuk'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Total Unit Terjual</div>
                <div class="summary-value" style="color: #15803d;">{{ number_format($ringkasan['total_unit_terjual'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card" style="width: 25%;">
                <div class="summary-label">Nilai Persediaan Akhir *</div>
                <div class="summary-value" style="color: #854d0e;">{{ rupiah($ringkasan['nilai_persediaan_akhir']) }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabel Mutasi dan Sisa Stok -->
    <div class="section-title">Tabel Mutasi & Sisa Stok Barang</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;" class="text-left">Kode</th>
                <th style="width: 22%;" class="text-left">Nama Barang</th>
                <th style="width: 12%;" class="text-left">Kategori</th>
                <th style="width: 7%;" class="text-center">Satuan</th>
                <th style="width: 7%;" class="text-center">Awal</th>
                <th style="width: 7%;" class="text-center">Masuk</th>
                <th style="width: 7%;" class="text-center">Terjual</th>
                <th style="width: 6%;" class="text-center">Retur</th>
                <th style="width: 6%;" class="text-center">Penyesuaian</th>
                <th style="width: 6%;" class="text-center">Akhir</th>
                <th style="width: 4%;" class="text-center">Min.</th>
                <th style="width: 6%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $b)
                <tr>
                    <td>{{ $b['kode'] }}</td>
                    <td>
                        <strong>{{ $b['nama'] }}</strong>
                        @if(!$b['is_aktif'])
                            <span style="font-size: 6pt; color: #888;">(Nonaktif)</span>
                        @endif
                    </td>
                    <td>{{ $b['kategori'] }}</td>
                    <td class="text-center">{{ $b['satuan'] }}</td>
                    <td class="text-center">{{ $b['stok_awal'] }}</td>
                    <td class="text-center" style="color: #0284c7;">{{ $b['masuk'] > 0 ? '+' . $b['masuk'] : '0' }}</td>
                    <td class="text-center" style="color: #15803d;">{{ $b['terjual'] }}</td>
                    <td class="text-center" style="color: #7e22ce;">{{ $b['retur_void'] > 0 ? '+' . $b['retur_void'] : '0' }}</td>
                    <td class="text-center">{{ $b['penyesuaian'] > 0 ? '+' . $b['penyesuaian'] : $b['penyesuaian'] }}</td>
                    <td class="text-center" style="font-weight: bold; {{ $b['stok_akhir'] <= 0 ? 'color: #dc2626;' : '' }}">
                        {{ $b['stok_akhir'] }}
                    </td>
                    <td class="text-center" style="color: #666;">{{ $b['stok_minimum'] }}</td>
                    <td class="text-center">
                        @if($b['perlu_kulakan'])
                            <span class="badge-danger">Restok</span>
                        @else
                            <span class="badge-safe">Aman</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="color: #666; padding: 8px;">Tidak ada data stok pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bagian Akhir: Daftar Belanja Stok -->
    <div style="page-break-inside: avoid; margin-top: 10px;">
        <div class="section-title" style="color: #b91c1c; border-color: #b91c1c;">Daftar Belanja Stok (Stok Menipis / Habis)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;" class="text-left">Kode</th>
                    <th class="text-left">Nama Barang</th>
                    <th style="width: 15%;" class="text-center">Stok Akhir</th>
                    <th style="width: 15%;" class="text-center">Batas Minimum</th>
                    <th style="width: 18%;" class="text-center">Kekurangan Restok</th>
                </tr>
            </thead>
            <tbody>
                @forelse($daftar_kulakan as $dk)
                    <tr>
                        <td>{{ $dk['kode'] }}</td>
                        <td><strong>{{ $dk['nama'] }}</strong></td>
                        <td class="text-center" style="color: #dc2626; font-weight: bold;">{{ $dk['stok_akhir'] }} {{ $dk['satuan'] }}</td>
                        <td class="text-center">{{ $dk['stok_minimum'] }} {{ $dk['satuan'] }}</td>
                        <td class="text-center" style="font-weight: bold; color: #b91c1c;">+{{ $dk['kekurangan'] }} {{ $dk['satuan'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center" style="color: #15803d; padding: 6px;">Seluruh stok barang dalam kondisi aman di atas batas minimum.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Catatan Kaki Dokumen -->
    <div class="footnote">
        <p>* {{ $catatan_kaki['persediaan'] }}</p>
        <p>** {{ $catatan_kaki['masuk'] }}</p>
    </div>
</body>
</html>
