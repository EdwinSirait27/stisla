<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Leavebalance;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // ─── Saldo cuti milik user yang login ────────────────────
        //     Untuk dropdown jenis cuti di modal pengajuan
        //     Hanya tampilkan saldo tahun ini & yang masih > 0
        $leaveBalances = Leavebalance::with('leaves')
            ->where('employee_id', Auth::user()->employee_id)
            ->where('year', date('Y'))
            ->where('balance_days', '>', 0)
            ->get();

        return view('pages.Dashboard.Dashboard', compact('leaveBalances'));
    }
}