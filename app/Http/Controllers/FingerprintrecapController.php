<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Fingerprintrecap;
use App\Models\Roster;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class FingerprintrecapController extends Controller
{
    /**
     * Store yang dapat toleransi 10 menit (HO/Holding/DC).
     * Selain ini → toleransi 5 menit.
     *
     * NOTE: Cek nama persis di database — sesuaikan jika perlu.
     */
    private const TOLERANSI_TINGGI_STORES = [
        'HO',
        'Head Office',
        'Holding',
        'DC',
        'Distribution Center',  // ✅ FIX: nama lengkap di database
    ];
    private const TOLERANSI_TINGGI_MENIT  = 10;
    private const TOLERANSI_NORMAL_MENIT  = 5;

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

        // ── Validasi input (defensive coding) ──
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'store_name' => 'nullable|string|max:100',
        ], [
            'start_date.date'         => 'Format start_date tidak valid.',
            'end_date.date'           => 'Format end_date tidak valid.',
            'end_date.after_or_equal' => 'End date harus setelah atau sama dengan start date.',
            'store_name.max'          => 'Nama store terlalu panjang.',
        ]);

        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
            $endDate   = $request->input('end_date', Carbon::now()->toDateString());
            $storeName = $request->input('store_name');

            // ── 1. Ambil semua employees ──
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

            // ── 3. Ambil roster Work + shift untuk hitung telat ──
            $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
                ->whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$startDate, $endDate])
                ->where('day_type', 'Work')
                ->get()
                ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

            $rostersByEmployee = $rosters->groupBy('employee_id');

            // ── 4. Build result ──
            $result = $employees->map(function ($employee) use (
                $recaps, $rosters, $rostersByEmployee
            ) {
                $employeeRecaps  = $recaps->get($employee->id, collect());
                $employeeRosters = $rostersByEmployee->get($employee->id, collect());

                $storeName = optional($employee->store)->name ?? '';
                $toleransi = in_array($storeName, self::TOLERANSI_TINGGI_STORES)
                    ? self::TOLERANSI_TINGGI_MENIT
                    : self::TOLERANSI_NORMAL_MENIT;

                // ──────────────────────────────────────────────────────
                //  HITUNG TELAT (hanya dari recap yang ADA scan-nya)
                // ──────────────────────────────────────────────────────
                $telatDates = [];

                foreach ($employeeRecaps as $recap) {
                    $dateStr = Carbon::parse($recap->date)->toDateString();

                    // ✅ FIX: Skip kalau tidak ada scan sama sekali
                    //    (ini bukan telat, ini "tidak masuk")
                    if (!$recap->time_in && !$recap->time_out) {
                        continue;
                    }

                    // Cek apakah hari ini hari kerja (roster Work)
                    $rosterKey = $employee->id . '_' . $dateStr;
                    $roster    = $rosters->get($rosterKey);

                    if (!$roster) continue; // bukan hari kerja → skip

                    // Kondisi C: scan tidak lengkap (hanya IN atau hanya OUT)
                    $scanTidakLengkap = (!$recap->time_in || !$recap->time_out);

                    // Kondisi A: scan masuk terlambat
                    $masukTerlambat = false;
                    if ($recap->time_in && $roster->shift) {
                        $shiftStart = Carbon::parse($dateStr . ' ' . $roster->shift->start_time);
                        $actualIn   = Carbon::parse($dateStr . ' ' . $recap->time_in);
                        $batasMasuk = $shiftStart->copy()->addMinutes($toleransi);

                        if ($actualIn->gt($batasMasuk)) {
                            $masukTerlambat = true;
                        }
                    }

                    if ($scanTidakLengkap || $masukTerlambat) {
                        $telatDates[] = $dateStr;
                    }
                }

                // ── Total Hari Kerja = jumlah hari dengan is_counted = 1 ──
                $totalHariKerja = $employeeRecaps->where('is_counted', 1)->count();

                // ── Total Hari Telat ──
                $totalHariTelat = count($telatDates);

                // ── Hari tidak masuk (roster Work tapi is_counted = 0 atau tidak ada di recap) ──
                $rosterDates = $employeeRosters->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();

                // Hari yang DI RECAP DAN counted = hari kerja
                $countedDates = $employeeRecaps
                    ->where('is_counted', 1)
                    ->pluck('date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->toArray();

                // Hari roster Work yang TIDAK terhitung kerja → tidak masuk
                $tidakMasukDates = array_diff($rosterDates, $countedDates);

                // ── Remarks (Opsi 3): campur semua hari bermasalah ──
                $bermasalahDates = collect(array_merge($telatDates, $tidakMasukDates))
                    ->unique()
                    ->sort()
                    ->values()
                    ->map(fn($d) => Carbon::parse($d)->format('d/m/Y'))
                    ->implode(', ');

                return [
                    'employee_name'    => $employee->employee_name ?? '-',
                    'store_name'       => $storeName ?: '-',
                    'total_hari'       => $totalHariKerja . ' hari',
                    'total_hari_telat' => $totalHariTelat . ' hari',
                    'remarks'          => $bermasalahDates ?: '-',
                    'period_in'        => Carbon::parse(request('start_date', Carbon::now()->startOfMonth()))->format('d-m-Y'),
                    'period_out'       => Carbon::parse(request('end_date', Carbon::now()))->format('d-m-Y'),
                ];
            });

            Log::info('FingerprintRecap@getData berhasil');

            return DataTables::of($result)->make(true);

        } catch (\Exception $e) {
            Log::error('FingerprintRecap@getData ERROR', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}