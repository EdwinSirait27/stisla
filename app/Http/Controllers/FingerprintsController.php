<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Models\Fingerprints;
use App\Models\EditedFingerprint;
use App\Models\Employee;
use App\Models\Stores;
use Illuminate\Support\Facades\Storage;
use App\Models\Roster;
use App\Models\User;
use App\Models\Fingerprintrecap;
use App\Exports\FingerprintsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Devicefingerprint;
use App\Models\Fingerprintrecaparchive;
use App\Services\FingerprintRecapCalculator;
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
        'Head Office',
        'Holding',
        'Distribution Center',
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
        $user = auth()->user();

        /** @var \App\Models\User|null $user */

        $canManage     = $user->hasPermissionTo('ManageFingerspot');
        $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
        $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');

        if (!$canManage && !$canSpvManager && !$canViewOwn) {
            abort(403, 'Unauthorized');
        }

        $today = now();

        if ($canManage) {
            // ManageFingerspot: default range bebas (26 bulan lalu - 25 bulan ini)
            $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
            $defaultEndDate   = $today->copy()->day(25)->toDateString();
        } else {
            // SPVManager & ViewOwn: default max 1 bulan ke belakang
            $defaultStartDate = $today->copy()->subMonth()->toDateString();
            $defaultEndDate   = $today->toDateString();
        }

        // Store list hanya untuk ManageFingerspot (bebas pilih)
        $stores = $canManage
            ? Stores::select('id', 'name')->whereNotNull('name')->distinct()->pluck('name')
            : collect();

        
        $lockedStore = null;
if ($canSpvManager && !$canManage) {
    $lockedStore = $user->employee->primaryStore()->first()?->name ?? null;
}
        return view('pages.Fingerprints.Fingerprints', compact(
            'stores',
            'defaultStartDate',
            'defaultEndDate',
            'canManage',
            'canSpvManager',
            'canViewOwn',
            'lockedStore'
        ));
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
        $user     = auth()->user();

        /** @var \App\Models\User|null $user */

        if (!$user->hasPermissionTo('ManageFingerspot')) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'store_name' => 'nullable|string',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        // $employeesQuery = Employee::with('store:id,name')
        //     ->select('id', 'pin', 'store_id')
        //     ->whereNotNull('pin')
        //     ->whereNull('deleted_at');
         $employeesQuery = Employee::with([
            'store' => fn($q) => $q->wherePivot('is_primary', true)->select('stores_tables.id', 'stores_tables.name'),
        ])
        ->select('id', 'pin')
        ->whereNotNull('pin')
        ->whereNull('deleted_at');

        // if ($request->store_name) {
        //     $employeesQuery->whereHas('store', fn($q) => $q->where('name', $request->store_name));
        // }
         if ($request->store_name) {
        $employeesQuery->whereExists(function ($q) use ($request) {
            $q->select(DB::raw(1))
                ->from('employee_stores')
                ->join('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')
                ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                ->where('stores_tables.name', $request->store_name);
        });
    }


        // $employees = $employeesQuery->get()->keyBy(fn($e) => (string) $e->pin);

        // if ($employees->isEmpty()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'There are no employees with registered PINs.',
        //     ], 422);
        // }
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
        $existingManualRecaps = Fingerprintrecap::whereIn('employee_id', $employeeIds)
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

        // ════════════════════════════════════════════════════════════
        //  SIMPAN KE ARSIP (per store + periode)
        //  Dihitung via service yang sama dengan halaman Fingerprint Recap
        //  supaya angka arsip = angka tampilan.
        // ════════════════════════════════════════════════════════════
        try {
            // Ambil ulang employees lengkap (butuh status_employee + store untuk hitung)
            $employeesForArchive = Employee::with('store:id,name')
                ->select('id', 'employee_name', 'store_id', 'status_employee')
                ->whereIn('id', $employeeIds)
                ->get();

            $calculator = new FingerprintRecapCalculator();
            $archiveRows = $calculator->calculate(
                $employeesForArchive,
                $startDate->toDateString(),
                $endDate->toDateString()
            );

            DB::transaction(function () use ($archiveRows, $startDate, $endDate) {
                foreach ($archiveRows as $row) {
                    // Timpa: hapus arsip lama untuk (karyawan + store + periode) ini
                    Fingerprintrecaparchive::where('employee_id', $row['employee_id'])
                        ->where('store_name', $row['store_name'])
                        ->where('period_start', $startDate->toDateString())
                        ->where('period_end', $endDate->toDateString())
                        ->delete();

                    // Tulis arsip baru
                    Fingerprintrecaparchive::create([
                        'employee_id'      => $row['employee_id'],
                        'employee_name'    => $row['employee_name'],
                        'store_name'       => $row['store_name'],
                        'period_start'     => $startDate->toDateString(),
                        'period_end'       => $endDate->toDateString(),
                        'total_hari_kerja' => $row['total_hari_kerja'],
                        'total_hari_telat' => $row['total_hari_telat'],
                        'remarks'          => $row['remarks'],
                        'archived_by'      => optional(auth()->user())->id,
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Recap: gagal simpan arsip', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);
            // Arsip gagal TIDAK membatalkan sync yang sudah berhasil.
            // Sync tetap sukses; arsip bisa diulang dengan recap lagi.
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


    private function buildFingerprintResult(
        $startDate,
        $endDate,
        $storeName,
        $user,
        bool $canManage,
        bool $canSpvManager,
        bool $canViewOwn
    ): \Illuminate\Support\Collection {

        $editedKeys = EditedFingerprint::whereBetween('scan_date', [$startDate, $endDate])
            ->get(['pin', 'scan_date'])
            ->map(fn($e) => $e->pin . '_' . Carbon::parse($e->scan_date)->toDateString())
            ->values()
            ->toArray();

//         $employeesQuery = Employee::with(['position:id,name', 'store:id,name'])
//             ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'status_employee')
//             ->whereNotNull('pin');

        
// if ($canViewOwn && !$canManage && !$canSpvManager) {
//     $employeesQuery->where('pin', $user->employee->pin);
// } elseif ($canSpvManager && !$canManage) {
//     // Ambil store primary user
//     $userStoreIds = $user->employee->store()
//         ->wherePivot('is_primary', true)
//         ->pluck('stores_tables.id');

//     $employeesQuery->whereHas('store', fn($q) =>
//         $q->whereIn('stores_tables.id', $userStoreIds)
//           ->where('employee_stores.is_primary', true)
//     );
// } else {
//     if ($storeName) {
//         $employeesQuery->whereHas('store', fn($q) =>
//             $q->where('stores_tables.name', $storeName)
//         );
//     }
// }
$employeesQuery = Employee::with(['position' => fn($q) => $q->wherePivot('is_primary', true), 'store' => fn($q) => $q->wherePivot('is_primary', true)])
    ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'status_employee','status', 'company_id')
    ->whereNotNull('pin');

if ($canViewOwn && !$canManage && !$canSpvManager) {
    // ← ViewFingerspot: hanya data sendiri
    $employeesQuery->where('pin', $user->employee->pin);

} elseif ($canSpvManager && !$canManage) {
    // ← ManageFingerspotSPVManager: filter company + store kepunyaan
    $userEmployee  = $user->employee;
    $companyId     = $userEmployee->company_id;
    $userStoreIds  = $userEmployee->store()->pluck('stores_tables.id')->toArray();
    $userDeptIds   = $userEmployee->department()->pluck('departments_tables.id')->toArray();

    if (empty($userStoreIds) || empty($userDeptIds)) {
        return collect();
    }



    $employeesQuery
        ->where('company_id', $companyId)
        ->whereExists(function ($q) use ($userStoreIds) {
            $q->select(DB::raw(1))
                ->from('employee_stores')
                ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                ->whereIn('employee_stores.store_id', $userStoreIds);
        })
        ->whereExists(function ($q) use ($userDeptIds) {
            $q->select(DB::raw(1))
                ->from('employee_departments')
                ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                ->whereIn('employee_departments.department_id', $userDeptIds);
        });

    if ($storeName) {
        $employeesQuery->whereHas('store', fn($q) =>
            $q->where('stores_tables.name', $storeName)
        );
    }

} else {
    // ← ManageFingerspot: bebas filter store
    if ($storeName) {
        $employeesQuery->whereHas('store', fn($q) =>
            $q->where('stores_tables.name', $storeName)
        );
    }
}

        $employees   = $employeesQuery->get()->keyBy('pin');
        $employeeIds = $employees->pluck('id')->filter()->values()->toArray();

        $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
            ->select('id', 'employee_id', 'shift_id', 'date', 'day_type')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

        $totalHariPerEmployee = Fingerprintrecap::select('employee_id', DB::raw('SUM(is_counted) as total_hari'))
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('employee_id')
            ->pluck('total_hari', 'employee_id');

        $pins         = $employees->keys()->toArray();
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

        return $grouped->map(function ($group, $key) use (
            $employees,
            $totalHariPerEmployee,
            $editedKeys,
            $rosters,
            $deviceNames,
            $canManage,
            $canSpvManager,
            $canViewOwn
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
                        . ' - ' . substr($roster->shift->end_time, 0, 5);
                }
            }

            $totalHari = $totalHariPerEmployee->get($employee->id, 0);

            $row = [
                'pin'               => $pin,
                'employee_name'     => $employee->employee_name     ?? '-',
                'status_employee'   => $employee->status_employee   ?? '-',
                'status'   => $employee->status   ?? '-',
                'employee_pengenal' => $employee->employee_pengenal ?? '-',
                'name'              => $employee->store->first()?->name      ?? '-', // ← fix
    'position_name'     => $employee->position->first()?->name   ?? '-', // ← fix
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
                ->flatMap(fn($i) => $row["in_$i"] ? explode(', ', $row["in_$i"]) : [])
                ->map(fn($t) => Carbon::parse($t))
                ->sort()
                ->values();

            if ($times->count() >= 2) {
                $minutes         = $times->first()->diffInMinutes($times->last());
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
            $row['is_late_in']     = false;
            $row['is_late']        = false;
            $row['late_minutes']   = 0;

            if ($roster && $roster->day_type === 'Work' && $roster->shift && $row['in_1']) {
                $firstIn = trim(explode(',', $row['in_1'])[0]);
                if ($firstIn) {
                    $shiftStart  = Carbon::parse($scanDate . ' ' . $roster->shift->start_time);
                    $actualIn    = Carbon::parse($scanDate . ' ' . $firstIn);
                 
                    $toleransi = in_array($employee->store->first()?->name ?? '', self::TOLERANSI_TINGGI_STORES)
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

            $row['can_action'] = $canManage;

            return $row;
        })->filter()->values();
    }
    public function getFingerprints(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);
        $user = auth()->user();

        /** @var \App\Models\User|null $user */

        $canManage     = $user->hasPermissionTo('ManageFingerspot');
        $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
        $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');

        if (!$canManage && !$canSpvManager && !$canViewOwn) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'store_name' => 'nullable|string|max:100',
        ]);

        $storeName = $request->input('store_name');
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
        $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

        if (!$canManage && ($canSpvManager || $canViewOwn)) {
            $minAllowedDate = now()->subMonth()->startOfDay();
            if ($startDate->lt($minAllowedDate)) $startDate = $minAllowedDate;
            if ($endDate->lt($minAllowedDate)) {
                abort(422, 'Rentang tanggal tidak diizinkan. Maksimal 1 bulan ke belakang.');
            }
        }

        $result = $this->buildFingerprintResult(
            $startDate,
            $endDate,
            $storeName,
            $user,
            $canManage,
            $canSpvManager,
            $canViewOwn
        );

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
                return $row['is_late']
                    ? '<span class="text-danger fw-bold">' . $row['in_1'] . '</span>'
                    : '<span class="text-success">' . $row['in_1'] . '</span>';
            })
            ->addColumn('action', function ($row) use ($canManage) {
                if (!$canManage) return '-';
                if ($row['is_updated']) {
                    return '<button class="btn btn-sm btn-secondary" disabled title="Already updated">
                            <i class="fas fa-edit"></i>
                        </button>';
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
    public function exportfingerprints(Request $request)
    {
        $user = auth()->user();

        /** @var \App\Models\User|null $user */

        $canManage     = $user->hasPermissionTo('ManageFingerspot');
        $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');

        if (!$canManage && !$canSpvManager) {
            abort(403, 'Unauthorized');
        }

        $storeName = $request->input('store_name');
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
        $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

        if (!$canManage && $canSpvManager) {
            $minAllowedDate = now()->subMonth()->startOfDay();
            if ($startDate->lt($minAllowedDate)) $startDate = $minAllowedDate;
        }

        $result = $this->buildFingerprintResult(
            $startDate,
            $endDate,
            $storeName,
            $user,
            $canManage,
            $canSpvManager,
            false
        );

        $exportType = $request->input('export', 'excel');
        $filename   = 'fingerprints_' . $startDate->toDateString() . '_' . $endDate->toDateString();
        $export     = new \App\Exports\FingerprintsExport($result, $storeName ?? '');

        return $exportType === 'csv'
            ? Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV)
            : Excel::download($export, $filename . '.xlsx');
    }
    


    /**
     * DataTable bawah: Manual Added
     * Sumber: mysql_second.manual_added (hasil Add Recap)
     */
    // ini_set('memory_limit', '1024M');
    //     set_time_limit(300);
    public function getManualAdded(Request $request)
    {
        $user     = auth()->user();

        /** @var \App\Models\User|null $user */

        if (!$user->hasPermissionTo('ManageFingerspot')) {
            abort(403, 'Unauthorized');
        }


        $storeName = $request->input('store_name');
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
        $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

        // $employeesQuery = Employee::with(['position:id,name', 'store:id,name'])
        //     ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'status_employee')
        //     ->whereNotNull('pin');
        $employeesQuery = Employee::with([
    'store'    => fn($q) => $q->wherePivot('is_primary', true),
    'position' => fn($q) => $q->wherePivot('is_primary', true),
])
->select('id', 'pin', 'employee_name', 'employee_pengenal', 'status_employee')
->whereNotNull('pin');

        // if ($storeName) {
        //     $employeesQuery->whereHas('store', fn($q) => $q->where('name', $storeName));
        // }
        if ($storeName) {
    $employeesQuery->whereHas('store', fn($q) => $q->where('stores_tables.name', $storeName));
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
                // 'name'              => $employee->store->name ?? '-',
                // 'position_name'     => $employee->position->name ?? '-',
                'name'              => $employee->store->first()?->name      ?? '-', // ← fix
    'position_name'     => $employee->position->first()?->name   ?? '-', // ← fix
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
                    intdiv($minutes, 60),
                    intdiv($minutes, 60) !== 1 ? 's' : '',
                    $minutes % 60,
                    $minutes % 60 !== 1 ? 's' : ''
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
        $user = auth()->user();

        /** @var \App\Models\User|null $user */

        if (!$user->hasPermissionTo('ManageFingerspot')) {
            abort(403, 'Unauthorized');
        }

        $request->merge(['pin' => $pin]);
        $request->validate([
            'pin'       => 'required|string|max:20',
            'scan_date' => 'required|date',
        ]);

        $scanDate       = $request->input('scan_date');
        $scanDateCarbon = Carbon::parse($scanDate)->toDateString();

        // ── Ambil semua device untuk dropdown ──
        $devices = Devicefingerprint::select('sn', 'device_name')
            ->whereNotNull('device_name')
            ->orderBy('device_name')
            ->get();
        //    dd($devices->pluck('device_name', 'sn'));
        $data = EditedFingerprint::with('devicefingerprints')
            ->where('pin', $pin)
            ->whereDate('scan_date', $scanDateCarbon)
            ->first();

        if ($data) {
            return view('pages.Fingerprints.edit', [
                'data'     => $data,
                'isEdited' => true,
                'devices'  => $devices,
            ]);
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
            'employee_name'     => $employee->employee_name               ?? '-',
            'status_employee'   => $employee->status_employee             ?? '-',
            'employee_pengenal' => $employee->employee_pengenal           ?? '-',
            // 'name'              => $employee->store->name                 ?? '-',
            // 'position_name'     => optional($employee->position)->name    ?? '-',
             'name'              => $employee->store->first()?->name       ?? '-', // ← fix
    'position_name'     => $employee->position->first()?->name    ?? '-', // ← fix
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
                // $deviceName           = optional($firstItem->devicefingerprints)->device_name ?? '';
                $deviceName           = trim(optional($firstItem->devicefingerprints)->device_name ?? '');

                $row["in_$mode"]      = $formatted;
                $row["device_$mode"]  = $deviceName;
                $row["combine_$mode"] = "{$formatted} {$deviceName}";
            }
        });

        return view('pages.Fingerprints.edit', [
            'data'     => (object) $row,
            'isEdited' => false,
            'devices'  => $devices,
        ]);
    }
    public function updateFingerprint(Request $request)
    {
        $user = auth()->user();

        /** @var \App\Models\User|null $user */

        if (!$user->hasPermissionTo('ManageFingerspot')) {
            abort(403, 'Unauthorized');
        }

        try {
            $validated = $request->validate([
                'pin'           => 'required|string',
                'scan_date'     => 'required|date',
                'employee_name' => 'nullable|string',
                'position_name' => 'nullable|string',
                'store_name'    => 'nullable|string',
                'duration'      => 'nullable|string',
                'attachment'    => 'required|mimes:jpg,jpeg,png,webp|max:512',
                ...collect(range(1, 10))->flatMap(function ($i) {
                    return ["in_$i" => 'nullable|string', "device_$i" => 'nullable|string'];
                })->toArray()
            ]);
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $file     = $request->file('attachment');
                $safeName = Str::slug($request->input('employee_name', 'employee'));
                $fileName = $safeName . '-' . now()->timestamp . '-fingerprint.' . $file->getClientOriginalExtension();
                $folder   = 'employees-edited-fingerprints';
                Log::info('[attachment fingerprints] Info upload', [
                    'original_name' => $file->getClientOriginalName(),
                    'size'          => $file->getSize(),
                    'mime'          => $file->getMimeType(),
                    'fileName'      => $fileName,
                    'folder'        => $folder,
                ]);
                // ── Hapus attachment lama jika ada ──
                $existing = EditedFingerprint::where('pin', $validated['pin'])
                    ->whereDate('scan_date', $validated['scan_date'])
                    ->first();

                if ($existing && $existing->attachment && Storage::disk('s3')->exists($existing->attachment)) {
                    Storage::disk('s3')->delete($existing->attachment);
                    Log::info('[attachment fingerprints] Attachment lama dihapus', ['path' => $existing->attachment]);
                } else {
                    Log::info('[attachment fingerprints] Tidak ada attachment lama untuk dihapus');
                }

                // ── Upload baru ke S3 ──
                $attachmentPath = Storage::disk('s3')->putFileAs($folder, $file, $fileName);

                Log::info('[attachment fingerprints] Upload selesai', [
                    'path'   => $attachmentPath,
                    'exists' => Storage::disk('s3')->exists($attachmentPath),
                ]);
            }

            // ── Simpan ke edited_fingerprint ──
            $payload = collect($validated)
                ->except(['pin', 'scan_date', 'attachment'])
                ->toArray();

            if ($attachmentPath) {
                $payload['attachment'] = $attachmentPath;
            }

            EditedFingerprint::updateOrCreate(
                [
                    'pin'       => $validated['pin'],
                    'scan_date' => $validated['scan_date'],
                ],
                $payload
            );

            return redirect()->route('pages.Fingerprints')
                ->with('success', 'Fingerprint updated successfully.');
        } catch (\Exception $e) {
            Log::error('Gagal updateFingerprint', ['error' => $e->getMessage()]);
            return back()->with('error', 'There is an error while updating fingerprints.');
        }
    }
}
