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

/**
 * Controller untuk halaman Fingerprints.
 *
 * METHOD UTAMA:
 * ─────────────
 * - recap()              : Sync raw scan dari att_log → fingerprints_recap (TIDAK menimpa Manual)
 * - getFingerprints()    : DataTable List Fingerprints (raw scan dari att_log)
 * - getManualAdded()     : DataTable Manual Added (data dari manual_added)
 * - editFingerprint()    : Form edit per row
 * - updateFingerprint()  : Submit edit per row
 *
 * PERBAIKAN BUG (recap & total hari kerja):
 * ─────────────────────────────────────────
 * 1. recap() sekarang CEK ROSTER sebelum set is_counted
 *    → Hari Off/Libur tidak ke-counted walaupun ada scan
 *
 * 2. recap() SKIP data dengan sync_status='Manual'
 *    → Manual Recap dari HR tidak ditimpa oleh sync otomatis
 *
 * 3. recap() TIDAK lagi concat manual_added
 *    → Sudah di-handle oleh ManualRecapController saat submit
 *    → Hindari double-write & risiko menimpa data manual
 *
 * 4. getFingerprints() cross-check roster saat hitung total_hari
 *    → Total hari kerja akurat (tidak hitung hari Off/Libur)
 */
class FingerprintsController extends Controller
{
    private const TOLERANSI_TINGGI_STORES = [
        'Head Office', 'Holding', 'Distribution Center',
    ];
    private const TOLERANSI_TINGGI_MENIT = 10;
    private const TOLERANSI_NORMAL_MENIT = 5;

    /**
     * Sync status untuk data Manual Recap.
     * Data dengan sync_status ini TIDAK BOLEH ditimpa oleh recap().
     */
    private const SYNC_STATUS_MANUAL = 'Manual';

    public function index()
    {
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->distinct()
            ->pluck('name');
        return view('pages.Fingerprints.Fingerprints', compact('stores'));
    }

    /**
     * Recap Absensi: sync raw scan dari att_log ke fingerprints_recap.
     *
     * SUMBER DATA:
     *   - mysql_second.att_log         (raw scan dari mesin)
     *   - mysql_second.edited_fingerprint (override hasil Edit per row)
     *
     * CATATAN PENTING:
     *   - Manual Recap (dari ManualRecapController) sudah langsung tulis ke
     *     fingerprints_recap dengan sync_status='Manual'. Jadi method ini
     *     TIDAK perlu lagi membaca dari manual_added.
     *   - Data dengan sync_status='Manual' akan di-SKIP supaya tidak ditimpa.
     *   - is_counted=1 hanya kalau hari kerja (roster day_type='Work') DAN ada scan.
     *
     * Hasil disimpan ke: fingerprints_recap
     */
    public function recap(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'store_name' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        $employeesQuery = Employee::with('store:id,name')
            ->select('id', 'pin', 'store_id')
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

        $pins        = $employees->keys()->toArray();
        $employeeIds = $employees->pluck('id')->toArray();

        // ── Ambil raw scan dari att_log SAJA ──
        // (manual_added TIDAK perlu diambil — sudah di-handle oleh ManualRecapController)
        $rawScans = DB::connection('mysql_second')
            ->table('att_log')
            ->select('pin', 'scan_date', 'inoutmode', 'sn')
            ->whereIn('pin', $pins)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->whereIn('inoutmode', [1, 2])
            ->get();

        if ($rawScans->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'There is no fingerprint data in that date range.',
            ], 422);
        }

        // ── Pre-load roster untuk cek hari kerja ──
        $rosters = Roster::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

        // ── Pre-load existing manual recaps supaya TIDAK ditimpa ──
        $existingManualRecaps = FingerprintRecap::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('sync_status', self::SYNC_STATUS_MANUAL)
            ->get()
            ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

        // ── Group scans by pin & date ──
        $grouped = [];
        foreach ($rawScans as $scan) {
            $pin  = (string) $scan->pin;
            $date = Carbon::parse($scan->scan_date)->toDateString();
            $time = Carbon::parse($scan->scan_date)->format('H:i:s');
            $mode = (int) $scan->inoutmode;
            $sn   = $scan->sn ?? null;

            if (!isset($grouped[$pin][$date])) {
                $grouped[$pin][$date] = ['time_in' => null, 'time_out' => null, 'device_sn' => $sn];
            }

            if ($mode === 1 && $grouped[$pin][$date]['time_in'] === null) {
                $grouped[$pin][$date]['time_in'] = $time;
            }
            if ($mode === 2) {
                $grouped[$pin][$date]['time_out'] = $time;
            }
        }

        // ── Ambil edited_fingerprint untuk override time_in/time_out ──
        $editedFingerprints = DB::connection('mysql_second')
            ->table('edited_fingerprint')
            ->select('pin', 'scan_date', 'in_1', 'in_2')
            ->whereIn('pin', $pins)
            ->whereBetween('scan_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($e) => (string)$e->pin . '_' . Carbon::parse($e->scan_date)->toDateString());

        $synced            = 0;
        $skipped           = 0;
        $skippedManual     = 0;
        $errors            = [];

        foreach ($grouped as $pin => $dates) {
            $employee = $employees->get($pin);

            if (!$employee) {
                $skipped++;
                $errors[] = "PIN {$pin} was not found in employee data.";
                continue;
            }

            foreach ($dates as $date => $times) {
                $key = $employee->id . '_' . $date;

                // ── SKIP: data manual recap, jangan ditimpa ──
                if ($existingManualRecaps->has($key)) {
                    $skippedManual++;
                    Log::info("Recap: skip {$employee->id} on {$date} — already has Manual recap");
                    continue;
                }

                $timeIn   = $times['time_in'];
                $timeOut  = $times['time_out'];
                $deviceSn = $times['device_sn'];

                // Override dari edited_fingerprint kalau ada
                $editedKey = $pin . '_' . $date;
                $edited    = $editedFingerprints->get($editedKey);

                if ($edited) {
                    $timeIn  = $edited->in_1 ?? $timeIn;
                    $timeOut = $edited->in_2 ?? $timeOut;
                }

                // Hitung durasi
                $duration = null;
                if ($timeIn && $timeOut) {
                    $dtIn  = Carbon::parse($date . ' ' . $timeIn);
                    $dtOut = Carbon::parse($date . ' ' . $timeOut);
                    if ($dtOut->lt($dtIn)) $dtOut->addDay();
                    $duration = (int) $dtIn->diffInMinutes($dtOut);
                }

                // ── Cek roster untuk tentukan is_counted ──
                $roster    = $rosters->get($key);
                $isWorkDay = $roster && $roster->day_type === 'Work';
                $hasScan   = ($timeIn || $timeOut);

                // is_counted = 1 HANYA kalau hari kerja (roster Work) DAN ada scan
                $isCounted = ($isWorkDay && $hasScan) ? 1 : 0;

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
            'success'        => true,
            'message'        => "Successfully summarized {$synced} attendance data. "
                              . "({$skipped} unmatch PIN, {$skippedManual} skipped manual recap)",
            'synced'         => $synced,
            'skipped'        => $skipped,
            'skipped_manual' => $skippedManual,
            'errors'         => $errors,
        ]);
    }

    /**
     * DataTable utama (atas): List Fingerprints
     * Sumber: hanya raw scan dari mysql_second.att_log
     */
    public function getFingerprints(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'store_name' => 'nullable|string|max:100',
        ]);

        $storeName = $request->input('store_name');
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
        $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

        $editedKeys = EditedFingerprint::whereBetween('scan_date', [$startDate, $endDate])
            ->get(['pin', 'scan_date'])
            ->map(fn($e) => $e->pin . '_' . Carbon::parse($e->scan_date)->toDateString())
            ->values()
            ->toArray();

        $employeesQuery = Employee::with(['position:id,name', 'store:id,name'])
            ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'status_employee')
            ->whereNotNull('pin');

        if ($storeName) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('name', $storeName));
        }

        $employees   = $employeesQuery->get()->keyBy('pin');
        $employeeIds = $employees->pluck('id')->filter()->values()->toArray();

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


        $pins = $employees->keys()->toArray();
        $fingerprints = Fingerprints::select(['sn', 'scan_date', 'pin', 'inoutmode'])
            ->whereIn('pin', $pins)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->orderBy('pin')
            ->orderBy('scan_date')
            ->get();

        $deviceNames = Devicefingerprint::select('sn', 'device_name')
            ->get()
            ->keyBy('sn')
            ->map(fn($d) => $d->device_name ?? '-');

        $grouped = $fingerprints
            ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

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
                    intdiv($minutes, 60), intdiv($minutes, 60) !== 1 ? 's' : '',
                    $minutes % 60, $minutes % 60 !== 1 ? 's' : ''
                );
            } else {
                $row['duration'] = 'invalid';
            }

            // Status Updated HANYA dari edited_fingerprint
            $row['is_updated']     = in_array($key, $editedKeys);
            $row['updated_status'] = $row['is_updated'] ? 'Updated' : 'Original';
            $row['is_late_in']     = false;
            $row['is_late']        = false;
            $row['late_minutes']   = 0;

            if ($roster && $roster->day_type === 'Work' && $roster->shift && $row['in_1']) {
                $firstIn = trim(explode(',', $row['in_1'])[0]);
                if ($firstIn) {
                    $shiftStart  = Carbon::parse($scanDate . ' ' . $roster->shift->start_time);
                    $actualIn    = Carbon::parse($scanDate . ' ' . $firstIn);
                    $toleransi   = in_array($employee->store->name ?? '', self::TOLERANSI_TINGGI_STORES)
                        ? self::TOLERANSI_TINGGI_MENIT
                        : self::TOLERANSI_NORMAL_MENIT;
                    $batasMasuk  = $shiftStart->copy()->addMinutes($toleransi);
                    $lateMinutes = max(0, $shiftStart->diffInMinutes($actualIn, false));

                    if ($actualIn->gt($batasMasuk)) {
                        $row['is_late_in']   = true;
                        $row['is_late']      = true;
                        $row['late_minutes'] = $lateMinutes;
                    }
                }
            }

            return $row;
        })->filter()->values();

        $stats = [
            'total'   => $result->count(),
            'on_time' => $result->where('is_late', false)->count(),
            'late'    => $result->where('is_late', true)->count(),
            'updated' => $result->where('is_updated', true)->count(),
            'missing' => $result->filter(fn($r) => empty($r['in_2']))->count(),
        ];

        return DataTables::of($result)
            ->with(['stats' => $stats])
            ->addColumn('in_1_colored', function ($row) {
                if (!$row['in_1']) return '-';
                if ($row['is_late']) {
                    return '<span class="text-danger fw-bold">' . $row['in_1'] . '</span>';
                }
                return '<span class="text-success">' . $row['in_1'] . '</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row['is_updated']) {
                    return '<button class="btn btn-sm btn-secondary" disabled title="Already updated"><i class="fas fa-edit"></i></button>';
                }
                $editUrl = route('pages.Fingerprints.edit', [
                    'pin'       => $row['pin'],
                    'scan_date' => $row['scan_date'],
                ]);
                return '<a href="' . $editUrl . '" class="btn btn-sm btn-primary me-1">
                            <i class="fas fa-edit"></i>
                        </a>';
            })
            ->rawColumns(['action', 'in_1_colored'])
            ->make(true);
    }

    /**
     * DataTable bawah: Manual Added
     * Sumber: mysql_second.manual_added (hasil Add Recap)
     */
    public function getManualAdded(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $storeName = $request->input('store_name');
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
        $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

        $employeesQuery = Employee::with(['position:id,name', 'store:id,name'])
            ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'status_employee')
            ->whereNotNull('pin');

        if ($storeName) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('name', $storeName));
        }

        $employees   = $employeesQuery->get()->keyBy('pin');
        $employeeIds = $employees->pluck('id')->filter()->values()->toArray();

        $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
            ->select('id', 'employee_id', 'shift_id', 'date', 'day_type')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

        $pins = $employees->keys()->toArray();

        $manualData = DB::connection('mysql_second')
            ->table('manual_added')
            ->select('sn', 'scan_date', 'pin', 'inoutmode')
            ->whereIn('pin', $pins)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->orderBy('pin')
            ->orderBy('scan_date')
            ->get();

        if ($manualData->isEmpty()) {
            return DataTables::of(collect([]))->make(true);
        }

        $grouped = collect($manualData)
            ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

        $result = $grouped->map(function ($group, $key) use ($employees, $rosters) {
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

            $row = [
                'pin'               => $pin,
                'employee_name'     => $employee->employee_name ?? '-',
                'status_employee'   => $employee->status_employee ?? '-',
                'employee_pengenal' => $employee->employee_pengenal ?? '-',
                'name'              => $employee->store->name ?? '-',
                'position_name'     => $employee->position->name ?? '-',
                'scan_date'         => $scanDate,
                'roster_name'       => $rosterName,
                'roster_time'       => $rosterTime,
                'is_updated'        => true,
                'updated_status'    => 'Updated',
            ];

            for ($i = 1; $i <= 10; $i++) {
                $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
            }

            collect($group)->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
                if ($mode < 1 || $mode > 10) return;
                $sorted = collect($items)->sortBy('scan_date');
                $times  = $sorted->pluck('scan_date')
                    ->map(fn($d) => Carbon::parse($d)->format('H:i:s'))
                    ->implode(', ');

                $row["in_$mode"]      = $times;
                $row["device_$mode"]  = 'Manual';
                $row["combine_$mode"] = trim($times . ' Manual');
            });

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
                    intdiv($minutes, 60), intdiv($minutes, 60) !== 1 ? 's' : '',
                    $minutes % 60, $minutes % 60 !== 1 ? 's' : ''
                );
            } else {
                $row['duration'] = 'Manual';
            }

            return $row;
        })->filter()->values();

        return DataTables::of($result)
            ->addColumn('action', function ($row) {
                return '<span class="fp-badge fp-badge-updated" style="font-size:.7rem;padding:.3rem .7rem">
                            <i class="fas fa-check me-1"></i>Manual Added
                        </span>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function editFingerprint($pin, Request $request)
    {
        $request->merge(['pin' => $pin]);
        $request->validate([
            'pin'       => 'required|string|max:20',
            'scan_date' => 'required|date',
        ]);

        $scanDate       = $request->input('scan_date');
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