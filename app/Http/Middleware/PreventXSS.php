<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventXSS
{
    protected $unwantedAttributes = [
        'on\w+',
        'style',
        'xmlns',
        'formaction',
        'form',
        'xlink:href',
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            // Menghapus tag HTML atau script
            $value = strip_tags($value);
        });

        $request->merge($input);

        return $next($request);
    }
}

