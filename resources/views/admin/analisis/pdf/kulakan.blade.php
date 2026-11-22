<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Belanja Stok - {{ $kios->nama_kios ?? 'Stokify' }}</title>
    <style>
        @page {
            margin: 28px 32px 38px 32px;
            size: a4 portrait;
        }

        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #1a202c;
            line-height: 1.35;
        }

        /* Kop Dokumen */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #166534;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .kios-title {
            font-size: 15pt;
            font-weight: bold;
            color: #111827;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kios-subtitle {
            font-size: 8pt;
            color: #4a5568;
            margin: 2px 0 0 0;
        }

        .doc-title {
            font-size: 11pt;
            font-weight: bold;
            color: #166534;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
        }

        .doc-meta {
            font-size: 8pt;
            color: #4a5568;
            text-align: right;
            margin: 2px 0 0 0;
        }

        /* Banner Ringkasan Angka */
        .summary-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 14px;
        }

        .summary-table {
            width: 100%;
        }

        .summary-label {
            font-size: 8pt;
            color: #166534;
            text-transform: uppercase;
            font-weight: bold;
        }

        .summary-val {
            font-size: 11pt;
            font-weight: bold;
            color: #14532d;
        }

        /* Tabel Data Barang */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table tr {
            page-break-inside: avoid;
        }

        table.data-table th {
            background-color: #166534;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 6px 8px;
            border: 1px solid #166534;
        }

        table.data-table td {
            padding: 5px 8px;
            font-size: 8.5pt;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #edf2f7;
            border-right: 1px solid #edf2f7;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.data-table tfoot td {
            background-color: #f1f5f9;
            font-weight: bold;
            border-top: 2px solid #166534;
            border-bottom: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9pt;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'DejaVu Sans', monospace; }
        .text-muted { color: #64748b; font-size: 7.5pt; }

        .catatan-kaki {
            margin-top: 14px;
            font-size: 7.5pt;
            color: #64748b;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
        }
    </style>
</head>
<body>

    <!-- KOP DOKUMEN -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top; width: 60%;">
                <h1 class="kios-title">{{ $kios->nama_kios ?? 'Stokify Kios' }}</h1>
                <p class="kios-subtitle">
                    {{ $kios->alamat ?? 'Alamat kios operasional' }}
                    @if($kios->no_telepon) • Telp: {{ $kios->no_telepon }} @endif
                </p>
            </td>
            <td style="vertical-align: top; width: 40%;">
                <h2 class="doc-title">Daftar Belanja Stok</h2>
                <p class="doc-meta">Periode Analisis: <strong>{{ $periode }} Hari Terakhir</strong></p>
                <p class="doc-meta">Tanggal Cetak: {{ $waktuCetak }}</p>
                <p class="doc-meta">Dicetak oleh: {{ $pencetak }}</p>
            </td>
        </tr>
    </table>

    <!-- BANNER RINGKASAN REKOMENDASI -->
    <div class="summary-box">
        <table class="summary-table">
            <tr>
                <td style="width: 50%;">
                    <div class="summary-label">Total Barang Perlu Restok</div>
                    <div class="summary-val">{{ count($items) }} Barang</div>
                </td>
                <td style="width: 50%; text-align: right;">
                    <div class="summary-label">Total Estimasi Modal Belanja</div>
                    <div class="summary-val">{{ rupiah($totalEstimasiBiaya) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- TABEL DAFTAR BARANG YANG PERLU DIRESTOK -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 14%;">Kode</th>
                <th style="width: 32%;">Nama Barang</th>
                <th style="width: 11%;" class="text-center">Sisa Stok</th>
                <th style="width: 13%;" class="text-center">Rekomendasi</th>
                <th style="width: 12%;" class="text-right">Harga Modal</th>
                <th style="width: 13%;" class="text-right">Estimasi Biaya</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $row)
                <tr>
                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                    <td class="font-mono" style="font-weight: bold; color: #1e293b;">
                        {{ $row['kode'] }}
                    </td>
                    <td>
                        <strong style="color: #0f172a;">{{ $row['nama'] }}</strong>
                        <div class="text-muted">{{ $row['kategori_nama'] }}</div>
                    </td>
                    <td class="text-center font-mono">
                        {{ $row['stok'] }} <span class="text-muted">{{ $row['satuan'] }}</span>
                    </td>
                    <td class="text-center font-mono" style="font-weight: bold; color: #166534;">
                        +{{ $row['jumlah_rekomendasi'] }} <span class="text-muted" style="font-weight: normal;">{{ $row['satuan'] }}</span>
                    </td>
                    <td class="text-right font-mono">
                        {{ rupiah($row['harga_modal']) }}
                    </td>
                    <td class="text-right font-mono" style="font-weight: bold; color: #0f172a;">
                        {{ rupiah($row['estimasi_biaya']) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 24px; color: #64748b;">
                        Tidak ada barang yang memerlukan restok saat ini. Seluruh stok barang berada di atas titik pemesanan ulang.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if (count($items) > 0)
            <tfoot>
                <tr>
                    <td colspan="4" style="text-transform: uppercase;">TOTAL KEBUTUHAN ESTIMASI BELANJA STOK</td>
                    <td class="text-center font-mono" style="color: #14532d;">
                        {{ $items->sum('jumlah_rekomendasi') }} unit
                    </td>
                    <td></td>
                    <td class="text-right font-mono" style="color: #14532d; font-size: 10pt;">
                        {{ rupiah($totalEstimasiBiaya) }}
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="catatan-kaki">
        <p><strong>Catatan:</strong> Dokumen ini merupakan rekomendasi otomatis berdasarkan model Titik Pesan Ulang (Reorder Point) dengan laju penjualan {{ $periode }} hari terakhir. Harga modal menggunakan snapshot harga terkini di sistem. Kuantitas akhir dan supplier dapat disesuaikan pada nota Barang Masuk resmi.</p>
    </div>

    <!-- NOMOR HALAMAN OTOMATIS DOMPDF VIA SCRIPT PHP -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("DejaVu Sans", "normal");
            $size = 8;
            $color = array(0.4, 0.45, 0.5);
            $y = $pdf->get_height() - 24;

            // Kiri: Tanda tangan sistem
            $pdf->text(32, $y, "Dokumen resmi aplikasi Stokify POS • Dicetak otomatis", $font, $size, $color);

            // Kanan: Nomor halaman dinamis
            $pageText = "Halaman " . $PAGE_NUM . " dari " . $PAGE_COUNT;
            $width = $fontMetrics->getTextWidth($pageText, $font, $size);
            $pdf->text($pdf->get_width() - 32 - $width, $y, $pageText, $font, $size, $color);
        }
    </script>
</body>
</html>
