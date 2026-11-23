<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        // Nginx mengarahkan request terlarang ke /forbidden: tampilkan halaman 403.
        // Parameter pertama renderable harus exception-nya; Laravel memakai tipenya untuk memilih callback.
        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('forbidden')) {
                return $this->renderHttpException(new HttpException(403, 'Akses ditolak oleh server'));
            }
            return null;
        });

        // Batas percobaan (throttle) untuk API & AJAX: pesan Indonesia + kode yang dikenali aplikasi mobile.
        // Halaman web biasa tetap memakai resources/views/errors/429.blade.php.
        $this->renderable(function (ThrottleRequestsException $e, Request $request) {
            if (!$request->is('api/*') && !$request->expectsJson()) {
                return null;
            }
            $headers = $e->getHeaders();
            $retry   = max(1, (int) ($headers['Retry-After'] ?? 60));

            return response()->json([
                'success'     => false,
                'message'     => "Terlalu banyak percobaan. Coba lagi dalam {$retry} detik.",
                'code'        => 'TOO_MANY_ATTEMPTS',
                'retry_after' => $retry,
            ], 429, $headers);
        });
    }
}
