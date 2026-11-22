<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 1. Rate limiter registrasi kios: 5 per jam per IP
        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(5)->by($request->ip())->response(function () {
                return response()->view('errors.429', [
                    'message' => 'Terlalu banyak permintaan pendaftaran. Silakan coba lagi 1 jam kemudian.'
                ], 429);
            });
        });

        // 2. Rate limiter pencarian barang kasir: 120 per menit per user
        RateLimiter::for('kasir.barang.cari', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak pencarian barang. Silakan tunggu beberapa saat.',
                ], 429);
            });
        });

        // 3. Rate limiter penyimpanan penjualan kasir: 60 per menit per user
        RateLimiter::for('kasir.penjualan.store', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak transaksi dikirim dalam waktu singkat. Silakan tunggu sejenak.',
                ], 429);
            });
        });

        // 4. Rate limiter unduh PDF: 20 per menit per user
        RateLimiter::for('unduh.pdf', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->view('errors.429', [
                    'message' => 'Terlalu banyak permintaan unduh dokumen PDF. Silakan tunggu 1 menit sebelum mengunduh kembali.'
                ], 429);
            });
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
