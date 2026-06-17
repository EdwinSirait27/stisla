<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Fingerprintrecap;
use App\Models\Roster;
use App\Models\Stores;
use App\Models\Fingerprintrecaparchive;
use Carbon\Carbon;
use App\Services\FingerprintRecapCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class FingerprintrecapController extends Controller
{
    private const TOLERANSI_TINGGI_STORES = [
        'Head Office',
        'Holding',
        'Distribution Center',
    ];
    private const TOLERANSI_TINGGI_MENIT = 10;
    private const TOLERANSI_NORMAL_MENIT = 5;
    public function index()
    {
        Log::info('FingerprintRecap@index dipanggil');
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');
        return view('pages.FingerprintRecap.FingerprintRecap', compact('stores'));
    }
    public function getData(Request $request)
    {


        Log::info('FingerprintRecap@getData dipanggil', [
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'store_name' => $request->input('store_name'),
        ]);
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'store_name' => 'nullable|string|max:100',
        ], [
            'end_date.after_or_equal' => 'End date harus setelah atau sama dengan start date.',
            'store_name.max'          => 'Nama store terlalu panjang.',
        ]);
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
            $endDate   = $request->input('end_date', Carbon::now()->toDateString());
            $storeName = $request->input('store_name');

            // ── 1. Ambil semua employees + status_employee ──
            //         $employeesQuery = Employee::with('store:id,name')
            //             ->select('id', 'employee_name', 'store_id')
            //             ->whereNotNull('pin')
            // ->whereIn('status', ['Active', 'Mutation', 'Pending'])
            //             ->whereNull('deleted_at');
            // ❌ Lama
            $employeesQuery = Employee::with('store:id,name')
                ->select('id', 'employee_name', 'store_id')
                ->whereNotNull('pin')
                ->whereIn('status', ['Active', 'Mutation', 'Pending'])
                ->whereNull('deleted_at');

            // ✅ Baru — pivot
            $employeesQuery = Employee::with([
                'store' => fn($q) => $q->wherePivot('is_primary', true),
            ])
                ->select('id', 'employee_name', 'status_employee') // ← hapus store_id
                ->whereNotNull('pin')
                ->whereIn('status', ['Active', 'Mutation', 'Pending', 'On Leave', 'Resign'])
                ->whereNull('deleted_at');
            if ($storeName) {
                $employeesQuery->whereHas('store', fn($q) => $q->where('stores_tables.name', $storeName));
            }

            $employees = $employeesQuery->get();

            // ── Ambil arsip untuk periode ini, key by employee_id ──
            $archives = Fingerprintrecaparchive::where('period_start', $startDate)
                ->where('period_end', $endDate)
                ->get()
                ->keyBy('employee_id');

            $periodIn  = Carbon::parse($startDate)->format('d-m-Y');
            $periodOut = Carbon::parse($endDate)->format('d-m-Y');

            // ── Gabung: tiap karyawan, pakai arsip kalau ada, kalau tidak tampil 0 ──
            $result = $employees->map(function ($employee) use ($archives, $periodIn, $periodOut) {
                $archive = $archives->get($employee->id);

                return [
                    'employee_name'    => $employee->employee_name ?? '-',
                    // 'store_name'       => optional($employee->store)->name ?? '-',
                    'store_name' => $employee->store->first()?->name ?? '-',
                    'total_hari'       => ($archive->total_hari_kerja ?? 0) . ' hari',
                    'total_hari_telat' => ($archive->total_hari_telat ?? 0) . ' hari',
                    'remarks'          => $archive->remarks ?? '-',
                    'period_in'        => $periodIn,
                    'period_out'       => $periodOut,
                ];
            });
            return DataTables::of($result)->make(true);
        } catch (\Exception $e) {
            Log::error('FingerprintRecap@getData ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ]);
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
