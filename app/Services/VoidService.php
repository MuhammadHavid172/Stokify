<?php

namespace App\Services;

use App\Models\Penjualan;
use App\Models\User;

class VoidService
{
    public function __construct(protected PenjualanService $penjualanService)
    {
    }

    /**
     * Membatalkan transaksi penjualan (void).
     *
     * @param Penjualan $penjualan
     * @param string $alasan
     * @param User|null $user
     * @return Penjualan
     */
    public function batalkan(Penjualan $penjualan, string $alasan, ?User $user = null): Penjualan
    {
        return $this->penjualanService->batalkan($penjualan, $alasan, $user);
    }
}
