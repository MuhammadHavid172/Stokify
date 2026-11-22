<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // 401 - Unauthenticated (JSON)
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi login Anda telah berakhir. Silakan masuk kembali.',
                ], 401);
            }
        });

        // 403 - Forbidden (JSON)
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Akses ditolak. Anda tidak memiliki izin untuk melakukan tindakan ini.',
                ], 403);
            }
        });

        // 404 - Not Found (JSON)
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data atau halaman yang diminta tidak ditemukan.',
                ], 404);
            }
        });

        // 419 - CSRF Token Mismatch (JSON)
        $this->renderable(function (TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi keamanan (CSRF) telah habis. Keranjang belanja Anda tetap aman. Silakan muat ulang halaman.',
                ], 419);
            }
        });

        // 422 - Validation Exception (JSON)
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                $firstError = collect($e->errors())->flatten()->first();
                return response()->json([
                    'success' => false,
                    'message' => $firstError ?: 'Data yang dikirimkan tidak valid.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // 429 - Throttle / Rate Limit (JSON)
        $this->renderable(function (ThrottleRequestsException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat sebelum mencoba kembali.',
                ], 429);
            }
        });

        // General HttpException with status codes (JSON)
        $this->renderable(function (HttpException $e, $request) {
            if ($request->expectsJson()) {
                if ($e->getStatusCode() === 419) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesi keamanan telah habis. Keranjang belanja Anda tetap aman. Silakan muat ulang halaman.',
                    ], 419);
                }
                if ($e->getStatusCode() === 429) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terlalu banyak permintaan. Silakan tunggu beberapa saat.',
                    ], 429);
                }
            }
        });

        // 500 - Server Error (JSON) tanpa membocorkan detail teknis
        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof HttpResponseException) {
                return $e->getResponse();
            }

            if ($request->expectsJson()) {
                if (! ($e instanceof ValidationException 
                    || $e instanceof HttpException 
                    || $e instanceof AuthenticationException 
                    || $e instanceof TokenMismatchException)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Terjadi kesalahan sistem internal pada server. Silakan coba beberapa saat lagi.',
                    ], 500);
                }
            }
        });
    }
}
