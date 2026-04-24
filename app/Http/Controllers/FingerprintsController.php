<?php

namespace App\Http\Controllers;

use App\Models\Fingerprints;
use App\Models\EditedFingerprint;
use App\Models\Employee;
use App\Models\Stores;
use App\Models\Roster;
use App\Models\Fingerprintrecap;
use App\Models\Devicefingerprint;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FingerprintsController extends Controller
{
    public function index()
    {
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->distinct()
            ->pluck('name');
        return view('pages.Fingerprints.Fingerprints', compact('stores'));
    }

    public function recap(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'store_name' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        // ── 1. Ambil karyawan aktif yang punya PIN ──
        $employeesQuery = Employee::select('id', 'pin', 'store_id')
            ->select('id','pin','store_id')
            ->whereNotNull('pin')
            ->whereNull('deleted_at');

        if ($request->store_name) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('name', $request->store_name));
        }

        $employees = $employeesQuery->get()->keyBy(fn($e) => (string) $e->pin);

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'There are no employees with registered PINs.',
            ], 422);
        }

        $pins = $employees->keys()->toArray();

        // ── 2. Ambil raw scan dari DB absensi (mysql_second) ──
        $rawScans = DB::connection('mysql_second')
            ->table('att_log')
            ->select('pin', 'scan_date', 'inoutmode', 'sn')
            ->whereIn('pin', $pins)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->whereIn('inoutmode', [1, 2])
            ->orderBy('pin')
            ->orderBy('scan_date')
            ->get();

        if ($rawScans->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'There is no fingerprint data in that date range.',
            ], 422);
        }

        // ── 3. Group per PIN per tanggal → IN pertama, OUT terakhir ──
        $grouped = [];
        foreach ($rawScans as $scan) {
            $pin  = (string) $scan->pin;
            $date = Carbon::parse($scan->scan_date)->toDateString();
            $time = Carbon::parse($scan->scan_date)->format('H:i:s');
            $mode = (int) $scan->inoutmode;
            $sn   = $scan->sn ?? null;

            if (!isset($grouped[$pin][$date])) {
                $grouped[$pin][$date] = [
                    'time_in'   => null,
                    'time_out'  => null,
                    'device_sn' => $sn,
                ];
            }

            // IN → ambil PERTAMA saja
            if ($mode === 1 && $grouped[$pin][$date]['time_in'] === null) {
                $grouped[$pin][$date]['time_in'] = $time;
            }

            // OUT → selalu update → dapat yang TERAKHIR
            if ($mode === 2) {
                $grouped[$pin][$date]['time_out'] = $time;
            }
        }

        // ── 4. Ambil semua roster dalam range tanggal ──
        $employeeIds = $employees->pluck('id')->toArray();

        $rosters = Roster::with('shift')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

        // ── 4b. Ambil data edited_fingerprint dari DB absensi ──
        $editedFingerprints = DB::connection('mysql_second')
            ->table('edited_fingerprint')
            ->select('pin', 'scan_date', 'in_1', 'in_2')
            ->whereIn('pin', $pins)
            ->whereBetween('scan_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($e) => (string)$e->pin . '_' . Carbon::parse($e->scan_date)->toDateString());

        // ── 5. Simpan ke fingerprints_recap & hitung is_counted ──
        $synced  = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($grouped as $pin => $dates) {

            $employee = $employees->get($pin);

            if (!$employee) {
                $skipped++;
                $errors[] = "PIN {$pin} was not found in employee data.";
                continue;
            }

            foreach ($dates as $date => $times) {
                $timeIn   = $times['time_in'];
                $timeOut  = $times['time_out'];
                $deviceSn = $times['device_sn'];

                // ── Cek edited_fingerprint ──
                $editedKey = $pin . '_' . $date;
                $edited    = $editedFingerprints->get($editedKey);

                if ($edited) {
                    $timeIn  = $edited->in_1 ?? $timeIn;
                    $timeOut = $edited->in_2 ?? $timeOut;
                }

                // Hitung durasi kerja dalam menit
                $duration = null;
                if ($timeIn && $timeOut) {
                    $dtIn  = Carbon::parse($date . ' ' . $timeIn);
                    $dtOut = Carbon::parse($date . ' ' . $timeOut);
                    if ($dtOut->lt($dtIn)) $dtOut->addDay();
                    $duration = (int) $dtIn->diffInMinutes($dtOut);
                }

                // ── Hitung is_counted ──
                $isCounted = 0;

                if ($timeIn || $timeOut) {
                    // Jika hanya ada 1 scan (IN saja ATAU OUT saja)
                    // → otomatis terhitung 1 hari
                    if (!$timeIn || !$timeOut) {
                        $isCounted = 1;
                    } else {
                        // Ada 2 scan → cek vs roster shift
                        $rosterKey = $employee->id . '_' . $date;
                        $roster    = $rosters->get($rosterKey);

                        if ($roster && $roster->day_type === 'Work' && $roster->shift) {
                            $shiftStart = Carbon::parse($date . ' ' . $roster->shift->start_time);
                            $shiftEnd   = Carbon::parse($date . ' ' . $roster->shift->end_time);
                            $actualIn   = Carbon::parse($date . ' ' . $timeIn);
                            $actualOut  = Carbon::parse($date . ' ' . $timeOut);

                            // Handle overnight shift
                            if ($shiftEnd->lt($shiftStart)) $shiftEnd->addDay();

                            // Tentukan toleransi berdasarkan store
                            $employeeStoreName = optional($employee->store)->name?? '';
                            $toleransi = in_array($employeeStoreName, ['Holding', 'Head Office']) ? 10 : 5;

                            // Terhitung jika masuk tidak lebih dari toleransi dan pulang tepat/lebih lambat
                            if ($actualIn->lte($shiftStart->copy()->addMinutes($toleransi)) && $actualOut->gte($shiftEnd)) {
                                $isCounted = 1;
                            }
    
                        }
                    }
                }

                // Simpan ke fingerprints_recap
                Fingerprintrecap::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date'        => $date,
                    ],
                    [
                        'pin'              => $pin,
                        'time_in'          => $timeIn,
                        'time_out'         => $timeOut,
                        'duration_minutes' => $duration,
                        'is_counted'       => $isCounted,
                        'device_sn'        => $deviceSn,
                        'sync_status'      => 'Synced',
                        'synced_at'        => now(),
                        'period_in'        => $startDate->toDateString(),
                        'period_out'       => $endDate->toDateString(),
                    ]
                );

                $synced++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully summarized {$synced} attendance data. ({$skipped} unmatch PIN)",
            'synced'  => $synced,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    public function getFingerprints(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $storeName = $request->input('store_name');
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
        $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

        // ── 1. Edited fingerprint keys ──
        $editedKeys = EditedFingerprint::whereBetween('scan_date', [$startDate, $endDate])
            ->pluck('scan_date', 'pin')
            ->map(fn($date, $pin) => $pin . '_' . Carbon::parse($date)->toDateString())
            ->values()
            ->toArray();

        // ── 2. Ambil Employees ──
        $employeesQuery = Employee::with([
            'position:id,name',
            'store:id,name',
        ])
        ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'status_employee')
        ->whereNotNull('pin');

        if ($storeName) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('name', $storeName));
        }

        $employees   = $employeesQuery->get()->keyBy('pin');
        $employeeIds = $employees->pluck('id')->filter()->values()->toArray();

        // ── 3. Ambil Roster ──
        $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
            ->select('id', 'employee_id', 'shift_id', 'date', 'day_type')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

        // ── 4. Ambil Total Hari Kerja ──
        $totalHariPerEmployee = Fingerprintrecap::select('employee_id', DB::raw('SUM(is_counted) as total_hari'))
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('employee_id')
            ->pluck('total_hari', 'employee_id');

        // ── 5. Ambil Fingerprints ──
        $pins = $employees->keys()->toArray();
        $fingerprints = Fingerprints::select(['sn', 'scan_date', 'pin', 'inoutmode'])
            ->whereIn('pin', $pins)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->orderBy('pin')
            ->orderBy('scan_date')
            ->get();

        // ── 6. Load device names ──
        $deviceNames = Devicefingerprint::select('sn', 'device_name')
            ->get()
            ->keyBy('sn')
            ->map(fn($d) => $d->device_name ?? '-');

        // ── 7. Group per PIN per tanggal ──
        $grouped = $fingerprints
            ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

        // ── 8. Build result ──
        $result = $grouped->map(function ($group, $key) use (
            $employees, $totalHariPerEmployee, $editedKeys, $rosters, $deviceNames
        ) {
            $first    = $group->first();
            $pin      = $first->pin;
            $scanDate = Carbon::parse($first->scan_date)->toDateString();
            $employee = $employees->get($pin);

            if (!$employee) return null;

            $rosterKey  = $employee->id . '_' . $scanDate;
            $roster     = $rosters->get($rosterKey);
            $rosterName = '-';
            $rosterTime = '';

            if ($roster) {
                if ($roster->day_type !== 'Work') {
                    $rosterName = $roster->day_type;
                } elseif ($roster->shift) {
                    $rosterName = $roster->shift->shift_name;
                    $rosterTime = substr($roster->shift->start_time, 0, 5)
                        . ' - '
                        . substr($roster->shift->end_time, 0, 5);
                }
            }

            $totalHari = $totalHariPerEmployee->get($employee->id, 0);

            $row = [
                'pin'               => $pin,
                'employee_name'     => $employee->employee_name ?? '-',
                'status_employee'   => $employee->status_employee ?? '-',
                'employee_pengenal' => $employee->employee_pengenal ?? '-',
                'name'              => $employee->store->name ?? '-',
                'position_name'     => $employee->position->name ?? '-',
                'device_name'       => $deviceNames->get($first->sn) ?? '-',
                'scan_date'         => $scanDate,
                'total_hari'        => $totalHari . ' Hari',
                'roster_name'       => $rosterName,
                'roster_time'       => $rosterTime,
            ];

            for ($i = 1; $i <= 10; $i++) {
                $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
            }

            $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row, $deviceNames) {
                if ($mode < 1 || $mode > 10) return;

                $sorted  = $items->sortBy('scan_date');
                $times   = $sorted->pluck('scan_date')->map(fn($d) => Carbon::parse($d)->format('H:i:s'))->implode(', ');
                $devices = $sorted->map(fn($i) => $deviceNames->get($i->sn) ?? '')->implode(', ');

                $row["in_$mode"]      = $times;
                $row["device_$mode"]  = $devices;
                $row["combine_$mode"] = trim($times . ' ' . $devices);
            });
            // ================== CEK KETERLAMBATAN ==================
$firstIn = null;

if (!empty($row['in_1'])) {
    $firstIn = Carbon::parse($scanDate . ' ' . explode(', ', $row['in_1'])[0]);
}

$isLate = false;
$lateMinutes = 0;

if ($roster && $roster->shift && $firstIn) {
    $shiftStart = Carbon::parse($scanDate . ' ' . $roster->shift->start_time);

    // Tentukan toleransi berdasarkan store
    // $storeName = strtolower($employee->store->name ?? '');
   $employeeStoreName = strtolower($employee->store->name ?? '');

if (str_contains($employeeStoreName, 'Head Office') || str_contains($employeeStoreName, 'Holding') || str_contains($employeeStoreName, 'Distribution Center')) {
    $tolerance = 15;
} else {
    $tolerance = 10;
}

    // Hitung selisih menit
    // $lateMinutes = $shiftStart->diffInMinutes($firstIn, false);
$lateMinutes = max(0, $shiftStart->diffInMinutes($firstIn, false));
    if ($lateMinutes > $tolerance) {
        $isLate = true;
    }
}

$row['is_late'] = $isLate;
$row['late_minutes'] = $lateMinutes;
            

            $times = collect(range(1, 10))
                ->flatMap(function ($i) use ($row) {
                    if (!$row["in_$i"]) return [];
                    return explode(', ', $row["in_$i"]);
                })
                ->map(fn($t) => Carbon::parse($t))
                ->sort()
                ->values();

            if ($times->count() >= 2) {
                $minutes = $times->first()->diffInMinutes($times->last());
                $row['duration'] = sprintf(
                    '%d hour%s %d minute%s',
                    intdiv($minutes, 60),
                    intdiv($minutes, 60) !== 1 ? 's' : '',
                    $minutes % 60,
                    $minutes % 60 !== 1 ? 's' : ''
                );
            } else {
                $row['duration'] = 'invalid';
            }

            $row['is_updated']     = in_array($key, $editedKeys);
            $row['updated_status'] = $row['is_updated'] ? 'Updated' : 'Original';

            return $row;
        })->filter()->values();

        $stats = [
    'total'   => $result->count(),
    'ontime'  => $result->where('is_late', false)->count(),
    'late'    => $result->where('is_late', true)->count(),
    'updated' => $result->where('is_updated', true)->count(),
    'missing' => $result->filter(fn($r) => empty($r['in_1']))->count(),
];

        return DataTables::of($result)->with(['stats' => $stats])
        ->addColumn('in_1_colored', function ($row) {
    if (!$row['in_1']) return '-';

    if ($row['is_late']) {
        return '<span class="text-danger fw-bold">' . $row['in_1'] . '</span>';
    }

    return '<span class="text-success">' . $row['in_1'] . '</span>';
})
            ->addColumn('action', function ($row) {
                if ($row['is_updated']) {
                    return '<button class="btn btn-sm btn-secondary" disabled><i class="fas fa-edit"></i></button>';
                }
                $editUrl = route('pages.Fingerprints.edit', [
                    'pin'       => $row['pin'],
                    'scan_date' => $row['scan_date'],
                ]);
                return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-edit"></i>
                        </a>';
            })
            ->rawColumns(['action','in_1_colored'])
            ->make(true);
    }
    // public function getFingerprints(Request $request)
    // {
    //     ini_set('memory_limit', '1024M');
    //     set_time_limit(300);

    //     $storeName = $request->input('store_name');
    //     $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
    //     $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

    //     // ── 1. Edited fingerprint keys (lightweight) ──
    //     $editedKeys = EditedFingerprint::whereBetween('scan_date', [$startDate, $endDate])
    //         ->pluck('scan_date', 'pin')
    //         ->map(fn($date, $pin) => $pin . '_' . Carbon::parse($date)->toDateString())
    //         ->values()
    //         ->toArray();

    //     // ── 2. Ambil Employees ──
    //     $employeesQuery = Employee::with([
    //         'position:id,name',
    //         'store:id,name',
    //     ])
    //     ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'status_employee')
    //     ->whereNotNull('pin');

    //     if ($storeName) {
    //         $employeesQuery->whereHas('store', fn($q) => $q->where('name', $storeName));
    //     }

    //     $employees   = $employeesQuery->get()->keyBy('pin');
    //     $employeeIds = $employees->pluck('id')->filter()->values()->toArray();

    //     // ── 3. Ambil Roster dalam rentang tanggal ──
    //     $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
    //         ->select('id', 'employee_id', 'shift_id', 'date', 'day_type')
    //         ->whereIn('employee_id', $employeeIds)
    //         ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
    //         ->get()
    //         ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

    //     // ── 4. Ambil Total Hari Kerja per employee dari fingerprints_recap ──
    //     $totalHariPerEmployee = FingerprintRecap::select('employee_id', DB::raw('SUM(is_counted) as total_hari'))
    //         ->whereIn('employee_id', $employeeIds)
    //         ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
    //         ->groupBy('employee_id')
    //         ->pluck('total_hari', 'employee_id');

    //     // ── 5. Ambil Fingerprints dari DB absensi ──
    //     $pins = $employees->keys()->toArray();
    //     $fingerprints = Fingerprints::select(['sn', 'scan_date', 'pin', 'inoutmode'])
    //         ->whereIn('pin', $pins)
    //         ->whereBetween('scan_date', [$startDate, $endDate])
    //         ->orderBy('pin')
    //         ->orderBy('scan_date')
    //         ->get();

    //     // ── 6. Load device names ──
    //     $deviceNames = Devicefingerprint::select('sn', 'device_name')
    //         ->get()
    //         ->keyBy('sn')
    //         ->map(fn($d) => $d->device_name ?? '-');

    //     // ── 7. Group per PIN per tanggal ──
    //     $grouped = $fingerprints
    //         ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

    //     // ── 8. Build result ──
    //     $result = $grouped->map(function ($group, $key) use (
    //         $employees, $totalHariPerEmployee, $editedKeys, $rosters, $deviceNames
    //     ) {
    //         $first    = $group->first();
    //         $pin      = $first->pin;
    //         $scanDate = Carbon::parse($first->scan_date)->toDateString();
    //         $employee = $employees->get($pin);

    //         if (!$employee) return null;

    //         // Ambil roster untuk hari ini
    //         $rosterKey  = $employee->id . '_' . $scanDate;
    //         $roster     = $rosters->get($rosterKey);
    //         $rosterName = '-';
    //         $rosterTime = '';

    //         if ($roster) {
    //             if ($roster->day_type !== 'Work') {
    //                 $rosterName = $roster->day_type;
    //             } elseif ($roster->shift) {
    //                 $rosterName = $roster->shift->shift_name;
    //                 $rosterTime = substr($roster->shift->start_time, 0, 5)
    //                     . ' - '
    //                     . substr($roster->shift->end_time, 0, 5);
    //             }
    //         }


    //         // Total hari kerja per karyawan dari fingerprints_recap
    //         $totalHari = $totalHariPerEmployee->get($employee->id, 0);

    //         $row = [
    //             'pin'               => $pin,
    //             'employee_name'     => $employee->employee_name ?? '-',
    //             'status_employee'   => $employee->status_employee ?? '-',
    //             'employee_pengenal' => $employee->employee_pengenal ?? '-',
    //             'name'              => $employee->store->name ?? '-',
    //             'position_name'     => $employee->position->name ?? '-',
    //             'device_name'       => $deviceNames->get($first->sn) ?? '-',
    //             'scan_date'         => $scanDate,
    //             'total_hari'        => $totalHari . ' Hari',
    //             'roster_name'       => $rosterName,
    //             'roster_time'       => $rosterTime,
    //         ];

    //         for ($i = 1; $i <= 10; $i++) {
    //             $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
    //         }

    //         $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row, $deviceNames) {
    //             if ($mode < 1 || $mode > 10) return;

    //             $sorted = $items->sortBy('scan_date');

    //             $times = $sorted->pluck('scan_date')
    //                 ->map(fn($d) => Carbon::parse($d)->format('H:i:s'))
    //                 ->implode(', ');

    //             $devices = $sorted
    //                 ->map(fn($i) => $deviceNames->get($i->sn) ?? '')
    //                 ->implode(', ');

    //             $row["in_$mode"]      = $times;
    //             $row["device_$mode"]  = $devices;
    //             $row["combine_$mode"] = trim($times . ' ' . $devices);
    //         });
            

    //         // Hitung durasi
    //         $times = collect(range(1, 10))
    //             ->flatMap(function ($i) use ($row) {
    //                 if (!$row["in_$i"]) return [];
    //                 return explode(', ', $row["in_$i"]);
    //             })
    //             ->map(fn($t) => Carbon::parse($t))
    //             ->sort()
    //             ->values();

    //         if ($times->count() >= 2) {
    //             $start   = $times->first();
    //             $end     = $times->last();
    //             $minutes = $start->diffInMinutes($end);

    //             $row['duration'] = sprintf(
    //                 '%d hour%s %d minute%s',
    //                 intdiv($minutes, 60),
    //                 intdiv($minutes, 60) !== 1 ? 's' : '',
    //                 $minutes % 60,
    //                 $minutes % 60 !== 1 ? 's' : ''
    //             );
    //         } else {
    //             $row['duration'] = 'invalid';
    //         }

    //         $row['is_updated']     = in_array($key, $editedKeys);
    //         $row['updated_status'] = $row['is_updated'] ? 'Updated' : 'Original';

    //         return $row;
    //     })->filter()->values();

    //     return DataTables::of($result)
    //         ->addColumn('action', function ($row) {
    //             if ($row['is_updated']) {
    //                 return '<button class="btn btn-sm btn-secondary" disabled><i class="fas fa-edit"></i></button>';
    //             }
    //             $editUrl = route('pages.Fingerprints.edit', [
    //                 'pin'       => $row['pin'],
    //                 'scan_date' => $row['scan_date'],
    //             ]);
    //             return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary me-1">
    //                         <i class="fas fa-edit"></i>
    //                     </a>';
    //         })
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }

    public function editFingerprint($pin, Request $request)
    {
        $scanDate = $request->input('scan_date');
        if (!$scanDate) {
            return response()->json(['message' => 'scan_date is required'], 400);
        }

        $scanDateCarbon = Carbon::parse($scanDate)->toDateString();

        $data = EditedFingerprint::with('devicefingerprints')
            ->where('pin', $pin)
            ->whereDate('scan_date', $scanDateCarbon)
            ->first();

        if ($data) {
            return view('pages.Fingerprints.edit', ['data' => $data, 'isEdited' => true]);
        }

        $fingerprints = Fingerprints::with('devicefingerprints')
            ->where('pin', $pin)
            ->whereDate('scan_date', $scanDateCarbon)
            ->orderBy('scan_date')
            ->get();

        if ($fingerprints->isEmpty()) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $first    = $fingerprints->first();
        $employee = Employee::with(['store:id,name', 'position:id,name'])
            ->where('pin', $pin)
            ->first();

        $row = [
            'pin'               => $pin,
            'employee_name'     => $employee->employee_name ?? '-',
            'status_employee'   => $employee->status_employee ?? '-',
            'employee_pengenal' => $employee->employee_pengenal ?? '-',
            'name'              => $employee->store->name ?? '-',
            'position_name'     => optional($employee->position)->name ?? '-',
            'device_name'       => optional($first->devicefingerprints)->device_name ?? '-',
            'scan_date'         => $scanDateCarbon,
        ];

        foreach (range(1, 10) as $i) {
            $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
        }

        $fingerprints->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
            if ($mode >= 1 && $mode <= 10) {
                $firstItem = $items->sortBy('scan_date')->first();
                $formatted = null;
                try {
                    $formatted = Carbon::parse($firstItem->scan_date)->format('H:i:s');
                } catch (\Exception $e) {
                    Log::error('Gagal parsing waktu', ['mode' => $mode, 'error' => $e->getMessage()]);
                }
                $deviceName           = optional($firstItem->devicefingerprints)->device_name ?? '';
                $row["in_$mode"]      = $formatted;
                $row["device_$mode"]  = $deviceName;
                $row["combine_$mode"] = "{$formatted} {$deviceName}";
            }
        });

        return view('pages.Fingerprints.edit', [
            'data'     => (object) $row,
            'isEdited' => false,
        ]);
    }

    public function updateFingerprint(Request $request)
    {
        try {
            $validated = $request->validate([
                'pin'            => 'required|string',
                'scan_date'      => 'required|date',
                'employee_name'  => 'nullable|string',
                'position_name'  => 'nullable|string',
                'store_name'     => 'nullable|string',
                'duration'       => 'nullable|string',
                'attachment'     => 'required|file|mimes:jpg,jpeg,png,pdf|max:512',
                ...collect(range(1, 10))->flatMap(function ($i) {
                    return ["in_$i" => 'nullable|string', "device_$i" => 'nullable|string'];
                })->toArray()
            ]);

            $filename = null;
            if ($request->hasFile('attachment')) {
                try {
                    $filename = $request->file('attachment')->store('attachment', 'public');
                } catch (\Exception $e) {
                    Log::error('Gagal upload attachment', ['error' => $e->getMessage()]);
                }
            }

            EditedFingerprint::updateOrCreate(
                ['pin' => $validated['pin'], 'scan_date' => $validated['scan_date']],
                collect($validated)->except(['pin', 'scan_date'])
                    ->merge(['attachment' => $filename])
                    ->toArray()
            );

            return redirect()->route('pages.Fingerprints')
                ->with('success', 'Fingerprint updated successfully.');
        } catch (\Exception $e) {
            Log::error('Gagal updateFingerprint', ['error' => $e->getMessage()]);
            return back()->with('error', 'There is an error while updating fingerprints.');
        }
    }
}