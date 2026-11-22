<?php

if (! function_exists('rupiah')) {
    /**
     * Format angka menjadi format Rupiah Indonesia (contoh: Rp 12.500 atau -Rp 5.000 jika negatif).
     */
    function rupiah(mixed $angka): string
    {
        $val = (float) ($angka ?? 0);
        if ($val < 0) {
            return '-Rp ' . number_format(abs($val), 0, ',', '.');
        }
        return 'Rp ' . number_format($val, 0, ',', '.');
    }
}
