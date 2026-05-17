<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Structuresnew;

class ManagerStoreOnly
{
    /**
     * Handle an incoming request.
     *
     * Akses diizinkan hanya jika employee terdaftar di structuresnew
     * dan posisinya adalah manager (is_manager = true).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Cek user login dan punya data employee
        if (!$user || !$user->employee) {
            abort(403, 'Akses ditolak.');
        }

        $employee = $user->employee;

        // Cek employee terdaftar di structuresnew
        if (!$employee->structure_id) {
            abort(403, 'Anda belum terdaftar di struktur organisasi. Hubungi HR.');
        }

        // Cek struktur valid dan is_manager = true
        $structure = Structuresnew::find($employee->structure_id);

        if (!$structure) {
            abort(403, 'Struktur organisasi tidak ditemukan. Hubungi HR.');
        }

        if (!$structure->is_manager) {
            abort(403, 'Halaman ini hanya untuk Manager.');
        }

        return $next($request);
    }
}