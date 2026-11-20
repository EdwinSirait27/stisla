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

   
    if (!$canManage && !$user->employee) {
        return redirect()->back()->with('error', 'Akun Anda belum terhubung ke data karyawan.');
    }

    // NoOverflow: tanggal 29–31 tidak boleh meloncat ke bulan berikutnya
    $today = today();
    $defaultStartDate = $today->copy()->startOfMonth()->subMonthNoOverflow()->day(26)->toDateString();
    $defaultEndDate   = $canManage
        ? $today->copy()->day(25)->toDateString()
        : $today->copy()->startOfMonth()->addMonthNoOverflow()->day(25)->toDateString();
    [$minAllowed, $maxAllowed] = $this->allowedRange();

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
    ) + [
        'minAllowed' => $minAllowed->toDateString(),
        'maxAllowed' => $maxAllowed->toDateString(),
    ]);
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
            ->orderBy('scan_date')
            ->get();

        if ($rawScans->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'There is no fingerprint data in that date range.',
            ], 422);
        }

        // ── Pre-load roster untuk cek hari kerja ──
        // $rosters = Roster::whereIn('employee_id', $employeeIds)
        //     ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
        //     ->get()
        //     ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());
        $rosters = Roster::with('shift:id,start_time,end_time')
    ->whereIn('employee_id', $employeeIds)
    ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
    ->get()
    ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());
    $overnightShiftIds = $rosters
    ->filter(fn($r) => $r->shift && $r->shift->end_time < $r->shift->start_time)
    ->pluck('shift_id')
    ->unique()
    ->toArray();

        // ── Pre-load existing manual recaps supaya TIDAK ditimpa ──
        $existingManualRecaps = Fingerprintrecap::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('sync_status', self::SYNC_STATUS_MANUAL)
            ->get()
            ->keyBy(fn($r) => $r->employee_id . '_' . Carbon::parse($r->date)->toDateString());

        // ── Group scans by pin & date ──
        // $grouped = [];
        // foreach ($rawScans as $scan) {
        //     $pin  = (string) $scan->pin;
        //     $date = Carbon::parse($scan->scan_date)->toDateString();
        //     $time = Carbon::parse($scan->scan_date)->format('H:i:s');
        //     $mode = (int) $scan->inoutmode;
        //     $sn   = $scan->sn ?? null;

        //     if (!isset($grouped[$pin][$date])) {
        //         $grouped[$pin][$date] = ['time_in' => null, 'time_out' => null, 'device_sn' => $sn];
        //     }

        //     if ($mode === 1 && $grouped[$pin][$date]['time_in'] === null) {
        //         $grouped[$pin][$date]['time_in'] = $time;
        //     }
        //     if ($mode === 2) {
        //         $grouped[$pin][$date]['time_out'] = $time;
        //     }
        // }
        $grouped = [];
foreach ($rawScans as $scan) {
    $pin  = (string) $scan->pin;
    $date = Carbon::parse($scan->scan_date)->toDateString();
    $time = Carbon::parse($scan->scan_date)->format('H:i:s');
    $mode = (int) $scan->inoutmode;
    $sn   = $scan->sn ?? null;

    // ── Deteksi overnight: inoutmode=2, tidak ada inoutmode=1 hari ini,
    //    tapi hari sebelumnya punya shift overnight ──
    if ($mode === 2) {
        $employee  = $employees->get($pin);
        $prevDate  = Carbon::parse($date)->subDay()->toDateString();
        $prevKey   = ($employee?->id ?? '') . '_' . $prevDate;
        $prevRoster = $rosters->get($prevKey);

        $isPrevOvernight = $prevRoster
            && $prevRoster->shift_id
            && in_array($prevRoster->shift_id, $overnightShiftIds);

        $hasTimeInToday = isset($grouped[$pin][$date]['time_in']) && $grouped[$pin][$date]['time_in'] !== null;

        if ($isPrevOvernight && !$hasTimeInToday) {
            // ← Scan pulang ini milik shift hari sebelumnya
            if (!isset($grouped[$pin][$prevDate])) {
                $grouped[$pin][$prevDate] = ['time_in' => null, 'time_out' => null, 'device_sn' => $sn];
            }
            $grouped[$pin][$prevDate]['time_out'] = $time;
            continue; // ← jangan masukkan ke tanggal hari ini
        }
    }

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
    /** Batas tanggal untuk non-HR: 26 dua bulan lalu sampai 25 bulan depan. */
    private function allowedRange(): array
    {
        $first = today()->startOfMonth();
        return [
            $first->copy()->subMonthsNoOverflow(2)->day(26)->startOfDay(),
            $first->copy()->addMonthNoOverflow()->day(25)->endOfDay(),
        ];
    }

    /**
     * Batasi query karyawan ke yang boleh dilihat SPV/Manager:
     * satu company + store + departemen yang sama, ditambah bawahan langsung.
     * Mengembalikan false kalau SPV tidak punya cakupan sama sekali.
     */
    private function applySpvScope($query, Employee $userEmployee): bool
    {
        $companyId    = $userEmployee->company_id;
        $userStoreIds = $userEmployee->store()->pluck('stores_tables.id')->toArray();
        $userDeptIds  = $userEmployee->department()->pluck('departments_tables.id')->toArray();
        $bawahanIds   = $userEmployee->bawahanList()->pluck('employees_tables.id')->toArray();

        if (empty($userStoreIds) || empty($userDeptIds)) {
            if (empty($bawahanIds)) return false;
            $query->whereIn('employees_tables.id', $bawahanIds);
            return true;
        }

        $query->where(function ($q) use ($companyId, $userStoreIds, $userDeptIds, $bawahanIds) {
            $q->where(function ($q1) use ($companyId, $userStoreIds, $userDeptIds) {
                $q1->where('company_id', $companyId)
                    ->whereExists(fn($sq) => $sq->select(DB::raw(1))->from('employee_stores')
                        ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                        ->whereIn('employee_stores.store_id', $userStoreIds))
                    ->whereExists(fn($sq) => $sq->select(DB::raw(1))->from('employee_departments')
                        ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                        ->whereIn('employee_departments.department_id', $userDeptIds));
            });
            if (!empty($bawahanIds)) {
                $q->orWhereIn('employees_tables.id', $bawahanIds);
            }
        });

        return true;
    }

    /** Boleh melihat data PIN ini? (HR semua, SPV cakupannya, karyawan miliknya sendiri) */
    private function canAccessPin($user, string $pin, bool $canManage, bool $canSpvManager): bool
    {
        if ($canManage) return true;
        $own = (string) ($user->employee?->pin ?? '');
        if ($own !== '' && $own === $pin) return true;
        if (!$canSpvManager || !$user->employee) return false;

        $q = Employee::where('pin', $pin);
        return $this->applySpvScope($q, $user->employee) && $q->exists();
    }

    /** Nama karyawan, store utama, jabatan utama berdasarkan PIN (untuk edit/show). */
    private function employeeInfoByPin(string $pin): array
    {
        $employee = Employee::with([
            'store'    => fn($q) => $q->wherePivot('is_primary', true),
            'position' => fn($q) => $q->wherePivot('is_primary', true),
        ])->where('pin', $pin)->first();

        return [
            'employee_name'     => $employee?->employee_name ?? '-',
            'employee_pengenal' => $employee?->employee_pengenal ?? '-',
            'status_employee'   => $employee?->status_employee ?? '-',
            'store_name'        => $employee?->store->first()?->name ?? '-',
            'position_name'     => $employee?->position->first()?->name ?? '-',
            'employee'          => $employee,
        ];
    }

    /** Scan mentah dari mesin (scan pertama per mode 1 = masuk, 2 = pulang) + roster hari itu. */
    private function rawScansFor(string $pin, string $date, ?Employee $employee): array
    {
        $scans = Fingerprints::with('devicefingerprints')
            ->where('pin', $pin)
            ->whereDate('scan_date', $date)
            ->orderBy('scan_date')
            ->get();

        $raw = ['count' => $scans->count(), 'all' => []];
        foreach ([1, 2] as $mode) {
            $first = $scans->where('inoutmode', $mode)->first();
            $raw["in_$mode"]     = $first ? Carbon::parse($first->scan_date)->format('H:i:s') : null;
            $raw["device_$mode"] = $first ? trim(optional($first->devicefingerprints)->device_name ?? '') : null;
        }
        $modeLabel = [1 => 'Masuk', 2 => 'Pulang', 3 => 'Istirahat', 4 => 'Istirahat', 5 => 'Lembur masuk', 6 => 'Lembur pulang'];
        foreach ($scans as $sc) {
            $raw['all'][] = [
                'time'   => Carbon::parse($sc->scan_date)->format('H:i:s'),
                'mode'   => $modeLabel[(int) $sc->inoutmode] ?? ('Mode ' . $sc->inoutmode),
                'device' => trim(optional($sc->devicefingerprints)->device_name ?? '') ?: '-',
            ];
        }

        $raw['roster'] = null;
        if ($employee) {
            $r = Roster::with('shift:id,shift_name,start_time,end_time')
                ->where('employee_id', $employee->id)->whereDate('date', $date)->first();
            if ($r) {
                $raw['roster'] = [
                    'day_type' => $r->day_type,
                    'shift'    => $r->shift?->shift_name,
                    'time'     => $r->shift ? substr($r->shift->start_time, 0, 5) . '–' . substr($r->shift->end_time, 0, 5) : null,
                ];
            }
        }
        return $raw;
    }

    private static function durationLabel(?string $in, ?string $out, string $date): ?string
    {
        if (!$in || !$out) return null;
        $a = Carbon::parse("$date $in");
        $b = Carbon::parse("$date $out");
        if ($b->lt($a)) $b->addDay();
        $m = (int) $a->diffInMinutes($b);
        return sprintf('%d hour%s %d minute%s', intdiv($m, 60), intdiv($m, 60) !== 1 ? 's' : '', $m % 60, $m % 60 !== 1 ? 's' : '');
    }

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

    $employeesQuery = Employee::with([
        'position' => fn($q) => $q->wherePivot('is_primary', true),
        'store'    => fn($q) => $q->wherePivot('is_primary', true),
    ])
    ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'status_employee', 'status', 'company_id')
    ->whereNotNull('pin');

    if ($canManage) {
        // ManageFingerspot: bebas filter store
        if ($storeName) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('stores_tables.name', $storeName));
        }
    } elseif ($canSpvManager) {
        $userEmployee = $user->employee;
        if (!$userEmployee || !$this->applySpvScope($employeesQuery, $userEmployee)) return collect();

        // Filter store hanya untuk store yang memang dimiliki SPV
        if ($storeName && in_array($storeName, $userEmployee->store()->pluck('stores_tables.name')->toArray(), true)) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('stores_tables.name', $storeName));
        }
    } else {
        $ownPin = $user->employee?->pin;
        if (!$ownPin) return collect();
        $employeesQuery->where('pin', $ownPin);
    }

    $employees   = $employeesQuery->get()->keyBy(fn($e) => (string) $e->pin);
    $employeeIds = $employees->pluck('id')->filter()->values()->toArray();

    $editedFingerprints = EditedFingerprint::whereBetween('scan_date', [$startDate->toDateString(), $endDate->toDateString()])
        ->whereIn('pin', $employees->keys()->all())
        ->get(['id', 'pin', 'scan_date', 'status', 'in_1', 'in_2'])
        ->keyBy(fn($e) => $e->pin . '_' . Carbon::parse($e->scan_date)->toDateString());
    $editedKeys = $editedFingerprints->keys()->toArray();

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
            'edited_in_1'           => $editedRecord?->in_1,
            'edited_in_2'           => $editedRecord?->in_2,
            'day_type'              => $roster?->day_type,
        ];

        for ($i = 1; $i <= 10; $i++) {
            $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
                $row["full_in_$i"] = null; // ← tambah ini

        }

        // $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row, $deviceNames) {
        //     if ($mode < 1 || $mode > 10) return;
        //     $sorted  = $items->sortBy('scan_date');
        //     $times   = $sorted->pluck('scan_date')->map(fn($d) => Carbon::parse($d)->format('H:i:s'))->implode(', ');
        //     $devices = $sorted->map(fn($i) => $deviceNames->get($i->sn) ?? '')->implode(', ');
        //     $row["in_$mode"]      = $times;
        //     $row["device_$mode"]  = $devices;
        //     $row["combine_$mode"] = trim($times . ' ' . $devices);
        // });
        $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row, $deviceNames) {
    if ($mode < 1 || $mode > 10) return;
    $sorted  = $items->sortBy('scan_date');
    
    // ← simpan waktu + tanggal lengkap untuk keperluan duration
    $times   = $sorted->pluck('scan_date')->map(fn($d) => Carbon::parse($d)->format('H:i:s'))->implode(', ');
    $fullTimes = $sorted->pluck('scan_date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d H:i:s'))->implode(', '); // ← tambah
    $devices = $sorted->map(fn($i) => $deviceNames->get($i->sn) ?? '')->implode(', ');
    
    $row["in_$mode"]       = $times;
    $row["full_in_$mode"]  = $fullTimes; // ← tambah untuk duration calculation
    $row["device_$mode"]   = $devices;
    $row["combine_$mode"]  = trim($times . ' ' . $devices);
});

        // $times = collect(range(1, 10))
        //     ->flatMap(fn($i) => $row["in_$i"] ? explode(', ', $row["in_$i"]) : [])
        //     ->map(fn($t) => Carbon::parse($t))
        //     ->sort()
        //     ->values();

        // if ($times->count() >= 2) {
        //     $minutes         = $times->first()->diffInMinutes($times->last());
        //     $row['duration'] = sprintf(
        //         '%d hour%s %d minute%s',
        //         intdiv($minutes, 60),
        //         intdiv($minutes, 60) !== 1 ? 's' : '',
        //         $minutes % 60,
        //         $minutes % 60 !== 1 ? 's' : ''
        //     );
        // } else {
        //     $row['duration'] = 'invalid';
        // }
        // ← Pakai full datetime untuk duration
$times = collect(range(1, 10))
    ->flatMap(fn($i) => $row["full_in_$i"] ? explode(', ', $row["full_in_$i"]) : [])
    ->map(fn($t) => Carbon::parse(trim($t)))
    ->sort()
    ->values();

if ($times->count() >= 2) {
    $first   = $times->first();
    $last    = $times->last();
    $minutes = $first->diffInMinutes($last);

    $row['duration'] = sprintf(
        '%d hour%s %d minute%s',
        intdiv($minutes, 60),
        intdiv($minutes, 60) !== 1 ? 's' : '',
        $minutes % 60,
        $minutes % 60 !== 1 ? 's' : ''
    );
} elseif ($times->count() === 1) {
    $isOvernightShift = $roster
        && $roster->shift
        && $roster->shift->end_time < $roster->shift->start_time;
    $row['duration'] = $isOvernightShift ? 'overnight' : 'invalid';
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
    [$minAllowedDate, $maxAllowedDate] = $this->allowedRange();

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
            'missing' => $result->filter(fn($r) => empty($r['in_2']) && $r['duration'] !== 'overnight')->count(),
            'pending' => $result->filter(fn($r) => in_array($r['edited_status'], $canManage ? ['approved_spv'] : ['draft'], true))->count(),
        ];

$spvPin = !$canManage ? $user->employee?->pin : null;

        return DataTables::of($result)
            ->with(['stats' => $stats])
            ->addColumn('in_1_colored', function ($row) {
                if (!$row['in_1']) return '-';
                return $row['is_late']
                    ? '<span class="text-danger fw-bold">' . $row['in_1'] . '</span>'
                    : '<span class="text-success">' . $row['in_1'] . '</span>';
            })
            
->addColumn('show_url', fn($row) => !empty($row['edited_fingerprint_id'])
    ? route('pages.Fingerprints.show', ['pin' => $row['pin'], 'scan_date' => $row['scan_date']]) : null)
->addColumn('edit_url', function ($row) use ($canManage, $canSpvManager, $canViewOwn, $spvPin) {
    $own    = $spvPin !== null && (string) $row['pin'] === (string) $spvPin;
    $status = $row['edited_status'];
    if ($status === 'approved_hr') return null; // sudah final
    if ($canManage) {
        $ok = true;
    } elseif ($row['is_updated']) {
        // Klarifikasi ulang hanya kalau masih draft / ditolak SPV
        $ok = ($own || ($canViewOwn && !$canSpvManager)) && in_array($status, ['draft', 'rejected_spv'], true);
    } else {
        // Belum diklarifikasi: hanya milik sendiri, dan scan masuk/pulang belum lengkap
        $ok = ($own || ($canViewOwn && !$canSpvManager)) && (empty($row['in_1']) || empty($row['in_2']));
    }
    return $ok ? route('pages.Fingerprints.edit', ['pin' => $row['pin'], 'scan_date' => $row['scan_date']]) : null;
})
->addColumn('can_select', function ($row) use ($canManage, $canSpvManager, $spvPin) {
    if (empty($row['edited_fingerprint_id'])) return false;
    if ($canManage) return in_array($row['edited_status'], ['approved_spv', 'draft'], true);
    // SPV tidak menyetujui klarifikasinya sendiri
    return $canSpvManager && $row['edited_status'] === 'draft' && (string) $row['pin'] !== (string) $spvPin;
})
->addColumn('action', fn() => '')
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
    $status    = $request->input('status'); // ← tambah
    $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
    $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

    // ← Update date range validation sama seperti getFingerprints
    if (!$canManage && $canSpvManager) {
        [$minAllowedDate, $maxAllowedDate] = $this->allowedRange();

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
    
    private const EDITABLE_BY_EMPLOYEE = ['draft', 'rejected_spv'];

    public function editFingerprint($pin, Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $pin  = (string) $pin;

        $canManage     = $user->hasPermissionTo('ManageFingerspot');
        $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
        $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');
        if (!$canManage && !$canSpvManager && !$canViewOwn) abort(403, 'Unauthorized');

        // Selain HR, klarifikasi hanya untuk data milik sendiri
        if (!$canManage && $pin !== (string) ($user->employee?->pin ?? '')) {
            abort(403, 'Anda hanya bisa mengklarifikasi data fingerprint milik sendiri.');
        }

        $request->merge(['pin' => $pin]);
        $request->validate(['pin' => 'required|string|max:20', 'scan_date' => 'required|date']);
        $date = Carbon::parse($request->input('scan_date'))->toDateString();

        $data = EditedFingerprint::with('attachments')->where('pin', $pin)->whereDate('scan_date', $date)->first();

        if ($data && $data->status === 'approved_hr') {
            return redirect()->route('pages.Fingerprints')
                ->with('error', 'Data fingerprint sudah di-approve HR, sudah tidak bisa untuk klarifikasi.');
        }
        if ($data && !$canManage && !in_array($data->status, self::EDITABLE_BY_EMPLOYEE, true)) {
            return redirect()->route('pages.Fingerprints')
                ->with('error', 'Klarifikasi ini sedang diproses atasan/HR, tidak bisa diubah lagi.');
        }

        $info = $this->employeeInfoByPin($pin);
        $raw  = $this->rawScansFor($pin, $date, $info['employee']);

        if (!$data && $raw['count'] === 0) {
            return redirect()->route('pages.Fingerprints')->with('error', 'Tidak ada data scan pada tanggal tersebut.');
        }
        if (!$data && !$canManage && $raw['in_1'] && $raw['in_2']) {
            return redirect()->route('pages.Fingerprints')
                ->with('error', 'Scan masuk dan pulang Anda sudah lengkap, tidak perlu diedit.');
        }

        $devices = Devicefingerprint::select('sn', 'device_name')->whereNotNull('device_name')->orderBy('device_name')->get();

        return view('pages.Fingerprints.edit', [
            'data'      => $data,
            'isEdited'  => (bool) $data,
            'pin'       => $pin,
            'scanDate'  => $date,
            'info'      => $info,
            'raw'       => $raw,
            'devices'   => $devices,
            'canManage' => $canManage,
        ]);
    }

    public function showFingerprint($pin, Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $pin  = (string) $pin;

        $canManage     = $user->hasPermissionTo('ManageFingerspot');
        $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
        $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');
        if (!$canManage && !$canSpvManager && !$canViewOwn) abort(403, 'Unauthorized');

        if (!$this->canAccessPin($user, $pin, $canManage, $canSpvManager)) {
            abort(403, 'Anda tidak punya akses ke data fingerprint karyawan ini.');
        }

        $request->merge(['pin' => $pin]);
        $request->validate(['pin' => 'required|string|max:20', 'scan_date' => 'required|date']);
        $date = Carbon::parse($request->input('scan_date'))->toDateString();

        $data = EditedFingerprint::with('attachments')->where('pin', $pin)->whereDate('scan_date', $date)->first();
        $info = $this->employeeInfoByPin($pin);
        $raw  = $this->rawScansFor($pin, $date, $info['employee']);

        if (!$data && $raw['count'] === 0) {
            return redirect()->route('pages.Fingerprints')->with('error', 'Tidak ada data scan pada tanggal tersebut.');
        }

        $approver = null;
        if ($data?->approved_by) {
            $u = User::with('employee:id,employee_name')->find($data->approved_by);
            $approver = $u?->employee?->employee_name ?? $u?->username;
        }

        $own    = $pin === (string) ($user->employee?->pin ?? '');
        $status = $data?->status;

        $ownPin  = (string) ($user->employee?->pin ?? '');
        $editUrl = null;
        if ($data && $status !== 'approved_hr' && ($canManage || ($own && in_array($status, self::EDITABLE_BY_EMPLOYEE, true)))) {
            $editUrl = route('pages.Fingerprints.edit', ['pin' => $pin, 'scan_date' => $date]);
        }

        $history = \Spatie\Activitylog\Models\Activity::with('causer.employee')
            ->where('log_name', 'fingerprint')
            ->when($data, fn($q) => $q->where('subject_type', EditedFingerprint::class)->where('subject_id', $data->id), fn($q) => $q->whereRaw('1=0'))
            ->latest()->limit(20)->get();

        return view('pages.Fingerprints.show', [
            'data'       => $data,
            'isEdited'   => (bool) $data,
            'pin'        => $pin,
            'scanDate'   => $date,
            'info'       => $info,
            'raw'        => $raw,
            'approver'   => $approver,
            'history'    => $history,
            'editUrl'    => $editUrl,
            // SPV menyetujui draft orang lain; HR memproses yang sudah disetujui SPV
            'canSpvAct'  => $data && !$canManage && $canSpvManager && !$own && $status === 'draft',
            'canHrAct'   => $data && $canManage && in_array($status, ['approved_spv', 'draft'], true),
            'canManage'  => $canManage,
        ]);
    }

    public function updateFingerprint(Request $request, $pin = null, $scanDate = null)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        $canManage     = $user->hasPermissionTo('ManageFingerspot');
        $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
        $canViewOwn    = $user->hasPermissionTo('ViewFingerspot');
        if (!$canManage && !$canSpvManager && !$canViewOwn) abort(403, 'Unauthorized');

        $timeRule = ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/'];
        $validated = $request->validate([
            'pin'           => 'required|string|max:20',
            'scan_date'     => 'required|date',
            'notes'         => 'required|string|min:5|max:1000',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,webp|max:512',
            ...collect(range(1, 10))->flatMap(fn($i) => [
                "in_$i"     => $timeRule,
                "device_$i" => 'nullable|string|max:50',
            ])->toArray(),
        ], [
            'notes.required'     => 'Alasan klarifikasi wajib diisi.',
            'notes.min'          => 'Alasan minimal 5 karakter.',
            'attachments.*.max'  => 'Ukuran tiap foto maksimal 512 KB.',
            'attachments.*.mimes'=> 'Lampiran harus foto jpg, jpeg, png, atau webp.',
            'in_1.regex'         => 'Format jam masuk harus HH:MM atau HH:MM:SS.',
            'in_2.regex'         => 'Format jam pulang harus HH:MM atau HH:MM:SS.',
        ]);

        $pinValue = (string) $validated['pin'];
        $date     = Carbon::parse($validated['scan_date'])->toDateString();

        // URL dan isi form harus menunjuk data yang sama
        if (($pin !== null && (string) $pin !== $pinValue) || ($scanDate !== null && Carbon::parse($scanDate)->toDateString() !== $date)) {
            abort(422, 'Data form tidak sesuai dengan alamat halaman.');
        }
        // Selain HR, hanya data milik sendiri
        if (!$canManage && $pinValue !== (string) ($user->employee?->pin ?? '')) {
            abort(403, 'Anda hanya bisa mengklarifikasi data fingerprint milik sendiri.');
        }

        $existing = EditedFingerprint::where('pin', $pinValue)->whereDate('scan_date', $date)->first();
        if ($existing && $existing->status === 'approved_hr') {
            return back()->with('error', 'Data sudah di-approve HR, tidak bisa diubah lagi.');
        }
        if ($existing && !$canManage && !in_array($existing->status, self::EDITABLE_BY_EMPLOYEE, true)) {
            return back()->with('error', 'Klarifikasi ini sedang diproses atasan/HR, tidak bisa diubah lagi.');
        }

        $info = $this->employeeInfoByPin($pinValue);
        $raw  = $this->rawScansFor($pinValue, $date, $info['employee']);

        // Scan asli dari mesin tidak boleh diganti lewat klarifikasi karyawan
        if (!$canManage) {
            foreach ([1, 2] as $i) {
                if ($raw["in_$i"]) {
                    $validated["in_$i"]     = $raw["in_$i"];
                    $validated["device_$i"] = $raw["device_$i"] ?: ($validated["device_$i"] ?? null);
                }
            }
        }
        foreach ([1, 2] as $i) {
            if (!empty($validated["in_$i"]) && strlen($validated["in_$i"]) === 5) $validated["in_$i"] .= ':00';
        }
        if (empty($validated['in_1']) || empty($validated['in_2'])) {
            return back()->withInput()->with('error', 'Jam masuk dan jam pulang wajib terisi.');
        }

        $isNew   = !$existing;
        $fields  = ['in_1', 'device_1', 'in_2', 'device_2', 'notes', 'status', 'approved_by', 'approved_at', 'rejection_note'];
        $oldData = $isNew ? [] : collect($fields)->mapWithKeys(fn($f) => [$f => $existing->{$f}])->all();

        $payload = collect($validated)->except(['pin', 'scan_date', 'attachments'])->all();
        // Identitas diambil dari data karyawan, bukan dari input form
        $payload['employee_name'] = $info['employee_name'];
        $payload['position_name'] = $info['position_name'];
        $payload['store_name']    = $info['store_name'];
        $payload['duration']      = self::durationLabel($validated['in_1'], $validated['in_2'], $date);

        if (!$canManage || $isNew || $existing->status === null) {
            // Masuk ke alur approval dari awal; catatan approval lama dikosongkan
            $payload['status']         = 'draft';
            $payload['approved_by']    = null;
            $payload['approved_at']    = null;
            $payload['rejection_note'] = null;
        }

        try {
            $editedFingerprint = DB::connection('mysql_second')->transaction(function () use ($pinValue, $date, $payload) {
                return EditedFingerprint::updateOrCreate(['pin' => $pinValue, 'scan_date' => $date], $payload);
            });

            if ($request->hasFile('attachments')) {
                // Karyawan mengunggah ulang: lampiran lama diganti
                if (!$canManage) {
                    foreach (\App\Models\EditedFingerprintAttachment::where('edited_fingerprint_id', $editedFingerprint->id)->get() as $att) {
                        if (Storage::disk('s3')->exists($att->attachment)) Storage::disk('s3')->delete($att->attachment);
                        $att->delete();
                    }
                }
                foreach ($request->file('attachments') as $file) {
                    $fileName = Str::slug($info['employee_name'] ?: 'employee') . '-' . now()->timestamp . '-'
                        . str_replace('-', '', $date) . '-' . uniqid() . '.' . strtolower($file->getClientOriginalExtension());
                    $path = Storage::disk('s3')->putFileAs('employees-edited-fingerprints', $file, $fileName);
                    \App\Models\EditedFingerprintAttachment::create(['edited_fingerprint_id' => $editedFingerprint->id, 'attachment' => $path]);
                }
            }

            $editedFingerprint->refresh();
            $newData = collect($fields)->mapWithKeys(fn($f) => [$f => $editedFingerprint->{$f}])->all();
            $changes = [];
            foreach ($newData as $field => $newValue) {
                $oldValue = $oldData[$field] ?? null;
                if ((string) $oldValue !== (string) $newValue) $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
            }

            if (!empty($changes) || $isNew) {
                activity('fingerprint')->causedBy($user)->performedOn($editedFingerprint)
                    ->withProperties([
                        'pin' => $pinValue, 'scan_date' => $date,
                        'employee_name' => $info['employee_name'], 'position_name' => $info['position_name'], 'store_name' => $info['store_name'],
                        'action' => $isNew ? 'created' : 'updated', 'changes' => $changes, 'after' => $newData,
                    ])
                    ->log(($isNew ? 'Edit fingerprint baru untuk ' : 'Update fingerprint untuk ') . $info['employee_name']
                        . ($isNew ? '' : ' — field diubah: ' . implode(', ', array_keys($changes))));
            }

            return redirect()->route('pages.Fingerprints')->with('success', $canManage
                ? 'Data fingerprint tersimpan.'
                : 'Klarifikasi terkirim dan menunggu persetujuan atasan.');
        } catch (\Throwable $e) {
            Log::error('Gagal updateFingerprint', ['error' => $e->getMessage(), 'pin' => $pinValue, 'date' => $date]);
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan. Coba lagi.');
        }
    }

    /**
     * Cek satu klarifikasi boleh diubah statusnya oleh user ini.
     * Mengembalikan pesan error, atau null kalau boleh.
     */
    private function statusGuard($user, EditedFingerprint $fp, string $target, bool $canManage, bool $canSpvManager): ?string
    {
        if ($canManage) {
            if (!in_array($target, ['draft', 'approved_hr', 'rejected_hr'], true)) return 'HR hanya bisa set draft, approved_hr, atau rejected_hr.';
            if (in_array($target, ['approved_hr', 'rejected_hr'], true) && !in_array($fp->status, ['approved_spv', 'draft'], true)) {
                return 'Hanya data draft / approved SPV yang bisa diproses HR.';
            }
            return null;
        }
        if (!$canSpvManager) return 'Tidak punya akses.';
        if (!in_array($target, ['approved_spv', 'rejected_spv'], true)) return 'SPV hanya bisa set approved_spv atau rejected_spv.';
        if ($fp->status !== 'draft') return 'Hanya data draft yang bisa di-approve/reject SPV.';
        if ((string) $fp->pin === (string) ($user->employee?->pin ?? '')) return 'Klarifikasi milik sendiri tidak bisa Anda setujui sendiri.';
        if (!$this->canAccessPin($user, (string) $fp->pin, false, true)) return 'Karyawan ini di luar cakupan Anda.';
        return null;
    }

    public function updateStatus(Request $request, $id)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $canManage     = $user->hasPermissionTo('ManageFingerspot');
        $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
        if (!$canManage && !$canSpvManager) abort(403, 'Unauthorized');

        $request->validate([
            'status'         => 'required|in:draft,approved_spv,rejected_spv,approved_hr,rejected_hr',
            'rejection_note' => 'nullable|string|max:500|required_if:status,rejected_spv,rejected_hr',
        ], ['rejection_note.required_if' => 'Alasan penolakan wajib diisi.']);

        $fingerprint = EditedFingerprint::findOrFail($id);
        if ($msg = $this->statusGuard($user, $fingerprint, $request->status, $canManage, $canSpvManager)) {
            return $request->expectsJson() ? response()->json(['message' => $msg], 403) : back()->with('error', $msg);
        }

        $oldStatus = $fingerprint->status;
        $fingerprint->update([
            'status'         => $request->status,
            'approved_by'    => $request->status === 'draft' ? null : $user->id,
            'approved_at'    => $request->status === 'draft' ? null : now(),
            'rejection_note' => str_starts_with($request->status, 'rejected') ? $request->rejection_note : null,
        ]);

        activity('fingerprint')->causedBy($user)->performedOn($fingerprint)
            ->withProperties([
                'pin' => $fingerprint->pin, 'scan_date' => $fingerprint->scan_date, 'employee_name' => $fingerprint->employee_name,
                'action' => 'status',
                'changes' => ['status' => ['old' => $oldStatus, 'new' => $request->status]] + ($request->rejection_note ? ['rejection_note' => ['old' => null, 'new' => $request->rejection_note]] : []),
            ])
            ->log('Status fingerprint ' . $fingerprint->employee_name . ' diubah dari ' . $oldStatus . ' ke ' . $request->status);

        $msg = 'Status berhasil diperbarui.';
        return $request->expectsJson() ? response()->json(['message' => $msg]) : back()->with('success', $msg);
    }

    public function bulkStatus(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $canManage     = $user->hasPermissionTo('ManageFingerspot');
        $canSpvManager = $user->hasPermissionTo('ManageFingerspotSPVManager');
        if (!$canManage && !$canSpvManager) abort(403);

        $request->validate([
            'ids'            => 'required|array|max:500',
            'ids.*'          => 'integer',
            'status'         => 'required|in:approved_spv,rejected_spv,approved_hr,rejected_hr',
            'rejection_note' => 'nullable|string|max:500|required_if:status,rejected_spv,rejected_hr',
        ], ['rejection_note.required_if' => 'Alasan penolakan wajib diisi.']);

        if (!$canManage && !in_array($request->status, ['approved_spv', 'rejected_spv'], true)) abort(403);
        if ($canManage && !in_array($request->status, ['approved_hr', 'rejected_hr'], true)) abort(403);

        $approverName = $user->employee?->employee_name ?? $user->username;
        $updated = 0;
        $skipped = [];

        foreach (EditedFingerprint::whereIn('id', $request->ids)->get() as $fp) {
            if ($msg = $this->statusGuard($user, $fp, $request->status, $canManage, $canSpvManager)) {
                $skipped[] = ($fp->employee_name ?? $fp->pin) . ' (' . Carbon::parse($fp->scan_date)->format('d M') . '): ' . $msg;
                continue;
            }
            $oldStatus = $fp->status;
            $isReject  = str_starts_with($request->status, 'rejected');
            $fp->update([
                'status'         => $request->status,
                'approved_by'    => $user->id,
                'approved_at'    => now(),
                'rejection_note' => $isReject ? $request->rejection_note : null,
            ]);
            $updated++;

            try {
                activity('fingerprint')->causedBy($user)->performedOn($fp)
                    ->withProperties([
                        'pin' => $fp->pin, 'scan_date' => $fp->scan_date, 'employee_name' => $fp->employee_name ?? '-',
                        'position_name' => $fp->position_name ?? '-', 'store_name' => $fp->store_name ?? '-',
                        'action'  => 'bulk_status',
                        'changes' => ['status' => ['old' => $oldStatus, 'new' => $request->status], 'approved_by' => ['old' => null, 'new' => $approverName]]
                            + ($isReject ? ['rejection_note' => ['old' => null, 'new' => $request->rejection_note]] : []),
                    ])
                    ->log('Bulk status fingerprint ' . ($fp->employee_name ?? $fp->pin) . ' diubah dari ' . $oldStatus . ' ke ' . $request->status);
            } catch (\Throwable $e) {
                Log::error('Bulk status activity error', ['fp_id' => $fp->id, 'message' => $e->getMessage()]);
            }
        }

        return response()->json([
            'message' => $updated . ' klarifikasi diperbarui' . ($skipped ? ', ' . count($skipped) . ' dilewati.' : '.'),
            'updated' => $updated,
            'skipped' => $skipped,
            'reload'  => true,
        ]);
    }

    public function deleteAttachment(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user->hasAnyPermission(['ManageFingerspot', 'ManageFingerspotSPVManager', 'ViewFingerspot'])) abort(403);

        $request->validate(['id' => 'required|integer']);
        $attachment = \App\Models\EditedFingerprintAttachment::with('editedFingerprint')->findOrFail($request->id);
        $fp = $attachment->editedFingerprint;

        if (!$fp || !in_array($fp->status, self::EDITABLE_BY_EMPLOYEE, true)) {
            return response()->json(['message' => 'Lampiran klarifikasi yang sedang/sudah diproses tidak bisa dihapus.'], 403);
        }
        if (!$user->hasPermissionTo('ManageFingerspot') && (string) $fp->pin !== (string) ($user->employee?->pin ?? '')) {
            return response()->json(['message' => 'Anda hanya bisa menghapus lampiran milik sendiri.'], 403);
        }

        if (Storage::disk('s3')->exists($attachment->attachment)) Storage::disk('s3')->delete($attachment->attachment);
        $attachment->delete();

        return response()->json(['message' => 'Lampiran berhasil dihapus.']);
    }

    public function getLog(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user->hasPermissionTo('ManageFingerspot')) abort(403);

        $query = \Spatie\Activitylog\Models\Activity::with('causer.employee')
            ->where('log_name', 'fingerprint')
            ->latest();

        $fmt = function ($v) {
            if ($v === null || $v === '') return '-';
            if (is_array($v)) $v = json_encode($v);
            return e(Str::limit((string) $v, 80));
        };

        return DataTables::of($query)
            ->addColumn('causer_name', fn($row) => $row->causer?->employee?->employee_name ?? $row->causer?->username ?? '-')
            ->addColumn('employee_name', fn($row) => $row->properties['employee_name'] ?? '-')
            ->addColumn('pin', fn($row) => $row->properties['pin'] ?? '-')
            ->addColumn('scan_date', fn($row) => isset($row->properties['scan_date']) ? Carbon::parse($row->properties['scan_date'])->toDateString() : '-')
            ->addColumn('action_key', fn($row) => $row->properties['action'] ?? '')
            ->addColumn('action_badge', fn($row) => e($row->properties['action'] ?? '-'))
            ->addColumn('changes', function ($row) use ($fmt) {
                $changes = $row->properties['changes'] ?? [];
                if (empty($changes)) return '-';
                $html = '<ul class="fp-changes">';
                foreach ($changes as $field => $change) {
                    $html .= '<li><strong>' . e($field) . '</strong>: <del>' . $fmt($change['old'] ?? null) . '</del> <ins>' . $fmt($change['new'] ?? null) . '</ins></li>';
                }
                return $html . '</ul>';
            })
            ->editColumn('description', fn($row) => e($row->description))
            ->editColumn('created_at', fn($row) => $row->created_at->timezone('Asia/Makassar')->format('d M Y H:i'))
            ->rawColumns(['changes'])
            ->make(true);
    }
}
