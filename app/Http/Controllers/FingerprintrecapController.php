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
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FingerprintRecapExport;


class FingerprintrecapController extends Controller
{
    private const TOLERANSI_TINGGI_STORES = [
        'Head Office',
        'Holding',
        'Distribution Center',
    ];
    private const TOLERANSI_TINGGI_MENIT = 10;
    private const TOLERANSI_NORMAL_MENIT = 5;
    // public function index()
    // {
    //     Log::info('FingerprintRecap@index dipanggil');
    //     $stores = Stores::select('id', 'name')
    //         ->whereNotNull('name')
    //         ->distinct()
    //         ->orderBy('name')
    //         ->pluck('name');
    //     return view('pages.FingerprintRecap.FingerprintRecap', compact('stores'));
    // }
    /** Status karyawan yang ikut direkap (dipakai halaman & export supaya isinya sama) */
    public const STATUSES = ['Active', 'Inactive', 'Mutation', 'Pending', 'On Leave', 'Resign'];

    public function index()
    {
        $stores = Stores::select('stores_tables.id', 'stores_tables.name')
            ->whereNotNull('stores_tables.name')
            ->whereExists(fn($q) => $q->select(DB::raw(1))->from('employee_stores')
                ->whereColumn('employee_stores.store_id', 'stores_tables.id')
                ->where('employee_stores.is_primary', true))
            ->distinct()
            ->orderBy('stores_tables.name')
            ->pluck('stores_tables.name');
        $statuses = self::STATUSES;

        // Periode yang sudah pernah direkap (arsip), terbaru dulu
        $periods = Fingerprintrecaparchive::selectRaw('period_start, period_end, COUNT(*) as total, MAX(updated_at) as last_at')
            ->groupBy('period_start', 'period_end')
            ->orderByDesc('period_start')
            ->limit(24)
            ->get()
            ->map(fn($p) => [
                'start'   => Carbon::parse($p->period_start)->toDateString(),
                'end'     => Carbon::parse($p->period_end)->toDateString(),
                'label'   => Carbon::parse($p->period_start)->locale('id')->translatedFormat('d M Y') . ' sampai ' . Carbon::parse($p->period_end)->locale('id')->translatedFormat('d M Y'),
                'total'   => (int) $p->total,
                'last_at' => $p->last_at ? Carbon::parse($p->last_at)->timezone('Asia/Makassar')->format('d M Y H:i') : null,
            ]);

        // Default: periode terakhir yang sudah direkap; kalau belum ada, periode payroll berjalan (26–25)
        $first = today()->startOfMonth();
        $defaultStart = $periods->first()['start'] ?? $first->copy()->subMonthNoOverflow()->day(26)->toDateString();
        $defaultEnd   = $periods->first()['end'] ?? $first->copy()->day(25)->toDateString();

        return view('pages.FingerprintRecap.FingerprintRecap', compact('stores', 'statuses', 'periods', 'defaultStart', 'defaultEnd'));
    }

    public function getData(Request $request)
    {
        $request->validate([
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'store_name'  => 'nullable|string|max:100',
            'status_name' => 'nullable|string|max:100',
        ], [
            'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'store_name.max'          => 'Nama lokasi terlalu panjang.',
        ]);

        try {
            $startDate  = Carbon::parse($request->input('start_date', today()->startOfMonth()))->toDateString();
            $endDate    = Carbon::parse($request->input('end_date', today()))->toDateString();
            $storeName  = $request->input('store_name');
            $statusName = $request->input('status_name');

            $employeesQuery = Employee::with(['store' => fn($q) => $q->wherePivot('is_primary', true)])
                ->select('id', 'employee_name', 'employee_pengenal', 'status_employee', 'status')
                ->whereNotNull('pin')
                ->whereIn('status', self::STATUSES)
                ->whereNull('deleted_at');

            if ($storeName) {
                $employeesQuery->whereHas('store', fn($q) => $q->where('stores_tables.name', $storeName)->where('employee_stores.is_primary', true));
            }
            if ($statusName) {
                $employeesQuery->where('status', $statusName);
            }

            $employees = $employeesQuery->get();

            // Arsip hasil "Rekap absensi" untuk periode yang persis sama
            $archives = Fingerprintrecaparchive::whereDate('period_start', $startDate)
                ->whereDate('period_end', $endDate)
                ->get()
                ->keyBy('employee_id');

            $result = $employees->map(function ($employee) use ($archives) {
                $archive = $archives->get($employee->id);
                return [
                    'employee_name'     => $employee->employee_name ?? '-',
                    'employee_pengenal' => $employee->employee_pengenal ?? '-',
                    'status_employee'   => $employee->status_employee ?? '-',
                    'store_name'        => $employee->store->first()?->name ?? ($archive->store_name ?? '-'),
                    'status_name'       => $employee->status ?? '-',
                    'has_archive'       => (bool) $archive,
                    'total_hari_num'    => (int) ($archive->total_hari_kerja ?? 0),
                    'total_telat_num'   => (int) ($archive->total_hari_telat ?? 0),
                    // nama lama tetap dikirim
                    'total_hari'        => ($archive->total_hari_kerja ?? 0) . ' hari',
                    'total_hari_telat'  => ($archive->total_hari_telat ?? 0) . ' hari',
                    'remarks'           => $archive->remarks ?? '-',
                    'archived_at'       => $archive?->updated_at?->timezone('Asia/Makassar')->format('d M Y H:i'),
                ];
            });

            $archived = $result->where('has_archive', true);
            $lastArchive = $archives->max('updated_at');
            $stats = [
                'employees'   => $result->count(),
                'archived'    => $archived->count(),
                'missing'     => $result->count() - $archived->count(),
                'avg_days'    => $archived->count() ? round($archived->avg('total_hari_num'), 1) : 0,
                'late_people' => $archived->where('total_telat_num', '>', 0)->count(),
                'late_days'   => (int) $archived->sum('total_telat_num'),
                'zero_days'   => $archived->where('total_hari_num', 0)->count(),
                'last_at'     => $lastArchive ? Carbon::parse($lastArchive)->timezone('Asia/Makassar')->format('d M Y H:i') : null,
                'by'          => optional(\App\Models\User::with('employee:id,employee_name')->find($archives->sortByDesc('updated_at')->first()?->archived_by))->employee?->employee_name,
                'period'      => Carbon::parse($startDate)->locale('id')->translatedFormat('d M Y') . ' sampai ' . Carbon::parse($endDate)->locale('id')->translatedFormat('d M Y'),
            ];

            return DataTables::of($result)->with(['stats' => $stats])->make(true);
        } catch (\Exception $e) {
            Log::error('FingerprintRecap@getData ERROR', ['message' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'store_name' => 'nullable|string|max:100',
            'status_name' => 'nullable|string|max:100',
        ]);

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());
        $storeName = $request->input('store_name');
        $statusName = $request->input('status_name');

        $filename = 'fingerprint_recap_'
            . Carbon::parse($startDate)->format('dmY')
            . '_'
            . Carbon::parse($endDate)->format('dmY')
            . '.xlsx';

        return Excel::download(
            new FingerprintRecapExport($startDate, $endDate, $storeName, $statusName),
            $filename
        );
    }
}
