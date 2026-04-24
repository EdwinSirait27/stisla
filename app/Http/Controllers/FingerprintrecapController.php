<?php

namespace App\Http\Controllers;



use App\Models\Employee;
use App\Models\Fingerprintrecap;
use App\Models\Roster;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class FingerprintrecapController extends Controller
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

            // Ambil semua employees
            $employeesQuery = Employee::with('store:id,name')
                ->select('id', 'employee_name', 'store_id')
                ->whereNotNull('pin')
                ->whereNull('deleted_at');

            if ($storeName) {
                $employeesQuery->whereHas('store', fn($q) => $q->where('name', $storeName));
            }

            $employees   = $employeesQuery->get();
            $employeeIds = $employees->pluck('id')->toArray();

            // Ambil semua recap dalam periode
            $recaps = Fingerprintrecap::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('employee_id');

            // Ambil roster untuk hitung total hari kerja seharusnya
            $rosters = Roster::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('day_type', 'Work')
                ->get()
                ->groupBy('employee_id');

            $result = $employees->map(function ($employee) use ($recaps, $rosters, $startDate, $endDate) {
                $employeeRecaps  = $recaps->get($employee->id, collect());
                $employeeRosters = $rosters->get($employee->id, collect());

                // Total hari kerja (is_counted = 1)
                $totalHariKerja = $employeeRecaps->where('is_counted', 1)->count();

                // Total hari seharusnya masuk (dari roster day_type = Work)
                $totalHariSeharusnya = $employeeRosters->count();

                // Total hari telat = hari seharusnya - hari terhitung
                $totalHariTelat = max(0, $totalHariSeharusnya - $totalHariKerja);

                // Remarks — hari-hari yang is_counted = 0 tapi roster = Work
                $rosterDates  = $employeeRosters->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();
                $countedDates = $employeeRecaps->where('is_counted', 1)
                    ->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();

                $telatDates = array_diff($rosterDates, $countedDates);
                $remarks = collect($telatDates)
                    ->sort()
                    ->map(fn($d) => Carbon::parse($d)->format('d/m/Y'))
                    ->implode(', ');

                return [
                    'employee_name'    => $employee->employee_name ?? '-',
                    'store_name'       => $employee->store->name ?? '-',
                    'total_hari'       => $totalHariKerja . ' hari',
                    'total_hari_telat' => $totalHariTelat . ' hari',
                    'remarks'          => $remarks ?: '-',
                    'period_in'        => Carbon::parse($startDate)->format('d-m-Y'),
                    'period_out'       => Carbon::parse($endDate)->format('d-m-Y'),
                ];
            });

            Log::info('FingerprintRecap@getData berhasil');

            return DataTables::of($result)->make(true);

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