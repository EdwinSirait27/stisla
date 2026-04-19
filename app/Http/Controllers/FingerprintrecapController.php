<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FingerprintRecap;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class FingerprintRecapController extends Controller
{
    public function index()
    {
        Log::info('FingerprintRecap@index dipanggil');

        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        Log::info('Stores loaded', ['total_stores' => $stores->count()]);

        return view('pages.FingerprintRecap.FingerprintRecap', compact('stores'));
    }

    public function getData(Request $request)
    {
        Log::info('FingerprintRecap@getData dipanggil', [
            'start_date' => $request->input('start_date'),
            'end_date'   => $request->input('end_date'),
            'store_name' => $request->input('store_name'),
        ]);

        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
            $endDate   = $request->input('end_date', Carbon::now()->toDateString());
            $storeName = $request->input('store_name');

            Log::info('Parameter query', [
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'store_name' => $storeName,
            ]);

            $query = DB::table('fingerprints_recap as fr')
                ->join('employees_tables as e', 'e.id', '=', 'fr.employee_id')
                ->join('stores_tables as s', 's.id', '=', 'e.store_id')
                ->leftJoin('position_tables as p', 'p.id', '=', 'e.position_id')
                ->select([
                    'e.id as employee_id',
                    'e.employee_name',
                    'e.employee_pengenal',
                    'e.status_employee',
                    's.name as store_name',
                    'p.name as position_name',
                    DB::raw('SUM(fr.is_counted) as total_hari'),
                    DB::raw("'{$startDate}' as start_date"),
                    DB::raw("'{$endDate}' as end_date"),
                ])
                ->whereBetween('fr.date', [$startDate, $endDate])
                ->whereNull('e.deleted_at')
                ->groupBy(
                    'e.id',
                    'e.employee_name',
                    'e.employee_pengenal',
                    'e.status_employee',
                    's.name',
                    'p.name'
                );

            if ($storeName) {
                $query->where('s.name', $storeName);
                Log::info('Filter store aktif', ['store_name' => $storeName]);
            }

            // Log raw SQL untuk debug query
            Log::debug('SQL Query', [
                'sql'      => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);

            $result = DataTables::of($query)
                ->addColumn('employee_name', fn($r) => $r->employee_name ?? '-')
                ->addColumn('nip', fn($r) => $r->employee_pengenal ?? '-')
                ->addColumn('store_name', fn($r) => $r->store_name ?? '-')
                ->addColumn('position_name', fn($r) => $r->position_name ?? '-')
                ->addColumn('status_employee', fn($r) => $r->status_employee ?? '-')
                ->addColumn('total_hari', fn($r) => ($r->total_hari ?? 0) . ' Hari')
                ->addColumn('start_date', fn($r) => Carbon::parse($r->start_date)->format('d-m-Y'))
                ->addColumn('end_date', fn($r) => Carbon::parse($r->end_date)->format('d-m-Y'))
                ->make(true);

            Log::info('FingerprintRecap@getData berhasil');

            return $result;

        } catch (\Exception $e) {
            Log::error('FingerprintRecap@getData ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}