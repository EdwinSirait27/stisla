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


//     public function index()
//     {
//         $user = auth()->user();

//         /** @var \App\Models\User|null $user */

//         $canManage     = $user->hasPermissionTo('ManageFingerspot');
//         $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
//         $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');

//         if (!$canManage && !$canSpvManager && !$canViewOwn) {
//             abort(403, 'Unauthorized');
//         }

//         $today = now();

//         if ($canManage) {
//             // ManageFingerspot: default range bebas (26 bulan lalu - 25 bulan ini)
//             $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
//             $defaultEndDate   = $today->copy()->day(25)->toDateString();
//         } else {
//             // SPVManager & ViewOwn: default max 1 bulan ke belakang
//             $defaultStartDate = $today->copy()->subMonth()->toDateString();
//             $defaultEndDate   = $today->toDateString();
//         }

//         // Store list hanya untuk ManageFingerspot (bebas pilih)
//         $stores = $canManage
//             ? Stores::select('id', 'name')->whereNotNull('name')->distinct()->pluck('name')
//             : collect();

        
//         $lockedStore = null;
// if ($canSpvManager && !$canManage) {
//     $lockedStore = $user->employee->primaryStore()->first()?->name ?? null;
// }
//         return view('pages.Fingerprints.Fingerprints', compact(
//             'stores',
//             'defaultStartDate',
//             'defaultEndDate',
//             'canManage',
//             'canSpvManager',
//             'canViewOwn',
//             'lockedStore'
//         ));
//     }
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

    // $today = now();

    // if ($canManage) {
    //     $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
    //     $defaultEndDate   = $today->copy()->day(25)->toDateString();
    // } else {
    //     $defaultStartDate = $today->copy()->subMonth()->toDateString();
    //     $defaultEndDate   = $today->toDateString();
    // }
   $today = now();

if ($canManage) {
    $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
    $defaultEndDate   = $today->copy()->day(25)->toDateString();
} else {
    // Default: 26 bulan lalu → 25 bulan depan
    $defaultStartDate = $today->copy()->subMonth()->day(26)->toDateString();
    $defaultEndDate   = $today->copy()->addMonth()->day(25)->toDateString();
}

    // ← ManageFingerspot: semua store
    if ($canManage) {
        $stores      = Stores::select('id', 'name')->whereNotNull('name')->orderBy('name')->pluck('name');
        $lockedStore = null;

    // ← ManageFingerspotSPVManager: hanya store kepunyaan, bisa pilih
    } elseif ($canSpvManager) {
        $stores      = $user->employee->store()
            ->orderBy('stores_tables.name')
            ->pluck('stores_tables.name');
        $lockedStore = null; // ← tidak locked, bisa pilih dari dropdown

    // ← ViewOwn: tidak ada dropdown
    } else {
        $stores      = collect();
        $lockedStore = null;
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
    private const STATIC_STORE_IDS = [
    '019623ad-de58-7368-8873-e3cbff2b0aff',
    '019963a7-cdb8-7002-b10b-163645c199d0',
    '019a230d-6146-7001-848d-046ccdbdf163',
];
    public function recap(Request $request)
    {
          set_time_limit(300);      // ← tambah ini
    ini_set('memory_limit', '512M');

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

         $employeesQuery = Employee::with([
            'store' => fn($q) => $q->wherePivot('is_primary', true)->select('stores_tables.id', 'stores_tables.name'),
        ])
        ->select('id', 'pin')
        ->whereNotNull('pin')
        ->whereNull('deleted_at');

         if ($request->store_name) {
        $employeesQuery->whereExists(function ($q) use ($request) {
            $q->select(DB::raw(1))
                ->from('employee_stores')
                ->join('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')
                ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                ->where('stores_tables.name', $request->store_name);
        });
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
    ->where('status', 'approved_hr') // ← tambah ini
    ->get()
    ->keyBy(fn($e) => (string)$e->pin . '_' . Carbon::parse($e->scan_date)->toDateString());

    $employeePrimaryStores = Employee::with([
    'store' => fn($q) => $q->wherePivot('is_primary', true)->select('stores_tables.id'),
])
->select('id')
->whereIn('id', $employeeIds)
->get()
->mapWithKeys(fn($e) => [
    $e->id => $e->store->first()?->id
]);
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
$isPHOnSunday = false;
if ($roster && $roster->day_type === 'Public Holiday') {
    $carbonDate     = Carbon::parse($date);
    $primaryStoreId = $employeePrimaryStores->get($employee->id);

    if ($carbonDate->isSunday() && in_array($primaryStoreId, self::STATIC_STORE_IDS)) {
        $isPHOnSunday = true;
        Log::info("Recap: PH on Sunday voided for employee {$employee->id} on {$date} (store: {$primaryStoreId})");
    }
}
                // is_counted = 1 HANYA kalau hari kerja (roster Work) DAN ada scan
                $isCounted = ($isWorkDay && $hasScan && !$isPHOnSunday) ? 1 : 0;
                

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
    // public function recap(Request $request)
    // {
    //     $user     = auth()->user();

    //     /** @var \App\Models\User|null $user */

    //     if (!$user->hasPermissionTo('ManageFingerspot')) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date'   => 'required|date|after_or_equal:start_date',
    //         'store_name' => 'nullable|string',
    //     ]);

    //     $startDate = Carbon::parse($request->start_date)->startOfDay();
    //     $endDate   = Carbon::parse($request->end_date)->endOfDay();

    //      $employeesQuery = Employee::with([
    //         'store' => fn($q) => $q->wherePivot('is_primary', true)->select('stores_tables.id', 'stores_tables.name'),
    //     ])
    //     ->select('id', 'pin')
    //     ->whereNotNull('pin')
    //     ->whereNull('deleted_at');

    //      if ($request->store_name) {
    //     $employeesQuery->whereExists(function ($q) use ($request) {
    //         $q->select(DB::raw(1))
    //             ->from('employee_stores')
    //             ->join('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')
    //             ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
    //             ->where('stores_tables.name', $request->store_name);
    //     });
    // }


    //      $employees = $employeesQuery->get()->keyBy(fn($e) => (string) $e->pin);

    // if ($employees->isEmpty()) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'There are no employees with registered PINs.',
    //     ], 422);
    // }

    //     $pins        = $employees->keys()->toArray();
    //     $employeeIds = $employees->pluck('id')->toArray();

    //     // ── Ambil raw scan dari att_log SAJA ──
    //     // (manual_added TIDAK perlu diambil — sudah di-handle oleh ManualRecapController)
    //     $rawScans = DB::connection('mysql_second')
    //         ->table('att_log')
    //         ->select('pin', 'scan_date', 'inoutmode', 'sn')
    //         ->whereIn('pin', $pins)
    //         ->whereBetween('scan_date', [$startDate, $endDate])
    //         ->whereIn('inoutmode', [1, 2])
    //         ->get();

    //     if ($rawScans->isEmpty()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'There is no fingerprint data in that date range.',
    //         ], 422);
    //     }

    //     // ── Pre-load roster untuk cek hari kerja ──
    //     $rosters = Roster::whereIn('employee_id', $employeeIds)
    //         ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
    //         ->get()
    //         ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

    //     // ── Pre-load existing manual recaps supaya TIDAK ditimpa ──
    //     $existingManualRecaps = Fingerprintrecap::whereIn('employee_id', $employeeIds)
    //         ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
    //         ->where('sync_status', self::SYNC_STATUS_MANUAL)
    //         ->get()
    //         ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

    //     // ── Group scans by pin & date ──
    //     $grouped = [];
    //     foreach ($rawScans as $scan) {
    //         $pin  = (string) $scan->pin;
    //         $date = Carbon::parse($scan->scan_date)->toDateString();
    //         $time = Carbon::parse($scan->scan_date)->format('H:i:s');
    //         $mode = (int) $scan->inoutmode;
    //         $sn   = $scan->sn ?? null;

    //         if (!isset($grouped[$pin][$date])) {
    //             $grouped[$pin][$date] = ['time_in' => null, 'time_out' => null, 'device_sn' => $sn];
    //         }

    //         if ($mode === 1 && $grouped[$pin][$date]['time_in'] === null) {
    //             $grouped[$pin][$date]['time_in'] = $time;
    //         }
    //         if ($mode === 2) {
    //             $grouped[$pin][$date]['time_out'] = $time;
    //         }
    //     }

    //     // ── Ambil edited_fingerprint untuk override time_in/time_out ──
       
    //     $editedFingerprints = DB::connection('mysql_second')
    // ->table('edited_fingerprint')
    // ->select('pin', 'scan_date', 'in_1', 'in_2')
    // ->whereIn('pin', $pins)
    // ->whereBetween('scan_date', [$startDate->toDateString(), $endDate->toDateString()])
    // ->where('status', 'approved_hr') // ← tambah ini
    // ->get()
    // ->keyBy(fn($e) => (string)$e->pin . '_' . Carbon::parse($e->scan_date)->toDateString());

    //     $synced            = 0;
    //     $skipped           = 0;
    //     $skippedManual     = 0;
    //     $errors            = [];

    //     foreach ($grouped as $pin => $dates) {
    //         $employee = $employees->get($pin);

    //         if (!$employee) {
    //             $skipped++;
    //             $errors[] = "PIN {$pin} was not found in employee data.";
    //             continue;
    //         }

    //         foreach ($dates as $date => $times) {
    //             $key = $employee->id . '_' . $date;

    //             // ── SKIP: data manual recap, jangan ditimpa ──
    //             if ($existingManualRecaps->has($key)) {
    //                 $skippedManual++;
    //                 Log::info("Recap: skip {$employee->id} on {$date} — already has Manual recap");
    //                 continue;
    //             }

    //             $timeIn   = $times['time_in'];
    //             $timeOut  = $times['time_out'];
    //             $deviceSn = $times['device_sn'];

    //             // Override dari edited_fingerprint kalau ada
    //             $editedKey = $pin . '_' . $date;
    //             $edited    = $editedFingerprints->get($editedKey);

    //             if ($edited) {
    //                 $timeIn  = $edited->in_1 ?? $timeIn;
    //                 $timeOut = $edited->in_2 ?? $timeOut;
    //             }

    //             // Hitung durasi
    //             $duration = null;
    //             if ($timeIn && $timeOut) {
    //                 $dtIn  = Carbon::parse($date . ' ' . $timeIn);
    //                 $dtOut = Carbon::parse($date . ' ' . $timeOut);
    //                 if ($dtOut->lt($dtIn)) $dtOut->addDay();
    //                 $duration = (int) $dtIn->diffInMinutes($dtOut);
    //             }

    //             // ── Cek roster untuk tentukan is_counted ──
    //             $roster    = $rosters->get($key);
    //             $isWorkDay = $roster && $roster->day_type === 'Work';
    //             $hasScan   = ($timeIn || $timeOut);

    //             // is_counted = 1 HANYA kalau hari kerja (roster Work) DAN ada scan
    //             $isCounted = ($isWorkDay && $hasScan) ? 1 : 0;

    //             Fingerprintrecap::updateOrCreate(

    //                 [
    //                     'employee_id' => $employee->id,
    //                     'date'        => $date,
    //                 ],
    //                 [
    //                     'pin'              => $pin,
    //                     'time_in'          => $timeIn,
    //                     'time_out'         => $timeOut,
    //                     'duration_minutes' => $duration,
    //                     'is_counted'       => $isCounted,
    //                     'device_sn'        => $deviceSn,
    //                     'sync_status'      => 'Synced',
    //                     'synced_at'        => now(),
    //                     'period_in'        => $startDate->toDateString(),
    //                     'period_out'       => $endDate->toDateString(),
    //                 ]
    //             );

    //             $synced++;
    //         }
    //     }

    //     // ════════════════════════════════════════════════════════════
    //     //  SIMPAN KE ARSIP (per store + periode)
    //     //  Dihitung via service yang sama dengan halaman Fingerprint Recap
    //     //  supaya angka arsip = angka tampilan.
    //     // ════════════════════════════════════════════════════════════
    //     try {
    //         // Ambil ulang employees lengkap (butuh status_employee + store untuk hitung)
    //         $employeesForArchive = Employee::with('store:id,name')
    //             ->select('id', 'employee_name', 'store_id', 'status_employee')
    //             ->whereIn('id', $employeeIds)
    //             ->get();

    //         $calculator = new FingerprintRecapCalculator();
    //         $archiveRows = $calculator->calculate(
    //             $employeesForArchive,
    //             $startDate->toDateString(),
    //             $endDate->toDateString()
    //         );

    //         DB::transaction(function () use ($archiveRows, $startDate, $endDate) {
    //             foreach ($archiveRows as $row) {
    //                 // Timpa: hapus arsip lama untuk (karyawan + store + periode) ini
    //                 Fingerprintrecaparchive::where('employee_id', $row['employee_id'])
    //                     ->where('store_name', $row['store_name'])
    //                     ->where('period_start', $startDate->toDateString())
    //                     ->where('period_end', $endDate->toDateString())
    //                     ->delete();

    //                 // Tulis arsip baru
    //                 Fingerprintrecaparchive::create([
    //                     'employee_id'      => $row['employee_id'],
    //                     'employee_name'    => $row['employee_name'],
    //                     'store_name'       => $row['store_name'],
    //                     'period_start'     => $startDate->toDateString(),
    //                     'period_end'       => $endDate->toDateString(),
    //                     'total_hari_kerja' => $row['total_hari_kerja'],
    //                     'total_hari_telat' => $row['total_hari_telat'],
    //                     'remarks'          => $row['remarks'],
    //                     'archived_by'      => optional(auth()->user())->id,
    //                 ]);
    //             }
    //         });
    //     } catch (\Exception $e) {
    //         Log::error('Recap: gagal simpan arsip', [
    //             'error' => $e->getMessage(),
    //             'line'  => $e->getLine(),
    //         ]);
    //         // Arsip gagal TIDAK membatalkan sync yang sudah berhasil.
    //         // Sync tetap sukses; arsip bisa diulang dengan recap lagi.
    //     }

    //     return response()->json([
    //         'success'        => true,
    //         'message'        => "Successfully summarized {$synced} attendance data. "
    //             . "({$skipped} unmatch PIN, {$skippedManual} skipped manual recap)",
    //         'synced'         => $synced,
    //         'skipped'        => $skipped,
    //         'skipped_manual' => $skippedManual,
    //         'errors'         => $errors,
    //     ]);
    // }


//     private function buildFingerprintResult(
//         $startDate,
//         $endDate,
//         $storeName,
//         $user,
//         bool $canManage,
//         bool $canSpvManager,
//         bool $canViewOwn
//     ): \Illuminate\Support\Collection {

//         $editedKeys = EditedFingerprint::whereBetween('scan_date', [$startDate, $endDate])
//             ->get(['pin', 'scan_date'])
//             ->map(fn($e) => $e->pin . '_' . Carbon::parse($e->scan_date)->toDateString())
//             ->values()
//             ->toArray();


// $employeesQuery = Employee::with(['position' => fn($q) => $q->wherePivot('is_primary', true), 'store' => fn($q) => $q->wherePivot('is_primary', true)])
//     ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'status_employee','status', 'company_id')
//     ->whereNotNull('pin');

// if ($canViewOwn && !$canManage && !$canSpvManager) {
//     // ← ViewFingerspot: hanya data sendiri
//     $employeesQuery->where('pin', $user->employee->pin);

// } 
// elseif ($canSpvManager && !$canManage) {
//     // ← ManageFingerspotSPVManager: filter company + store kepunyaan
//     $userEmployee  = $user->employee;
//     $companyId     = $userEmployee->company_id;
//     $userStoreIds  = $userEmployee->store()->pluck('stores_tables.id')->toArray();
//     $userDeptIds   = $userEmployee->department()->pluck('departments_tables.id')->toArray();

//     if (empty($userStoreIds) || empty($userDeptIds)) {
//         return collect();
//     }



//     $employeesQuery
//         ->where('company_id', $companyId)
//         ->whereExists(function ($q) use ($userStoreIds) {
//             $q->select(DB::raw(1))
//                 ->from('employee_stores')
//                 ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
//                 ->whereIn('employee_stores.store_id', $userStoreIds);
//         })
//         ->whereExists(function ($q) use ($userDeptIds) {
//             $q->select(DB::raw(1))
//                 ->from('employee_departments')
//                 ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
//                 ->whereIn('employee_departments.department_id', $userDeptIds);
//         });

//     if ($storeName) {
//         $employeesQuery->whereHas('store', fn($q) =>
//             $q->where('stores_tables.name', $storeName)
//         );
//     }

// } 
// else {
//     // ← ManageFingerspot: bebas filter store
//     if ($storeName) {
//         $employeesQuery->whereHas('store', fn($q) =>
//             $q->where('stores_tables.name', $storeName)
//         );
//     }
// }

//         $employees   = $employeesQuery->get()->keyBy('pin');
//         $employeeIds = $employees->pluck('id')->filter()->values()->toArray();

//         $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
//             ->select('id', 'employee_id', 'shift_id', 'date', 'day_type')
//             ->whereIn('employee_id', $employeeIds)
//             ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
//             ->get()
//             ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

//         $totalHariPerEmployee = Fingerprintrecap::select('employee_id', DB::raw('SUM(is_counted) as total_hari'))
//             ->whereIn('employee_id', $employeeIds)
//             ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
//             ->groupBy('employee_id')
//             ->pluck('total_hari', 'employee_id');

//         $pins         = $employees->keys()->toArray();
//         $fingerprints = Fingerprints::select(['sn', 'scan_date', 'pin', 'inoutmode'])
//             ->whereIn('pin', $pins)
//             ->whereBetween('scan_date', [$startDate, $endDate])
//             ->orderBy('pin')
//             ->orderBy('scan_date')
//             ->get();

//         $deviceNames = Devicefingerprint::select('sn', 'device_name')
//             ->get()
//             ->keyBy('sn')
//             ->map(fn($d) => $d->device_name ?? '-');

//         $grouped = $fingerprints
//             ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

//         return $grouped->map(function ($group, $key) use (
//             $employees,
//             $totalHariPerEmployee,
//             $editedKeys,
//             $rosters,
//             $deviceNames,
//             $canManage,
//             $canSpvManager,
//             $canViewOwn
//         ) {
//             $first    = $group->first();
//             $pin      = $first->pin;
//             $scanDate = Carbon::parse($first->scan_date)->toDateString();
//             $employee = $employees->get($pin);

//             if (!$employee) return null;

//             $rosterKey  = $employee->id . '_' . $scanDate;
//             $roster     = $rosters->get($rosterKey);
//             $rosterName = '-';
//             $rosterTime = '';

//             if ($roster) {
//                 if ($roster->day_type !== 'Work') {
//                     $rosterName = $roster->day_type;
//                 } elseif ($roster->shift) {
//                     $rosterName = $roster->shift->shift_name;
//                     $rosterTime = substr($roster->shift->start_time, 0, 5)
//                         . ' - ' . substr($roster->shift->end_time, 0, 5);
//                 }
//             }

//             $totalHari = $totalHariPerEmployee->get($employee->id, 0);

//             $row = [
//                 'pin'               => $pin,
//                 'employee_name'     => $employee->employee_name     ?? '-',
//                 'status_employee'   => $employee->status_employee   ?? '-',
//                 'status'   => $employee->status   ?? '-',
//                 'employee_pengenal' => $employee->employee_pengenal ?? '-',
//                 'name'              => $employee->store->first()?->name      ?? '-', // ← fix
//     'position_name'     => $employee->position->first()?->name   ?? '-', // ← fix
//                 'device_name'       => $deviceNames->get($first->sn) ?? '-',
//                 'scan_date'         => $scanDate,
//                 'total_hari'        => $totalHari . ' Hari',
//                 'roster_name'       => $rosterName,
//                 'roster_time'       => $rosterTime,
//             ];

//             for ($i = 1; $i <= 10; $i++) {
//                 $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
//             }

//             $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row, $deviceNames) {
//                 if ($mode < 1 || $mode > 10) return;
//                 $sorted  = $items->sortBy('scan_date');
//                 $times   = $sorted->pluck('scan_date')->map(fn($d) => Carbon::parse($d)->format('H:i:s'))->implode(', ');
//                 $devices = $sorted->map(fn($i) => $deviceNames->get($i->sn) ?? '')->implode(', ');
//                 $row["in_$mode"]      = $times;
//                 $row["device_$mode"]  = $devices;
//                 $row["combine_$mode"] = trim($times . ' ' . $devices);
//             });

//             $times = collect(range(1, 10))
//                 ->flatMap(fn($i) => $row["in_$i"] ? explode(', ', $row["in_$i"]) : [])
//                 ->map(fn($t) => Carbon::parse($t))
//                 ->sort()
//                 ->values();

//             if ($times->count() >= 2) {
//                 $minutes         = $times->first()->diffInMinutes($times->last());
//                 $row['duration'] = sprintf(
//                     '%d hour%s %d minute%s',
//                     intdiv($minutes, 60),
//                     intdiv($minutes, 60) !== 1 ? 's' : '',
//                     $minutes % 60,
//                     $minutes % 60 !== 1 ? 's' : ''
//                 );
//             } else {
//                 $row['duration'] = 'invalid';
//             }

//             $row['is_updated']     = in_array($key, $editedKeys);
//             $row['updated_status'] = $row['is_updated'] ? 'Updated' : 'Original';
//             $row['is_late_in']     = false;
//             $row['is_late']        = false;
//             $row['late_minutes']   = 0;

//             if ($roster && $roster->day_type === 'Work' && $roster->shift && $row['in_1']) {
//                 $firstIn = trim(explode(',', $row['in_1'])[0]);
//                 if ($firstIn) {
//                     $shiftStart  = Carbon::parse($scanDate . ' ' . $roster->shift->start_time);
//                     $actualIn    = Carbon::parse($scanDate . ' ' . $firstIn);
                 
//                     $toleransi = in_array($employee->store->first()?->name ?? '', self::TOLERANSI_TINGGI_STORES)
//     ? self::TOLERANSI_TINGGI_MENIT
//     : self::TOLERANSI_NORMAL_MENIT;
//                     $batasMasuk  = $shiftStart->copy()->addMinutes($toleransi);
//                     $lateMinutes = max(0, $shiftStart->diffInMinutes($actualIn, false));

//                     if ($actualIn->gt($batasMasuk)) {
//                         $row['is_late_in']   = true;
//                         $row['is_late']      = true;
//                         $row['late_minutes'] = $lateMinutes;
//                     }
//                 }
//             }

//             $row['can_action'] = $canManage;

//             return $row;
//         })->filter()->values();
//     }


// ini acuan /
// private function buildFingerprintResult(
//     $startDate,
//     $endDate,
//     $storeName,
//     $user,
//     bool $canManage,
//     bool $canSpvManager,
//     bool $canViewOwn
// ): \Illuminate\Support\Collection {
//     $editedFingerprints = EditedFingerprint::whereBetween('scan_date', [$startDate, $endDate])
//     ->get(['id', 'pin', 'scan_date', 'status'])
//     ->keyBy(fn($e) => $e->pin . '_' . Carbon::parse($e->scan_date)->toDateString());

// $editedKeys = $editedFingerprints->keys()->toArray();

//     $employeesQuery = Employee::with([
//         'position' => fn($q) => $q->wherePivot('is_primary', true),
//         'store'    => fn($q) => $q->wherePivot('is_primary', true),
//     ])
//     ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'status_employee', 'status', 'company_id')
//     ->whereNotNull('pin');

//     if ($canViewOwn && !$canManage && !$canSpvManager) {
//         // ← ViewFingerspot: hanya data sendiri
//         $employeesQuery->where('pin', $user->employee->pin);

//     } 
    
//     elseif ($canSpvManager && !$canManage) {
//     $userEmployee = $user->employee;
//     $companyId    = $userEmployee->company_id;
//     $userStoreIds = $userEmployee->store()->pluck('stores_tables.id')->toArray();
//     $userDeptIds  = $userEmployee->department()->pluck('departments_tables.id')->toArray();

//     // ← Ambil bawahan langsung dari pivot (bisa beda company)
//     $bawahanIds = $userEmployee->bawahanList()->pluck('employees_tables.id')->toArray();

//     if (empty($userStoreIds) || empty($userDeptIds)) {
//         // Tidak punya store/department — hanya tampilkan bawahan langsung
//         if (empty($bawahanIds)) {
//             return collect();
//         }
//         $employeesQuery->whereIn('id', $bawahanIds);
//     } else {
//         $employeesQuery->where(function ($q) use ($companyId, $userStoreIds, $userDeptIds, $bawahanIds) {
//             // ← Kondisi 1: sama company + store + department
//             $q->where(function ($q1) use ($companyId, $userStoreIds, $userDeptIds) {
//                 $q1->where('company_id', $companyId)
//                     ->whereExists(function ($sq) use ($userStoreIds) {
//                         $sq->select(DB::raw(1))
//                             ->from('employee_stores')
//                             ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
//                             ->whereIn('employee_stores.store_id', $userStoreIds);
//                     })
//                     ->whereExists(function ($sq) use ($userDeptIds) {
//                         $sq->select(DB::raw(1))
//                             ->from('employee_departments')
//                             ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
//                             ->whereIn('employee_departments.department_id', $userDeptIds);
//                     });
//             });

//             // ← Kondisi 2: bawahan langsung via pivot (beda company sekalipun)
//             if (!empty($bawahanIds)) {
//                 $q->orWhereIn('id', $bawahanIds);
//             }
//         });

//         // ← Filter store yang dipilih, validasi harus kepunyaan SPVManager
//         if ($storeName) {
//             $allowedStoreNames = $userEmployee->store()
//                 ->pluck('stores_tables.name')
//                 ->toArray();

//             if (in_array($storeName, $allowedStoreNames)) {
//                 $employeesQuery->whereHas('store', fn($q) =>
//                     $q->where('stores_tables.name', $storeName)
//                 );
//             }
//         }
//     }
// }
//     elseif ($canSpvManager && !$canManage) {
//     $userEmployee = $user->employee;
//     $companyId    = $userEmployee->company_id;
//     $userStoreIds = $userEmployee->store()->pluck('stores_tables.id')->toArray();
//     $userDeptIds  = $userEmployee->department()->pluck('departments_tables.id')->toArray();

//     // ← Ambil bawahan langsung dari pivot employee_atasans
//     $bawahanIds = $userEmployee->bawahanList()->pluck('employees_tables.id')->toArray();

//     if (empty($userStoreIds) || empty($userDeptIds)) {
//         // Kalau tidak punya store/department, hanya tampilkan bawahan langsung
//         if (empty($bawahanIds)) {
//             return collect();
//         }
//         $employeesQuery->whereIn('id', $bawahanIds);
//     } else {
//         $employeesQuery
//             ->where('company_id', $companyId)
//             ->where(function ($q) use ($userStoreIds, $userDeptIds, $bawahanIds) {
//                 // ← Kondisi 1: employee di store + department yang sama
//                 $q->where(function ($q1) use ($userStoreIds, $userDeptIds) {
//                     $q1->whereExists(function ($sq) use ($userStoreIds) {
//                         $sq->select(DB::raw(1))
//                             ->from('employee_stores')
//                             ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
//                             ->whereIn('employee_stores.store_id', $userStoreIds);
//                     })
//                     ->whereExists(function ($sq) use ($userDeptIds) {
//                         $sq->select(DB::raw(1))
//                             ->from('employee_departments')
//                             ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
//                             ->whereIn('employee_departments.department_id', $userDeptIds);
//                     });
//                 });

//                 // ← Kondisi 2: bawahan langsung via pivot (beda company/store/department sekalipun)
//                 if (!empty($bawahanIds)) {
//                     $q->orWhereIn('id', $bawahanIds);
//                 }
//             });

//         // ← Filter store yang dipilih, validasi harus kepunyaan SPVManager
//         if ($storeName) {
//             $allowedStoreNames = $userEmployee->store()
//                 ->pluck('stores_tables.name')
//                 ->toArray();

//             if (in_array($storeName, $allowedStoreNames)) {
//                 $employeesQuery->whereHas('store', fn($q) =>
//                     $q->where('stores_tables.name', $storeName)
//                 );
//             }
//         }
//     }
// }
//     else {
//         // ← ManageFingerspot: bebas filter store
//         if ($storeName) {
//             $employeesQuery->whereHas('store', fn($q) =>
//                 $q->where('stores_tables.name', $storeName)
//             );
//         }
//     }

//     $employees   = $employeesQuery->get()->keyBy('pin');
//     $employeeIds = $employees->pluck('id')->filter()->values()->toArray();

//     $rosters = Roster::with('shift:id,shift_name,start_time,end_time')
//         ->select('id', 'employee_id', 'shift_id', 'date', 'day_type')
//         ->whereIn('employee_id', $employeeIds)
//         ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
//         ->get()
//         ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

//     $totalHariPerEmployee = Fingerprintrecap::select('employee_id', DB::raw('SUM(is_counted) as total_hari'))
//         ->whereIn('employee_id', $employeeIds)
//         ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
//         ->groupBy('employee_id')
//         ->pluck('total_hari', 'employee_id');

//     $pins         = $employees->keys()->toArray();
//     $fingerprints = Fingerprints::select(['sn', 'scan_date', 'pin', 'inoutmode'])
//         ->whereIn('pin', $pins)
//         ->whereBetween('scan_date', [$startDate, $endDate])
//         ->orderBy('pin')
//         ->orderBy('scan_date')
//         ->get();

//     $deviceNames = Devicefingerprint::select('sn', 'device_name')
//         ->get()
//         ->keyBy('sn')
//         ->map(fn($d) => $d->device_name ?? '-');

//     $grouped = $fingerprints
//         ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

//     return $grouped->map(function ($group, $key) use (
//         $employees,
//         $totalHariPerEmployee,
//         $editedKeys,
//          $editedFingerprints, 
//         $rosters,
//         $deviceNames,
//         $canManage,
//         $canSpvManager,
//         $canViewOwn
//     ) {
//         $first    = $group->first();
//         $pin      = $first->pin;
//         $scanDate = Carbon::parse($first->scan_date)->toDateString();
//         $employee = $employees->get($pin);

//         if (!$employee) return null;

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
//                     . ' - ' . substr($roster->shift->end_time, 0, 5);
//             }
//         }

//         $totalHari = $totalHariPerEmployee->get($employee->id, 0);
//         $editedRecord = $editedFingerprints->get($key) ?? null;
        


//         $row = [
//             'pin'               => $pin,
//             'employee_name'     => $employee->employee_name     ?? '-',
//             'status_employee'   => $employee->status_employee   ?? '-',
//             'status'            => $employee->status            ?? '-',
//             'employee_pengenal' => $employee->employee_pengenal ?? '-',
//             'name'              => $employee->store->first()?->name    ?? '-',
//             'position_name'     => $employee->position->first()?->name ?? '-',
//             'device_name'       => $deviceNames->get($first->sn) ?? '-',
//             'scan_date'         => $scanDate,
//             'total_hari'        => $totalHari . ' Hari',
//             'roster_name'       => $rosterName,
//             'roster_time'       => $rosterTime,
//                'edited_fingerprint_id' => $editedRecord?->id,
//     'edited_status'         => $editedRecord?->status ?? null,
//     'can_action'            => $canManage || $canSpvManager,
//         ];

//         for ($i = 1; $i <= 10; $i++) {
//             $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
//         }

//         $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row, $deviceNames) {
//             if ($mode < 1 || $mode > 10) return;
//             $sorted  = $items->sortBy('scan_date');
//             $times   = $sorted->pluck('scan_date')->map(fn($d) => Carbon::parse($d)->format('H:i:s'))->implode(', ');
//             $devices = $sorted->map(fn($i) => $deviceNames->get($i->sn) ?? '')->implode(', ');
//             $row["in_$mode"]      = $times;
//             $row["device_$mode"]  = $devices;
//             $row["combine_$mode"] = trim($times . ' ' . $devices);
//         });

//         $times = collect(range(1, 10))
//             ->flatMap(fn($i) => $row["in_$i"] ? explode(', ', $row["in_$i"]) : [])
//             ->map(fn($t) => Carbon::parse($t))
//             ->sort()
//             ->values();

//         if ($times->count() >= 2) {
//             $minutes         = $times->first()->diffInMinutes($times->last());
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
//         $row['is_late_in']     = false;
//         $row['is_late']        = false;
//         $row['late_minutes']   = 0;

//         if ($roster && $roster->day_type === 'Work' && $roster->shift && $row['in_1']) {
//             $firstIn = trim(explode(',', $row['in_1'])[0]);
//             if ($firstIn) {
//                 $shiftStart = Carbon::parse($scanDate . ' ' . $roster->shift->start_time);
//                 $actualIn   = Carbon::parse($scanDate . ' ' . $firstIn);

//                 $toleransi = in_array($employee->store->first()?->name ?? '', self::TOLERANSI_TINGGI_STORES)
//                     ? self::TOLERANSI_TINGGI_MENIT
//                     : self::TOLERANSI_NORMAL_MENIT;

//                 $batasMasuk  = $shiftStart->copy()->addMinutes($toleransi);
//                 $lateMinutes = max(0, $shiftStart->diffInMinutes($actualIn, false));

//                 if ($actualIn->gt($batasMasuk)) {
//                     $row['is_late_in']   = true;
//                     $row['is_late']      = true;
//                     $row['late_minutes'] = $lateMinutes;
//                 }
//             }
//         }

//         $row['can_action'] = $canManage;

//         return $row;
//     })->filter()->values();
// }


private function buildFingerprintResult(
    $startDate,
    $endDate,
    $storeName,
    $status,       // ← tambah parameter
    $user,
    bool $canManage,
    bool $canSpvManager,
    bool $canViewOwn
): \Illuminate\Support\Collection {

    $editedFingerprints = EditedFingerprint::whereBetween('scan_date', [$startDate, $endDate])
        ->get(['id', 'pin', 'scan_date', 'status'])
        ->keyBy(fn($e) => $e->pin . '_' . Carbon::parse($e->scan_date)->toDateString());

    $editedKeys = $editedFingerprints->keys()->toArray();

    $employeesQuery = Employee::with([
        'position' => fn($q) => $q->wherePivot('is_primary', true),
        'store'    => fn($q) => $q->wherePivot('is_primary', true),
    ])
    ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'status_employee', 'status', 'company_id')
    ->whereNotNull('pin');

    if ($canViewOwn && !$canManage && !$canSpvManager) {
        $employeesQuery->where('pin', $user->employee->pin);

    } elseif ($canSpvManager && !$canManage) {
        // ← Satu blok saja, hapus duplikat
        $userEmployee = $user->employee;
        $companyId    = $userEmployee->company_id;
        $userStoreIds = $userEmployee->store()->pluck('stores_tables.id')->toArray();
        $userDeptIds  = $userEmployee->department()->pluck('departments_tables.id')->toArray();
        $bawahanIds   = $userEmployee->bawahanList()->pluck('employees_tables.id')->toArray();

        if (empty($userStoreIds) || empty($userDeptIds)) {
            if (empty($bawahanIds)) return collect();
            $employeesQuery->whereIn('id', $bawahanIds);
        } else {
            $employeesQuery->where(function ($q) use ($companyId, $userStoreIds, $userDeptIds, $bawahanIds) {
                $q->where(function ($q1) use ($companyId, $userStoreIds, $userDeptIds) {
                    $q1->where('company_id', $companyId)
                        ->whereExists(function ($sq) use ($userStoreIds) {
                            $sq->select(DB::raw(1))
                                ->from('employee_stores')
                                ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                                ->whereIn('employee_stores.store_id', $userStoreIds);
                        })
                        ->whereExists(function ($sq) use ($userDeptIds) {
                            $sq->select(DB::raw(1))
                                ->from('employee_departments')
                                ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                                ->whereIn('employee_departments.department_id', $userDeptIds);
                        });
                });

                if (!empty($bawahanIds)) {
                    $q->orWhereIn('id', $bawahanIds);
                }
            });

            if ($storeName) {
                $allowedStoreNames = $userEmployee->store()->pluck('stores_tables.name')->toArray();
                if (in_array($storeName, $allowedStoreNames)) {
                    $employeesQuery->whereHas('store', fn($q) =>
                        $q->where('stores_tables.name', $storeName)
                    );
                }
            }
        }

    } else {
        // ManageFingerspot: bebas filter store
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


    // ... query rosters, fingerprints, dll tetap sama ...

    $result = $grouped->map(function ($group, $key) use (
        $employees,
        $totalHariPerEmployee,
        $editedKeys,
        $editedFingerprints,
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
        $editedRecord = $editedFingerprints->get($key) ?? null;
        


        $row = [
            'pin'               => $pin,
            'employee_name'     => $employee->employee_name     ?? '-',
            'status_employee'   => $employee->status_employee   ?? '-',
            'status'            => $employee->status            ?? '-',
            'employee_pengenal' => $employee->employee_pengenal ?? '-',
            'name'              => $employee->store->first()?->name    ?? '-',
            'position_name'     => $employee->position->first()?->name ?? '-',
            'device_name'       => $deviceNames->get($first->sn) ?? '-',
            'scan_date'         => $scanDate,
            'total_hari'        => $totalHari . ' Hari',
            'roster_name'       => $rosterName,
            'roster_time'       => $rosterTime,
               'edited_fingerprint_id' => $editedRecord?->id,
    'edited_status'         => $editedRecord?->status ?? null,
    'can_action'            => $canManage || $canSpvManager,
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
                $shiftStart = Carbon::parse($scanDate . ' ' . $roster->shift->start_time);
                $actualIn   = Carbon::parse($scanDate . ' ' . $firstIn);

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

    // ← Filter status di akhir
    if ($status) {
        $allowedStatuses = $canManage
            ? ['draft', 'approved_spv', 'rejected_spv', 'approved_hr', 'rejected_hr']
            : ['draft', 'approved_spv', 'rejected_spv'];

        if (in_array($status, $allowedStatuses)) {
            $result = $result->filter(fn($r) =>
                ($r['edited_status'] ?? null) === $status
            )->values();
        }
    }
    return $result;
}
// elseif ($canSpvManager && !$canManage) {
    //     // ← ManageFingerspotSPVManager: filter company + semua store kepunyaan
    //     $userEmployee = $user->employee;
    //     $companyId    = $userEmployee->company_id;
    //     $userStoreIds = $userEmployee->store()->pluck('stores_tables.id')->toArray();
    //     $userDeptIds  = $userEmployee->department()->pluck('departments_tables.id')->toArray();

    //     if (empty($userStoreIds) || empty($userDeptIds)) {
    //         return collect();
    //     }

    //     $employeesQuery
    //         ->where('company_id', $companyId)
    //         ->whereExists(function ($q) use ($userStoreIds) {
    //             $q->select(DB::raw(1))
    //                 ->from('employee_stores')
    //                 ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
    //                 ->whereIn('employee_stores.store_id', $userStoreIds);
    //         })
    //         ->whereExists(function ($q) use ($userDeptIds) {
    //             $q->select(DB::raw(1))
    //                 ->from('employee_departments')
    //                 ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
    //                 ->whereIn('employee_departments.department_id', $userDeptIds);
    //         });

    //     // ← Filter store yang dipilih, validasi harus kepunyaan SPVManager
    //     if ($storeName) {
    //         $allowedStoreNames = $userEmployee->store()
    //             ->pluck('stores_tables.name')
    //             ->toArray();

    //         if (in_array($storeName, $allowedStoreNames)) {
    //             $employeesQuery->whereHas('store', fn($q) =>
    //                 $q->where('stores_tables.name', $storeName)
    //             );
    //         }
    //         // Kalau store tidak kepunyaan SPVManager, abaikan filter
    //     }

    // } 
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
              'status'     => 'nullable|string', 
            
        ]);

        $storeName = $request->input('store_name');
        $status    = $request->input('status');
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
        $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();
      if (!$canManage && ($canSpvManager || $canViewOwn)) {
    // ← Min: 26 dua bulan lalu (boleh lihat 1 periode ke belakang)
    $minAllowedDate = now()->subMonths(2)->day(26)->startOfDay();
    // ← Max: 25 bulan depan
    $maxAllowedDate = now()->addMonth()->day(25)->endOfDay();

    if ($startDate->lt($minAllowedDate)) {
        $startDate = $minAllowedDate;
    }

    if ($endDate->gt($maxAllowedDate)) {
        $endDate = $maxAllowedDate;
    }

    if ($endDate->lt($minAllowedDate)) {
        abort(422, 'Rentang tanggal tidak diizinkan.');
    }
}
        $result = $this->buildFingerprintResult(
            $startDate,
            $endDate,
            $storeName,
            $status, 
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

$spvPin = ($canSpvManager && !$canManage) ? $user->Employee?->pin : null;

        return DataTables::of($result)
            ->with(['stats' => $stats])
            ->addColumn('in_1_colored', function ($row) {
                if (!$row['in_1']) return '-';
                return $row['is_late']
                    ? '<span class="text-danger fw-bold">' . $row['in_1'] . '</span>'
                    : '<span class="text-success">' . $row['in_1'] . '</span>';
            })
            
->addColumn('action', function ($row) use ($canManage, $canSpvManager, $canViewOwn, $spvPin) {
  $actions = '';

    // ── Tombol show (semua permission bisa lihat) ──
    if (!empty($row['edited_fingerprint_id'])) {
        $showUrl = route('pages.Fingerprints.show', [
            'pin'       => $row['pin'],
            'scan_date' => $row['scan_date'],
        ]);
        $actions .= '<a href="' . $showUrl . '" class="btn btn-sm btn-info me-1" title="Detail">
            <i class="fas fa-eye"></i>
        </a>';
    }

    // ── Tombol edit ──
    if ($row['is_updated']) {
        $canEdit = $canManage
            || ($canSpvManager && $row['pin'] === $spvPin && in_array($row['edited_status'], ['draft', 'rejected_spv']))
            || ($canViewOwn && in_array($row['edited_status'], ['draft', 'rejected_spv']));

        if ($canEdit) {
            $editUrl = route('pages.Fingerprints.edit', [
                'pin'       => $row['pin'],
                'scan_date' => $row['scan_date'],
            ]);
            $actions .= '<a href="' . $editUrl . '" class="btn btn-sm btn-primary me-1" title="Edit">
                <i class="fas fa-edit"></i>
            </a>';
        }
        // ← hapus else disabled, kalau tidak bisa edit tidak tampil apapun

    } else {
        // Belum pernah diedit
        $canEditNew = $canManage
            || ($canSpvManager && $row['pin'] === $spvPin) // ← SPV hanya pin sendiri
            || $canViewOwn;

        if ($canEditNew) {
            $editUrl = route('pages.Fingerprints.edit', [
                'pin'       => $row['pin'],
                'scan_date' => $row['scan_date'],
            ]);
            $actions .= '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                <i class="fas fa-edit"></i>
            </a>';
        }
    }

    return $actions;
})

->rawColumns(['action', 'in_1_colored'])
->make(true);

    }
//     public function getFingerprints(Request $request)
//     {
//         ini_set('memory_limit', '1024M');
//         set_time_limit(300);
//         $user = auth()->user();

//         /** @var \App\Models\User|null $user */

//         $canManage     = $user->hasPermissionTo('ManageFingerspot');
//         $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
//         $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');

//         if (!$canManage && !$canSpvManager && !$canViewOwn) {
//             abort(403, 'Unauthorized');
//         }

//         $request->validate([
//             'start_date' => 'nullable|date',
//             'end_date'   => 'nullable|date|after_or_equal:start_date',
//             'store_name' => 'nullable|string|max:100',
//         ]);

//         $storeName = $request->input('store_name');
//         $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
//         $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();
//       if (!$canManage && ($canSpvManager || $canViewOwn)) {
//     // ← Min: 26 dua bulan lalu (boleh lihat 1 periode ke belakang)
//     $minAllowedDate = now()->subMonths(2)->day(26)->startOfDay();
//     // ← Max: 25 bulan depan
//     $maxAllowedDate = now()->addMonth()->day(25)->endOfDay();

//     if ($startDate->lt($minAllowedDate)) {
//         $startDate = $minAllowedDate;
//     }

//     if ($endDate->gt($maxAllowedDate)) {
//         $endDate = $maxAllowedDate;
//     }

//     if ($endDate->lt($minAllowedDate)) {
//         abort(422, 'Rentang tanggal tidak diizinkan.');
//     }
// }
//         $result = $this->buildFingerprintResult(
//             $startDate,
//             $endDate,
//             $storeName,
//             $user,
//             $canManage,
//             $canSpvManager,
//             $canViewOwn
//         );

//         $stats = [
//             'total'   => $result->count(),
//             'on_time' => $result->where('is_late', false)->count(),
//             'late'    => $result->where('is_late', true)->count(),
//             'updated' => $result->where('is_updated', true)->count(),
//             'missing' => $result->filter(fn($r) => empty($r['in_2']))->count(),
//         ];

// $spvPin = ($canSpvManager && !$canManage) ? $user->Employee?->pin : null;

//         return DataTables::of($result)
//             ->with(['stats' => $stats])
//             ->addColumn('in_1_colored', function ($row) {
//                 if (!$row['in_1']) return '-';
//                 return $row['is_late']
//                     ? '<span class="text-danger fw-bold">' . $row['in_1'] . '</span>'
//                     : '<span class="text-success">' . $row['in_1'] . '</span>';
//             })
            
// ->addColumn('action', function ($row) use ($canManage, $canSpvManager, $canViewOwn, $spvPin) {
//   $actions = '';

//     // ── Tombol show (semua permission bisa lihat) ──
//     if (!empty($row['edited_fingerprint_id'])) {
//         $showUrl = route('pages.Fingerprints.show', [
//             'pin'       => $row['pin'],
//             'scan_date' => $row['scan_date'],
//         ]);
//         $actions .= '<a href="' . $showUrl . '" class="btn btn-sm btn-info me-1" title="Detail">
//             <i class="fas fa-eye"></i>
//         </a>';
//     }

//     // ── Tombol edit ──
//     if ($row['is_updated']) {
//         $canEdit = $canManage
//             || ($canSpvManager && $row['pin'] === $spvPin && in_array($row['edited_status'], ['draft', 'rejected_spv']))
//             || ($canViewOwn && in_array($row['edited_status'], ['draft', 'rejected_spv']));

//         if ($canEdit) {
//             $editUrl = route('pages.Fingerprints.edit', [
//                 'pin'       => $row['pin'],
//                 'scan_date' => $row['scan_date'],
//             ]);
//             $actions .= '<a href="' . $editUrl . '" class="btn btn-sm btn-primary me-1" title="Edit">
//                 <i class="fas fa-edit"></i>
//             </a>';
//         }
//         // ← hapus else disabled, kalau tidak bisa edit tidak tampil apapun

//     } else {
//         // Belum pernah diedit
//         $canEditNew = $canManage
//             || ($canSpvManager && $row['pin'] === $spvPin) // ← SPV hanya pin sendiri
//             || $canViewOwn;

//         if ($canEditNew) {
//             $editUrl = route('pages.Fingerprints.edit', [
//                 'pin'       => $row['pin'],
//                 'scan_date' => $row['scan_date'],
//             ]);
//             $actions .= '<a href="' . $editUrl . '" class="btn btn-sm btn-outline-primary me-1" title="Edit">
//                 <i class="fas fa-edit"></i>
//             </a>';
//         }
//     }

//     return $actions;
// })

// ->rawColumns(['action', 'in_1_colored'])
// ->make(true);

//     }
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
    $status    = $request->input('status'); // ← tambah
    $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
    $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

    // ← Update date range validation sama seperti getFingerprints
    if (!$canManage && $canSpvManager) {
        $minAllowedDate = now()->subMonths(2)->day(26)->startOfDay();
        $maxAllowedDate = now()->addMonth()->day(25)->endOfDay();

        if ($startDate->lt($minAllowedDate)) $startDate = $minAllowedDate;
        if ($endDate->gt($maxAllowedDate))   $endDate   = $maxAllowedDate;
    }

    $result = $this->buildFingerprintResult(
        $startDate,
        $endDate,
        $storeName,
        $status,    // ← tambah parameter
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
    // public function exportfingerprints(Request $request)
    // {
    //     $user = auth()->user();

    //     /** @var \App\Models\User|null $user */

    //     $canManage     = $user->hasPermissionTo('ManageFingerspot');
    //     $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');

    //     if (!$canManage && !$canSpvManager) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $storeName = $request->input('store_name');
    //     $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
    //     $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

    //     if (!$canManage && $canSpvManager) {
    //         $minAllowedDate = now()->subMonth()->startOfDay();
    //         if ($startDate->lt($minAllowedDate)) $startDate = $minAllowedDate;
    //     }

    //     $result = $this->buildFingerprintResult(
    //         $startDate,
    //         $endDate,
    //         $storeName,
    //         $user,
    //         $canManage,
    //         $canSpvManager,
    //         false
    //     );

    //     $exportType = $request->input('export', 'excel');
    //     $filename   = 'fingerprints_' . $startDate->toDateString() . '_' . $endDate->toDateString();
    //     $export     = new \App\Exports\FingerprintsExport($result, $storeName ?? '');

    //     return $exportType === 'csv'
    //         ? Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV)
    //         : Excel::download($export, $filename . '.xlsx');
    // }
    


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

        $employeesQuery = Employee::with([
    'store'    => fn($q) => $q->wherePivot('is_primary', true),
    'position' => fn($q) => $q->wherePivot('is_primary', true),
])
->select('id', 'pin', 'employee_name', 'employee_pengenal', 'status_employee')
->whereNotNull('pin');

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
    //         $manualLogs = DB::table('manual_recap_logs')
    // ->whereIn('employee_id', $employeeIds)
    // ->whereBetween('created_at', [$startDate, $endDate])
    // ->get()
    // ->groupBy('employee_id');
    $manualLogs = DB::table('manual_recap_logs')
    ->whereIn('employee_id', $employeeIds)
    ->get() // ← hapus filter date, ambil semua log per employee
    ->groupBy('employee_id');

        if ($manualData->isEmpty()) {
            return DataTables::of(collect([]))->make(true);
        }

        $grouped = collect($manualData)
            ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

        $result = $grouped->map(function ($group, $key) use ($employees, $rosters, $manualLogs) {
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
$employeeId = $employee->id;
    $row['evidences'] = $manualLogs->get($employeeId, collect())
        ->map(fn($log) => [
            'file_name' => $log->file_name,
            'file_path' => $log->file_path,
            'mime_type' => $log->mime_type ?? '',
            'reason'    => $log->reason ?? '-',
        ])->values()->toArray();
            return $row;
        })->filter()->values();

        return DataTables::of($result)
            // ->addColumn('action', function ($row) {
            //     return '<span class="fp-badge fp-badge-updated" style="font-size:.7rem;padding:.3rem .7rem">
            //                 <i class="fas fa-check me-1"></i>Manual Added
            //             </span>';
            // })
            ->addColumn('action', function ($row) {
    $badge = '<span class="fp-badge fp-badge-updated" style="font-size:.7rem;padding:.3rem .7rem">
                <i class="fas fa-check me-1"></i>Manual Added
              </span>';

    if (!empty($row['evidences'])) {
        $evidenceJson = htmlspecialchars(json_encode($row['evidences']), ENT_QUOTES);
        $badge .= ' <button class="btn btn-sm btn-outline-info ms-1 btn-show-evidence"
                        data-evidences="' . $evidenceJson . '"
                        data-reason="' . htmlspecialchars($row['evidences'][0]['reason'] ?? '-', ENT_QUOTES) . '"
                        title="See">
                        <i class="fas fa-paperclip"></i>
                    </button>';
    }

    return $badge;
})
            ->rawColumns(['action'])
            ->make(true);
    }
    
    public function editFingerprint($pin, Request $request)
    {
        $user = auth()->user();

    /** @var \App\Models\User|null $user */

    $canManage     = $user->hasPermissionTo('ManageFingerspot');
    $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
    $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');

    if (!$canManage && !$canSpvManager && !$canViewOwn) {
        abort(403, 'Unauthorized');
    }

   
    if ($canViewOwn && !$canManage && !$canSpvManager) {
    $employeePin = $user->employee?->pin;
    if ($pin !== $employeePin) {
        abort(403, 'Anda hanya bisa edit data fingerprint milik sendiri.');
    }
}
if ($canSpvManager && !$canManage) {
    $employeePin = $user->employee?->pin;
    if ($pin !== $employeePin) {
        abort(403, 'Anda hanya bisa edit data fingerprint milik sendiri.');
    }
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
        $data = EditedFingerprint::with('attachments')
        // $data = EditedFingerprint::with('devicefingerprints','attachments')
            ->where('pin', $pin)
            ->whereDate('scan_date', $scanDateCarbon)
            ->first();

            if ($data && $data->status === 'approved_hr') {
    return redirect()->route('pages.Fingerprints')
        ->with('error', 'Data fingerprint sudah di-approve HR, sudah tidak bisa untuk klarifikasi.');
}

       
        if ($data) {
    // Guard: ViewFingerspot tidak bisa edit jika sudah approved_spv atau approved_hr
    if ($canViewOwn && !$canManage && !$canSpvManager) {
        if (!in_array($data->status, ['draft', 'rejected_spv'])) {
            abort(403, 'Data sudah di-approve spv/manager, sudah tidak bisa untuk klarifikasi.');
        }
    }

    // Guard: SPV tidak bisa edit jika sudah approved_spv
    if ($canSpvManager && !$canManage) {
        if ($data->status === 'approved_spv') {
            abort(403, 'Data sudah di-approve SPV, tidak bisa klarifikasi.');
        }
    }

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
             'name'              => $employee->store->first()?->name       ?? '-', // ← fix
    'position_name'     => $employee->position->first()?->name    ?? '-', // ← fix
            'device_name'       => optional($first->devicefingerprints)->device_name ?? '-',
            'scan_date'         => $scanDateCarbon,
        ];

        foreach (range(1, 2) as $i) {
            $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
        }

        $fingerprints->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
            if ($mode >= 1 && $mode <= 2) {
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


if (!$canManage && ($canViewOwn || $canSpvManager)) {
    Log::info('Guard scan check', [
        'in_1' => $row['in_1'],
        'in_2' => $row['in_2'],
        'in_1_empty' => empty($row['in_1']),
        'in_2_empty' => empty($row['in_2']),
    ]);
    if (!empty($row['in_1']) && !empty($row['in_2'])) {
        return redirect()->route('pages.Fingerprints')
            ->with('error', 'Scan masuk dan pulang Anda sudah lengkap, tidak perlu diedit.');
    }
}

        return view('pages.Fingerprints.edit', [
            'data'     => (object) $row,
            'isEdited' => false,
            'devices'  => $devices,
        ]);
    }
    // public function editFingerprint($pin, Request $request)
    // {
    //     $user = auth()->user();

    //     /** @var \App\Models\User|null $user */

    //     if (!$user->hasPermissionTo('ManageFingerspot')) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $request->merge(['pin' => $pin]);
    //     $request->validate([
    //         'pin'       => 'required|string|max:20',
    //         'scan_date' => 'required|date',
    //     ]);

    //     $scanDate       = $request->input('scan_date');
    //     $scanDateCarbon = Carbon::parse($scanDate)->toDateString();

    //     // ── Ambil semua device untuk dropdown ──
    //     $devices = Devicefingerprint::select('sn', 'device_name')
    //         ->whereNotNull('device_name')
    //         ->orderBy('device_name')
    //         ->get();
    //     //    dd($devices->pluck('device_name', 'sn'));
    //     $data = EditedFingerprint::with('devicefingerprints')
    //         ->where('pin', $pin)
    //         ->whereDate('scan_date', $scanDateCarbon)
    //         ->first();

    //     if ($data) {
    //         return view('pages.Fingerprints.edit', [
    //             'data'     => $data,
    //             'isEdited' => true,
    //             'devices'  => $devices,
    //         ]);
    //     }

    //     $fingerprints = Fingerprints::with('devicefingerprints')
    //         ->where('pin', $pin)
    //         ->whereDate('scan_date', $scanDateCarbon)
    //         ->orderBy('scan_date')
    //         ->get();

    //     if ($fingerprints->isEmpty()) {
    //         return response()->json(['message' => 'Data not found'], 404);
    //     }

    //     $first    = $fingerprints->first();
    //     $employee = Employee::with(['store:id,name', 'position:id,name'])
    //         ->where('pin', $pin)
    //         ->first();

    //     $row = [
    //         'pin'               => $pin,
    //         'employee_name'     => $employee->employee_name               ?? '-',
    //         'status_employee'   => $employee->status_employee             ?? '-',
    //         'employee_pengenal' => $employee->employee_pengenal           ?? '-',
    //          'name'              => $employee->store->first()?->name       ?? '-', // ← fix
    // 'position_name'     => $employee->position->first()?->name    ?? '-', // ← fix
    //         'device_name'       => optional($first->devicefingerprints)->device_name ?? '-',
    //         'scan_date'         => $scanDateCarbon,
    //     ];

    //     foreach (range(1, 2) as $i) {
    //         $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
    //     }

    //     $fingerprints->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
    //         if ($mode >= 1 && $mode <= 2) {
    //             $firstItem = $items->sortBy('scan_date')->first();
    //             $formatted = null;
    //             try {
    //                 $formatted = Carbon::parse($firstItem->scan_date)->format('H:i:s');
    //             } catch (\Exception $e) {
    //                 Log::error('Gagal parsing waktu', ['mode' => $mode, 'error' => $e->getMessage()]);
    //             }
    //             // $deviceName           = optional($firstItem->devicefingerprints)->device_name ?? '';
    //             $deviceName           = trim(optional($firstItem->devicefingerprints)->device_name ?? '');

    //             $row["in_$mode"]      = $formatted;
    //             $row["device_$mode"]  = $deviceName;
    //             $row["combine_$mode"] = "{$formatted} {$deviceName}";
    //         }
    //     });

    //     return view('pages.Fingerprints.edit', [
    //         'data'     => (object) $row,
    //         'isEdited' => false,
    //         'devices'  => $devices,
    //     ]);
    // }
     public function showFingerprint($pin, Request $request)
{
    /** @var \App\Models\User|null $user */

    $user = auth()->user();

    $canManage     = $user->hasPermissionTo('ManageFingerspot');
    $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
    $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');

    if (!$canManage && !$canSpvManager && !$canViewOwn) {
        abort(403, 'Unauthorized');
    }

    // Guard ViewFingerspot — hanya bisa lihat data milik sendiri
    if ($canViewOwn && !$canManage && !$canSpvManager) {
        $employeePin = $user->employee?->pin;
        if ($pin !== $employeePin) {
            abort(403, 'Anda hanya bisa melihat data fingerprint milik sendiri.');
        }
    }

    $request->merge(['pin' => $pin]);
    $request->validate([
        'pin'       => 'required|string|max:20',
        'scan_date' => 'required|date',
    ]);

    $scanDate       = $request->input('scan_date');
    $scanDateCarbon = Carbon::parse($scanDate)->toDateString();

    $devices = Devicefingerprint::select('sn', 'device_name')
        ->whereNotNull('device_name')
        ->orderBy('device_name')
        ->get();

    $data = EditedFingerprint::with('attachments')->where('pin', $pin)
        ->whereDate('scan_date', $scanDateCarbon)
        ->first();

    if ($data) {
        return view('pages.Fingerprints.show', [
            'data'          => $data,
            'isEdited'      => true,
            'devices'       => $devices,
            'canManage'     => $canManage,
            'canSpvManager' => $canSpvManager,
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
        'employee_name'     => $employee->employee_name             ?? '-',
        'status_employee'   => $employee->status_employee           ?? '-',
        'employee_pengenal' => $employee->employee_pengenal         ?? '-',
        'name'              => $employee->store->first()?->name     ?? '-',
        'position_name'     => $employee->position->first()?->name  ?? '-',
        'device_name'       => optional($first->devicefingerprints)->device_name ?? '-',
        'scan_date'         => $scanDateCarbon,
    ];

    foreach (range(1, 2) as $i) {
        $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
    }

    $fingerprints->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
        if ($mode >= 1 && $mode <= 2) {
            $firstItem = $items->sortBy('scan_date')->first();
            $formatted = null;
            try {
                $formatted = Carbon::parse($firstItem->scan_date)->format('H:i:s');
            } catch (\Exception $e) {
                Log::error('Gagal parsing waktu', ['mode' => $mode, 'error' => $e->getMessage()]);
            }
            $deviceName           = trim(optional($firstItem->devicefingerprints)->device_name ?? '');
            $row["in_$mode"]      = $formatted;
            $row["device_$mode"]  = $deviceName;
            $row["combine_$mode"] = "{$formatted} {$deviceName}";
        }
    });

    return view('pages.Fingerprints.show', [
        'data'          => (object) $row,
        'isEdited'      => false,
        'devices'       => $devices,
        'canManage'     => $canManage,
        'canSpvManager' => $canSpvManager,
    ]);
}
    // public function editFingerprint($pin, Request $request)
    // {
    //     $user = auth()->user();

    //     /** @var \App\Models\User|null $user */

    //     if (!$user->hasPermissionTo('ManageFingerspot')) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $request->merge(['pin' => $pin]);
    //     $request->validate([
    //         'pin'       => 'required|string|max:20',
    //         'scan_date' => 'required|date',
    //     ]);

    //     $scanDate       = $request->input('scan_date');
    //     $scanDateCarbon = Carbon::parse($scanDate)->toDateString();

    //     // ── Ambil semua device untuk dropdown ──
    //     $devices = Devicefingerprint::select('sn', 'device_name')
    //         ->whereNotNull('device_name')
    //         ->orderBy('device_name')
    //         ->get();
    //     //    dd($devices->pluck('device_name', 'sn'));
    //     $data = EditedFingerprint::with('devicefingerprints')
    //         ->where('pin', $pin)
    //         ->whereDate('scan_date', $scanDateCarbon)
    //         ->first();

    //     if ($data) {
    //         return view('pages.Fingerprints.edit', [
    //             'data'     => $data,
    //             'isEdited' => true,
    //             'devices'  => $devices,
    //         ]);
    //     }

    //     $fingerprints = Fingerprints::with('devicefingerprints')
    //         ->where('pin', $pin)
    //         ->whereDate('scan_date', $scanDateCarbon)
    //         ->orderBy('scan_date')
    //         ->get();

    //     if ($fingerprints->isEmpty()) {
    //         return response()->json(['message' => 'Data not found'], 404);
    //     }

    //     $first    = $fingerprints->first();
    //     $employee = Employee::with(['store:id,name', 'position:id,name'])
    //         ->where('pin', $pin)
    //         ->first();

    //     $row = [
    //         'pin'               => $pin,
    //         'employee_name'     => $employee->employee_name               ?? '-',
    //         'status_employee'   => $employee->status_employee             ?? '-',
    //         'employee_pengenal' => $employee->employee_pengenal           ?? '-',
    //          'name'              => $employee->store->first()?->name       ?? '-', // ← fix
    // 'position_name'     => $employee->position->first()?->name    ?? '-', // ← fix
    //         'device_name'       => optional($first->devicefingerprints)->device_name ?? '-',
    //         'scan_date'         => $scanDateCarbon,
    //     ];

    //     foreach (range(1, 10) as $i) {
    //         $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
    //     }

    //     $fingerprints->groupBy('inoutmode')->each(function ($items, $mode) use (&$row) {
    //         if ($mode >= 1 && $mode <= 10) {
    //             $firstItem = $items->sortBy('scan_date')->first();
    //             $formatted = null;
    //             try {
    //                 $formatted = Carbon::parse($firstItem->scan_date)->format('H:i:s');
    //             } catch (\Exception $e) {
    //                 Log::error('Gagal parsing waktu', ['mode' => $mode, 'error' => $e->getMessage()]);
    //             }
    //             // $deviceName           = optional($firstItem->devicefingerprints)->device_name ?? '';
    //             $deviceName           = trim(optional($firstItem->devicefingerprints)->device_name ?? '');

    //             $row["in_$mode"]      = $formatted;
    //             $row["device_$mode"]  = $deviceName;
    //             $row["combine_$mode"] = "{$formatted} {$deviceName}";
    //         }
    //     });

    //     return view('pages.Fingerprints.edit', [
    //         'data'     => (object) $row,
    //         'isEdited' => false,
    //         'devices'  => $devices,
    //     ]);
    // }
//    public function updateFingerprint(Request $request)
// {
//     /** @var \App\Models\User|null $user */
//     $user = auth()->user();

//     $canManage     = $user->hasPermissionTo('ManageFingerspot');
//     $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
//     $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');

//     if (!$canManage && !$canSpvManager && !$canViewOwn) {
//         abort(403, 'Unauthorized');
//     }

//     // Cek existing record
//     $existing = EditedFingerprint::where('pin', $request->pin)
//         ->whereDate('scan_date', $request->scan_date)
//         ->first();

//     // Guard edit berdasarkan status & permission
//     if ($existing) {
//         if ($canViewOwn && !$canManage && !$canSpvManager) {
//             // ViewFingerspot hanya bisa edit jika draft atau rejected_spv
//             if (!in_array($existing->status, ['draft', 'rejected_spv'])) {
//                 return back()->with('error', 'Data sudah di-approve SPV, tidak bisa diedit.');
//             }
//         } elseif ($canSpvManager && !$canManage) {
//             // SPV hanya bisa edit jika masih draft
//             if ($existing->status !== 'draft') {
//                 return back()->with('error', 'Hanya data berstatus draft yang bisa diedit SPV.');
//             }
//         }
//         // ManageFingerspot (HR) bebas edit kapanpun
//     }

//     try {
//     $validated = $request->validate([
//         'pin'           => 'required|string',
//         'scan_date'     => 'required|date',
//         'employee_name' => 'nullable|string',
//         'position_name' => 'nullable|string',
//         'store_name'    => 'nullable|string',
//         'duration'      => 'nullable|string',
//         'notes'      => 'required|string',
//         'attachments'   => 'nullable|array',
//         'attachments.*' => 'mimes:jpg,jpeg,png,webp|max:512',
//         ...collect(range(1, 10))->flatMap(fn($i) => [
//             "in_$i"     => 'nullable|string',
//             "device_$i" => 'nullable|string',
//         ])->toArray()
//     ]);

//     $payload = collect($validated)
//         ->except(['pin', 'scan_date', 'attachments'])
//         ->toArray();

//     // Set status otomatis saat edit → selalu kembali ke draft
//     // Kecuali HR yang bisa edit tanpa reset status
//     if (!$canManage) {
//         $payload['status'] = 'draft';
//     }

//     $editedFingerprint = EditedFingerprint::updateOrCreate(
//         ['pin' => $validated['pin'], 'scan_date' => $validated['scan_date']],
//         $payload
//     );

//     if ($canViewOwn && !$canManage && !$canSpvManager && $request->hasFile('attachments')) {
//     $existingAttachments = \App\Models\EditedFingerprintAttachment::where('edited_fingerprint_id', $editedFingerprint->id)->get();

//     foreach ($existingAttachments as $att) {
//         if (Storage::disk('s3')->exists($att->attachment)) {
//             Storage::disk('s3')->delete($att->attachment);
//             Log::info('[attachment fingerprints] File lama dihapus', ['path' => $att->file_path]);
//         }
//         $att->delete();
//     }
// }

//     // ── Upload multiple attachments ke S3 ──
//     if ($request->hasFile('attachments')) {
//         foreach ($request->file('attachments') as $file) {
//             $safeName = Str::slug($request->input('employee_name', 'employee'));
//             $scanDate = Str::replace('-', '', $validated['scan_date']);
//             $fileName = $safeName . '-' . now()->timestamp . '-' . $scanDate . '-'. uniqid() . '.' . $file->getClientOriginalExtension();
//             $folder   = 'employees-edited-fingerprints';

//             Log::info('[attachment fingerprints] Upload', [
//                 'original_name' => $file->getClientOriginalName(),
//                 'size'          => $file->getSize(),
//                 'mime'          => $file->getMimeType(),
//                 'fileName'      => $fileName,
//             ]);

//             $path = Storage::disk('s3')->putFileAs($folder, $file, $fileName);

//             \App\Models\EditedFingerprintAttachment::create([
//                 'edited_fingerprint_id' => $editedFingerprint->id,
//                'attachment'            => $path,
//             ]);

//             Log::info('[attachment fingerprints] Upload selesai', [
//                 'path'   => $path,
//                 'exists' => Storage::disk('s3')->exists($path),
//             ]);
//         }
//     }

//     // Activity log
//     // activity('fingerprint')
//     //     ->causedBy($user)
//     //     ->performedOn($editedFingerprint)
//     //     ->withProperties([
//     //         'pin'           => $validated['pin'],
//     //         'scan_date'     => $validated['scan_date'],
//     //         'employee_name' => $validated['employee_name'] ?? '-',
//     //         'action'        => $editedFingerprint->wasRecentlyCreated ? 'created' : 'updated',
//     //     ])
//     //     ->log(
//     //         $editedFingerprint->wasRecentlyCreated
//     //             ? 'Edit fingerprint baru untuk ' . ($validated['employee_name'] ?? $validated['pin'])
//     //             : 'Update fingerprint untuk ' . ($validated['employee_name'] ?? $validated['pin'])
//     //     );





    
//     // Ambil data lama sebelum update (kalau update, bukan create)
// $oldData = $editedFingerprint->wasRecentlyCreated ? [] : [
//     'in_1'           => $editedFingerprint->getOriginal('in_1'),
//     'device_1'       => $editedFingerprint->getOriginal('device_1'),
//     'in_2'           => $editedFingerprint->getOriginal('in_2'),
//     'device_2'       => $editedFingerprint->getOriginal('device_2'),
//     'notes'          => $editedFingerprint->getOriginal('notes'),
//     'status'         => $editedFingerprint->getOriginal('status'),
//     'approved_by'    => $editedFingerprint->getOriginal('approved_by'),
//     'approved_at'    => $editedFingerprint->getOriginal('approved_at'),
//     'rejection_note' => $editedFingerprint->getOriginal('rejection_note'),
// ];

// $newData = [
//     'in_1'           => $editedFingerprint->in_1,
//     'device_1'       => $editedFingerprint->device_1,
//     'in_2'           => $editedFingerprint->in_2,
//     'device_2'       => $editedFingerprint->device_2,
//     'notes'          => $editedFingerprint->notes,
//     'status'         => $editedFingerprint->status,
//     'approved_by'    => $editedFingerprint->approved_by,
//     'approved_at'    => $editedFingerprint->approved_at,
//     'rejection_note' => $editedFingerprint->rejection_note,
// ];

// // Hitung field yang berubah
// $changes = [];
// foreach ($newData as $field => $newValue) {
//     $oldValue = $oldData[$field] ?? null;
//     if ($oldValue !== $newValue) {
//         $changes[$field] = [
//             'old' => $oldValue,
//             'new' => $newValue,
//         ];
//     }
// }

// activity('fingerprint')
//     ->causedBy($user)
//     ->performedOn($editedFingerprint)
//     ->withProperties([
//         'pin'           => $validated['pin'],
//         'scan_date'     => $validated['scan_date'],
//         'employee_name' => $validated['employee_name'] ?? '-',
//         'position_name' => $editedFingerprint->position_name ?? '-',
//         'store_name'    => $editedFingerprint->store_name ?? '-',
//         'action'        => $editedFingerprint->wasRecentlyCreated ? 'created' : 'updated',
//         'changes'       => $changes, // ← field apa saja yang berubah
//         'after'         => $newData, // ← semua nilai setelah update
//     ])
//     ->log(
//         $editedFingerprint->wasRecentlyCreated
//             ? 'Edit fingerprint baru untuk ' . ($validated['employee_name'] ?? $validated['pin'])
//             : 'Update fingerprint untuk ' . ($validated['employee_name'] ?? $validated['pin'])
//             . ' — field diubah: ' . implode(', ', array_keys($changes))
//     );

//     return redirect()->route('pages.Fingerprints')
//         ->with('success', 'Fingerprint updated successfully.');

// } catch (\Exception $e) {
//     Log::error('Gagal updateFingerprint', ['error' => $e->getMessage()]);
//     return back()->with('error', 'There is an error while updating fingerprints. kemungkinan anda tidak memasukkan scan, lokasi device, dan attachment yaa');
// }
// }
public function updateFingerprint(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    $canManage     = $user->hasPermissionTo('ManageFingerspot');
    $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
    $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');

    if (!$canManage && !$canSpvManager && !$canViewOwn) {
        abort(403, 'Unauthorized');
    }

    // ← 1. Cari existing record DULU sebelum apapun
    $existing = EditedFingerprint::where('pin', $request->pin)
        ->whereDate('scan_date', $request->scan_date)
        ->first();

    // Guard edit berdasarkan status & permission
    if ($existing) {
        if ($canViewOwn && !$canManage && !$canSpvManager) {
            if (!in_array($existing->status, ['draft', 'rejected_spv'])) {
                return back()->with('error', 'Data sudah di-approve SPV, tidak bisa diedit.');
            }
        } elseif ($canSpvManager && !$canManage) {
            if ($existing->status !== 'draft') {
                return back()->with('error', 'Hanya data berstatus draft yang bisa diedit SPV.');
            }
        }
    }

    // ← 2. Simpan old data sebelum update
    $isNew   = !$existing;
    $oldData = $isNew ? [] : [
        'in_1'           => $existing->in_1,
        'device_1'       => $existing->device_1,
        'in_2'           => $existing->in_2,
        'device_2'       => $existing->device_2,
        'notes'          => $existing->notes,
        'status'         => $existing->status,
        'approved_by'    => $existing->approved_by,
        'approved_at'    => $existing->approved_at,
        'rejection_note' => $existing->rejection_note,
    ];

    try {
        $validated = $request->validate([
            'pin'           => 'required|string',
            'scan_date'     => 'required|date',
            'employee_name' => 'nullable|string',
            'position_name' => 'nullable|string',
            'store_name'    => 'nullable|string',
            'duration'      => 'nullable|string',
            'notes'         => 'required|string',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'mimes:jpg,jpeg,png,webp|max:512',
            ...collect(range(1, 10))->flatMap(fn($i) => [
                "in_$i"     => 'nullable|string',
                "device_$i" => 'nullable|string',
            ])->toArray()
        ]);

        $payload = collect($validated)
            ->except(['pin', 'scan_date', 'attachments'])
            ->toArray();

        if (!$canManage) {
            $payload['status'] = 'draft';
        }

        // ← 3. Update atau create
        $editedFingerprint = EditedFingerprint::updateOrCreate(
            ['pin' => $validated['pin'], 'scan_date' => $validated['scan_date']],
            $payload
        );

        // Upload attachment
        if ($canViewOwn && !$canManage && !$canSpvManager && $request->hasFile('attachments')) {
            $existingAttachments = \App\Models\EditedFingerprintAttachment::where('edited_fingerprint_id', $editedFingerprint->id)->get();
            foreach ($existingAttachments as $att) {
                if (Storage::disk('s3')->exists($att->attachment)) {
                    Storage::disk('s3')->delete($att->attachment);
                }
                $att->delete();
            }
        }

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $safeName = Str::slug($request->input('employee_name', 'employee'));
                $scanDate = Str::replace('-', '', $validated['scan_date']);
                $fileName = $safeName . '-' . now()->timestamp . '-' . $scanDate . '-' . uniqid() . '.' . $file->getClientOriginalExtension();
                $folder   = 'employees-edited-fingerprints';
                $path     = Storage::disk('s3')->putFileAs($folder, $file, $fileName);

                \App\Models\EditedFingerprintAttachment::create([
                    'edited_fingerprint_id' => $editedFingerprint->id,
                    'attachment'            => $path,
                ]);
            }
        }

        // ← 4. Ambil data baru setelah save
        $newData = [
            'in_1'           => $editedFingerprint->in_1,
            'device_1'       => $editedFingerprint->device_1,
            'in_2'           => $editedFingerprint->in_2,
            'device_2'       => $editedFingerprint->device_2,
            'notes'          => $editedFingerprint->notes,
            'status'         => $editedFingerprint->status,
            'approved_by'    => $editedFingerprint->approved_by,
            'approved_at'    => $editedFingerprint->approved_at,
            'rejection_note' => $editedFingerprint->rejection_note,
        ];

        // ← 5. Hitung changes
        $changes = [];
        foreach ($newData as $field => $newValue) {
            $oldValue = $oldData[$field] ?? null;
            if ((string)$oldValue !== (string)$newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        // ← 6. Activity log
        if (!empty($changes) || $isNew) {
            activity('fingerprint')
                ->causedBy($user)
                ->performedOn($editedFingerprint)
                ->withProperties([
                    'pin'           => $validated['pin'],
                    'scan_date'     => $validated['scan_date'],
                    'employee_name' => $validated['employee_name'] ?? '-',
                    'position_name' => $editedFingerprint->position_name ?? '-',
                    'store_name'    => $editedFingerprint->store_name ?? '-',
                    'action'        => $isNew ? 'created' : 'updated',
                    'changes'       => $changes,
                    'after'         => $newData,
                ])
                ->log(
                    $isNew
                        ? 'Edit fingerprint baru untuk ' . ($validated['employee_name'] ?? $validated['pin'])
                        : 'Update fingerprint untuk ' . ($validated['employee_name'] ?? $validated['pin'])
                          . ' — field diubah: ' . implode(', ', array_keys($changes))
                );
        }

        return redirect()->route('pages.Fingerprints')
            ->with('success', 'Fingerprint updated successfully.');

    } catch (\Exception $e) {
        Log::error('Gagal updateFingerprint', ['error' => $e->getMessage()]);
        return back()->with('error', 'There is an error while updating fingerprints.');
    }
}
public function updateStatus(Request $request, $id)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    $canManage     = $user->hasPermissionTo('ManageFingerspot');
    $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');

    if (!$canManage && !$canSpvManager) {
        abort(403, 'Unauthorized');
    }

    $request->validate([
        'status'         => 'required|string',
        'rejection_note' => 'nullable|string|max:500',
    ]);

    $fingerprint = EditedFingerprint::findOrFail($id);

    // Guard SPV — hanya bisa set approved_spv/rejected_spv dari status draft
    if ($canSpvManager && !$canManage) {
        if ($fingerprint->status !== 'draft') {
            return back()->with('error', 'Hanya data draft yang bisa di-approve/reject SPV.');
        }
        if (!in_array($request->status, ['approved_spv', 'rejected_spv'])) {
            abort(403, 'SPV hanya bisa set approved_spv atau rejected_spv.');
        }
    }

    // Guard HR — hanya bisa set draft/approved_hr/rejected_hr, dari approved_spv
    if ($canManage) {
        if ($request->status === 'approved_hr' || $request->status === 'rejected_hr') {
            if ($fingerprint->status !== 'approved_spv') {
                return back()->with('error', 'Hanya data approved_spv yang bisa di-approve/reject HR.');
            }
        }
        if (!in_array($request->status, ['draft', 'approved_hr', 'rejected_hr'])) {
            abort(403, 'HR hanya bisa set draft, approved_hr, atau rejected_hr.');
        }
    }

    $fingerprint->update([
        'status'         => $request->status,
        'approved_by'    => $user->id,
        'approved_at'    => now(),
        'rejection_note' => $request->rejection_note,
    ]);

    activity('fingerprint')
        ->causedBy($user)
        ->performedOn($fingerprint)
        ->withProperties([
            'old_status' => $fingerprint->getOriginal('status'),
            'new_status' => $request->status,
            'rejection_note' => $request->rejection_note,
        ])
        ->log('Status fingerprint diubah ke ' . $request->status);

    return back()->with('success', 'Status berhasil diupdate.');
}

// public function bulkStatus(Request $request)
// {
//     /** @var \App\Models\User|null $user */
//     $user = auth()->user();
//     $canManage     = $user->hasPermissionTo('ManageFingerspot');
//     $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');

//     if (!$canManage && !$canSpvManager) abort(403);

//     $request->validate([
//         'ids'    => 'required|array',
//         'ids.*'  => 'integer',
//         'status' => 'required|in:approved_spv,rejected_spv,approved_hr,rejected_hr',
//     ]);

//     // Guard status per permission
//     if ($canSpvManager && !$canManage) {
//         if (!in_array($request->status, ['approved_spv', 'rejected_spv'])) abort(403);
//     }
//     if ($canManage) {
//         if (!in_array($request->status, ['approved_hr', 'rejected_hr'])) abort(403);
//     }

//     $fingerprints = EditedFingerprint::whereIn('id', $request->ids)->get();

// foreach ($fingerprints as $fp) {
//     // Guard: SPV hanya bisa update dari draft
//     if ($canSpvManager && !$canManage && $fp->status !== 'draft') continue;
    
//     // Guard: HR bisa update dari approved_spv ATAU draft (antisipasi SPV tidak respon)
//     if ($canManage && !in_array($fp->status, ['approved_spv', 'draft'])) continue;

//     $oldStatus = $fp->status;

//     $fp->update([
//         'status'      => $request->status,
//         'approved_by' => $user->id,
//         'approved_at' => now(),
//     ]);

//     activity('fingerprint')
//         ->causedBy($user)
//         ->performedOn($fp)
//         ->withProperties([
//             'pin'           => $fp->pin,
//             'scan_date'     => $fp->scan_date,
//             'employee_name' => $fp->employee_name ?? '-',
//             'position_name' => $fp->position_name ?? '-',
//             'store_name'    => $fp->store_name ?? '-',
//             'action'        => 'bulk_status',
//             'changes'       => [
//                 'status' => [
//                     'old' => $oldStatus,
//                     'new' => $request->status,
//                 ],
//                 'approved_by' => [
//                     'old' => null,
//                     'new' => $user->employee?->employee_name ?? $user->username,
//                 ],
//                 'approved_at' => [
//                     'old' => null,
//                     'new' => now()->toDateTimeString(),
//                 ],
//             ],
//             'after' => [
//                 'status'      => $request->status,
//                 'approved_by' => $user->employee?->employee_name ?? $user->username,
//                 'approved_at' => now()->toDateTimeString(),
//             ],
//         ])
//         ->log('Bulk status fingerprint ' . ($fp->employee_name ?? $fp->pin) . ' diubah dari ' . $oldStatus . ' ke ' . $request->status);
// }

//     return response()->json(['message' => 'Status berhasil diupdate.',
//         'reload'  => true,
// ]);
// }
public function bulkStatus(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
    $canManage     = $user->hasPermissionTo('ManageFingerspot');
    $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');

    if (!$canManage && !$canSpvManager) abort(403);

    $request->validate([
        'ids'    => 'required|array',
        'ids.*'  => 'integer',
        'status' => 'required|in:approved_spv,rejected_spv,approved_hr,rejected_hr',
    ]);

    if ($canSpvManager && !$canManage) {
        if (!in_array($request->status, ['approved_spv', 'rejected_spv'])) abort(403);
    }
    if ($canManage) {
        if (!in_array($request->status, ['approved_hr', 'rejected_hr'])) abort(403);
    }

    $fingerprints = EditedFingerprint::whereIn('id', $request->ids)->get();

    foreach ($fingerprints as $fp) {
        Log::info('BULK LOOP', [
            'fp_id'     => $fp->id,
            'fp_status' => $fp->status,
        ]);

        if ($canSpvManager && !$canManage && $fp->status !== 'draft') {
            Log::info('SKIPPED SPV GUARD', ['fp_id' => $fp->id]);
            continue;
        }

        if ($canManage && !in_array($fp->status, ['approved_spv', 'draft'])) {
            Log::info('SKIPPED HR GUARD', ['fp_id' => $fp->id, 'status' => $fp->status]);
            continue;
        }

        $oldStatus = $fp->status;

        $fp->update([
            'status'      => $request->status,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        Log::info('BEFORE ACTIVITY', [
            'fp_id'      => $fp->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
        ]);

        try {
            activity('fingerprint')
                ->causedBy($user)
                ->performedOn($fp)
                ->withProperties([
                    'pin'           => $fp->pin,
                    'scan_date'     => $fp->scan_date,
                    'employee_name' => $fp->employee_name ?? '-',
                    'position_name' => $fp->position_name ?? '-',
                    'store_name'    => $fp->store_name ?? '-',
                    'action'        => 'bulk_status',
                    'changes'       => [
                        'status' => [
                            'old' => $oldStatus,
                            'new' => $request->status,
                        ],
                        'approved_by' => [
                            'old' => null,
                            'new' => $user->employee?->employee_name ?? $user->username,
                        ],
                        'approved_at' => [
                            'old' => null,
                            'new' => now()->toDateTimeString(),
                        ],
                    ],
                    'after' => [
                        'status'      => $request->status,
                        'approved_by' => $user->employee?->employee_name ?? $user->username,
                        'approved_at' => now()->toDateTimeString(),
                    ],
                ])
                ->log('Bulk status fingerprint ' . ($fp->employee_name ?? $fp->pin) . ' diubah dari ' . $oldStatus . ' ke ' . $request->status);

            Log::info('ACTIVITY SUCCESS', ['fp_id' => $fp->id]);

        } catch (\Throwable $e) {
            Log::error('ACTIVITY ERROR', [
                'fp_id'   => $fp->id,
                'message' => $e->getMessage(),
            ]);
        }
    }

    return response()->json([
        'message' => 'Status berhasil diupdate.',
        'reload'  => true,
    ]);
}
public function deleteAttachment(Request $request)
{
    /** @var \App\Models\User|null $user */

    $user = auth()->user();
    if (!$user->hasAnyPermission(['ManageFingerspot', 'ManageFingerspotSPVManager', 'ViewFingerspot'])) {
        abort(403);
    }

    $attachment = \App\Models\EditedFingerprintAttachment::findOrFail($request->id);

    // Guard: hanya bisa hapus jika status draft
    if ($attachment->editedFingerprint->status !== 'draft') {
        return response()->json(['message' => 'Tidak bisa hapus attachment yang sudah di-approve.'], 403);
    }

    if (Storage::disk('s3')->exists($attachment->attachment)) {
        Storage::disk('s3')->delete($attachment->attachment);
    }

    $attachment->delete();

    return response()->json(['message' => 'Attachment berhasil dihapus.']);
}


public function getLog(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    if (!$user->hasPermissionTo('ManageFingerspot')) {
        abort(403);
    }

    $query = \Spatie\Activitylog\Models\Activity::with('causer.employee')
        ->where('log_name', 'fingerprint')
        ->latest();

    return DataTables::of($query)
        ->addColumn('causer_name', fn($row) =>
            $row->causer?->employee?->employee_name ?? $row->causer?->username ?? '-'
        )
        ->addColumn('employee_name', fn($row) =>
            $row->properties['employee_name'] ?? '-'
        )
        ->addColumn('pin', fn($row) =>
            $row->properties['pin'] ?? '-'
        )
        ->addColumn('scan_date', fn($row) =>
            $row->properties['scan_date'] ?? '-'
        )
        ->addColumn('action_badge', fn($row) => match($row->properties['action'] ?? '') {
            'created' => '<span class="badge bg-success">Created</span>',
            'updated' => '<span class="badge bg-warning text-dark">Updated</span>',
            default   => '-',
        })
        ->addColumn('changes', function ($row) {
    $changes = $row->properties['changes'] ?? [];
    if (empty($changes)) return '-';

    $html = '<ul class="mb-0 ps-3" style="font-size:.8rem">';
    foreach ($changes as $field => $change) {
        $old = $change['old'] ?? '-';
        $new = $change['new'] ?? '-';
        $html .= "<li><strong>{$field}</strong>: 
            <span class='text-danger'>{$old}</span> → 
            <span class='text-success'>{$new}</span>
        </li>";
    }
    $html .= '</ul>';
    return $html;
})
        ->editColumn('created_at', fn($row) =>
            $row->created_at->timezone('Asia/Makassar')->format('d M Y H:i:s')
        )
        ->rawColumns(['action_badge','changes'])
        ->make(true);
}
    // public function updateFingerprint(Request $request)
    // {
    //     $user = auth()->user();
    //     /** @var \App\Models\User|null $user */
    //     if (!$user->hasPermissionTo('ManageFingerspot')) {
    //         abort(403, 'Unauthorized');
    //     }
    //     try {
    //         $validated = $request->validate([
    //             'pin'           => 'required|string',
    //             'scan_date'     => 'required|date',
    //             'employee_name' => 'nullable|string',
    //             'position_name' => 'nullable|string',
    //             'store_name'    => 'nullable|string',
    //             'duration'      => 'nullable|string',
    //             'attachment'    => 'required|mimes:jpg,jpeg,png,webp|max:512',
    //             ...collect(range(1, 10))->flatMap(function ($i) {
    //                 return ["in_$i" => 'nullable|string', "device_$i" => 'nullable|string'];
    //             })->toArray()
    //         ]);
    //         $attachmentPath = null;
    //         if ($request->hasFile('attachment')) {
    //             $file     = $request->file('attachment');
    //             $safeName = Str::slug($request->input('employee_name', 'employee'));
    //             $fileName = $safeName . '-' . now()->timestamp . '-fingerprint.' . $file->getClientOriginalExtension();
    //             $folder   = 'employees-edited-fingerprints';
    //             Log::info('[attachment fingerprints] Info upload', [
    //                 'original_name' => $file->getClientOriginalName(),
    //                 'size'          => $file->getSize(),
    //                 'mime'          => $file->getMimeType(),
    //                 'fileName'      => $fileName,
    //                 'folder'        => $folder,
    //             ]);
    //             // ── Hapus attachment lama jika ada ──
    //             $existing = EditedFingerprint::where('pin', $validated['pin'])
    //                 ->whereDate('scan_date', $validated['scan_date'])
    //                 ->first();

    //             if ($existing && $existing->attachment && Storage::disk('s3')->exists($existing->attachment)) {
    //                 Storage::disk('s3')->delete($existing->attachment);
    //                 Log::info('[attachment fingerprints] Attachment lama dihapus', ['path' => $existing->attachment]);
    //             } else {
    //                 Log::info('[attachment fingerprints] Tidak ada attachment lama untuk dihapus');
    //             }

    //             // ── Upload baru ke S3 ──
    //             $attachmentPath = Storage::disk('s3')->putFileAs($folder, $file, $fileName);

    //             Log::info('[attachment fingerprints] Upload selesai', [
    //                 'path'   => $attachmentPath,
    //                 'exists' => Storage::disk('s3')->exists($attachmentPath),
    //             ]);
    //         }

    //         // ── Simpan ke edited_fingerprint ──
    //         $payload = collect($validated)
    //             ->except(['pin', 'scan_date', 'attachment'])
    //             ->toArray();

    //         if ($attachmentPath) {
    //             $payload['attachment'] = $attachmentPath;
    //         }

    //         EditedFingerprint::updateOrCreate(
    //             [
    //                 'pin'       => $validated['pin'],
    //                 'scan_date' => $validated['scan_date'],
    //             ],
    //             $payload
    //         );

    //         return redirect()->route('pages.Fingerprints')
    //             ->with('success', 'Fingerprint updated successfully.');
    //     } catch (\Exception $e) {
    //         Log::error('Gagal updateFingerprint', ['error' => $e->getMessage()]);
    //         return back()->with('error', 'There is an error while updating fingerprints.');
    //     }
    // }
}
