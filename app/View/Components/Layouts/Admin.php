<?php

namespace App\View\Components\Layouts;

use App\Models\Barang;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

class Admin extends Component
{
    public int $stokMenipisCount = 0;
    public $stokMenipisItems = [];

    /**
     * Create a new component instance.
     */
    public function __construct(public ?string $title = null)
    {
        if (Auth::check() && Auth::user()->kios_id) {
            // Hitung efisien satu query untuk jumlah barang stok menipis kios sendiri
            $this->stokMenipisCount = Barang::stokMenipis()->count();

            // Ambil maksimal 5 barang paling kritis untuk dropdown notifikasi lonceng
            $this->stokMenipisItems = Barang::stokMenipis()
                ->with('kategori')
                ->orderBy('stok', 'asc')
                ->limit(5)
                ->get();
        }
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.admin', [
            'stokMenipisCount' => $this->stokMenipisCount,
            'stokMenipisItems' => $this->stokMenipisItems,
        ]);
    }
}
