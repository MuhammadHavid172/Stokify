<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    /**
     * Pastikan akun pengguna dan kios dalam status aktif.
     * Jika tidak aktif, lakukan logout paksa dan kembalikan ke halaman login dengan pesan Bahasa Indonesia.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Segarkan data user dan relasi kios dari database untuk memastikan status teranyar
            $user->refresh();

            // 1. Cek apakah user berstatus aktif
            if (! $user->is_aktif) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'username' => 'Akun Anda dinonaktifkan. Hubungi admin kios.',
                ]);
            }

            // 2. Cek apakah kios tempat user bernaung berstatus aktif
            if ($user->kios && $user->kios->status !== 'aktif') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'username' => 'Kios Anda sedang dinonaktifkan.',
                ]);
            }
        }

        return $next($request);
    }
}
