<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Password default minimal 8 karakter di seluruh aplikasi
        Password::defaults(function () {
            return Password::min(8);
        });

        // 2. Cegah N+1 Lazy Loading saat pengembangan / testing (Tahap 11)
        Model::preventLazyLoading(! app()->isProduction());

        // 3. View Composer untuk Notifikasi Lonceng (Admin & Kasir)
        \Illuminate\Support\Facades\View::composer(['layouts.admin', 'kasir.index', 'kasir.riwayat'], function ($view) {
            $view->with([
                'notifStokMenipis' => collect(),
                'totalNotifStokMenipis' => 0,
            ]);
        });
    }
}
