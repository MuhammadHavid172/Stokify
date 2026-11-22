<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Tangani permintaan masuk dan periksa kecocokan peran (role).
     *
     * Aturan:
     * - Role yang terdaftar di parameter diizinkan.
     * - Jika 'kasir' diizinkan, 'admin' juga otomatis diizinkan (admin boleh menjaga kasir).
     * - Jika tidak sesuai, kembalikan HTTP 403 dengan pesan Bahasa Indonesia.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $isAllowed = in_array($user->role, $roles);

        // Admin juga diizinkan mengakses area kasir
        if (in_array('kasir', $roles) && $user->isAdmin()) {
            $isAllowed = true;
        }

        if (! $isAllowed) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
