<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * Di production aplikasi berada di belakang reverse proxy di server yang sama, jadi tanpa ini
     * $request->ip() selalu 127.0.0.1: log login salah dan batas login per IP berlaku untuk semua user.
     *
     * TRUSTED_PROXIES (pisahkan dengan koma) berisi alamat proxy yang meneruskan request,
     * default hanya localhost. Jangan isi "*" kalau server bisa diakses langsung tanpa proxy,
     * karena header X-Forwarded-For bisa dipalsukan.
     */
    public function __construct()
    {
        $list = array_values(array_filter(array_map('trim', explode(',', (string) config('app.trusted_proxies', '127.0.0.1,::1')))));
        $this->proxies = $list === ['*'] ? '*' : $list;
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
