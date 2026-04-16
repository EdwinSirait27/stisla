<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\FingerprintRecap;
use App\Models\Schedule;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class FingerprintRecapController extends Controller
{
    /**
     * Tampilkan halaman Fingerprint Recap
     */
    public function index()
    {
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        return view('pages.FingerprintRecap.FingerprintRecap', compact('stores'));
    }

    /**
     * ═══════════════════════════════════════════════════════
     * TOMBOL: Fingerprint Recap Otomatis
     * 1. Ambil semua PIN dari employees_tables (mysql utama)
     * 2. Cari di att_log (mysql_second) WHERE pin IN [...] AND scan_date BETWEEN
     * 3. Group per PIN per hari → IN pertama (inoutmode=1), OUT terakhir (inoutmode=2)
     * 4. Simpan ke fingerprint_recaps (mysql utama)
     * 5. Auto update status di schedules (Attended/Late/Absent)
     * PIN = jembatan antara mysql_second dan mysql utama
     * ═══════════════════════════════════════════════════════
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

        // ── 1. Ambil semua karyawan aktif yang punya PIN dari DB utama ──
        $employeesQuery = Employee::select('id', 'pin', 'store_id')
            ->whereNotNull('pin')
            ->whereNull('deleted_at');

        if ($request->store_name) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('name', $request->store_name));
        }

        // Key by PIN (string) untuk lookup cepat
        $employees = $employeesQuery->get()->keyBy(fn($e) => (string) $e->pin);

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada karyawan dengan PIN terdaftar.',
            ], 422);
        }

        $pins = $employees->keys()->toArray();

        // ── 2. Ambil raw scan dari mysql_second berdasarkan PIN ──
        // PIN adalah jembatan antara att_log dan employees_tables
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
                'message' => 'Tidak ada data fingerprint di rentang tanggal tersebut.',
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

        // ── 4. Simpan ke fingerprint_recaps & update schedules ──
        $synced  = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($grouped as $pin => $dates) {

            // Lookup employee berdasarkan PIN ← jembatan utama
            $employee = $employees->get($pin);

            if (!$employee) {
                $skipped++;
                $errors[] = "PIN {$pin} tidak ditemukan di data karyawan.";
                continue;
            }

            foreach ($dates as $date => $times) {
                $timeIn   = $times['time_in'];
                $timeOut  = $times['time_out'];
                $deviceSn = $times['device_sn'];

                // Hitung durasi kerja dalam menit
                $duration = null;
                if ($timeIn && $timeOut) {
                    $dtIn  = Carbon::parse($date . ' ' . $timeIn);
                    $dtOut = Carbon::parse($date . ' ' . $timeOut);
                    if ($dtOut->lt($dtIn)) $dtOut->addDay(); // handle overnight shift
                    $duration = (int) $dtIn->diffInMinutes($dtOut);
                }

                // Simpan ke fingerprint_recaps (DB utama)
                FingerprintRecap::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date'        => $date,
                    ],
                    [
                        'pin'              => $pin,
                        'time_in'          => $timeIn,
                        'time_out'         => $timeOut,
                        'duration_minutes' => $duration,
                        'device_sn'        => $deviceSn,
                        'sync_status'      => 'Synced',
                        'synced_at'        => now(),
                    ]
                );

                // ── 5. Auto update status di schedules ──
                $schedule = Schedule::with('roster.shift')
                    ->where('employee_id', $employee->id)
                    ->where('date', $date)
                    ->first();

                if ($schedule && $schedule->day_type === 'Work') {
                    $newStatus = 'Absent';

                    if ($timeIn) {
                        $shift = $schedule->roster?->shift;
                        if ($shift) {
                            $shiftStart = Carbon::parse($date . ' ' . $shift->start_time);
                            $actualIn   = Carbon::parse($date . ' ' . $timeIn);
                            // Toleransi 15 menit
                            $newStatus  = $actualIn->gt($shiftStart->copy()->addMinutes(15))
                                ? 'Late'
                                : 'Attended';
                        } else {
                            $newStatus = 'Attended';
                        }
                    }

                    $schedule->update(['status' => $newStatus]);
                }

                $synced++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil merekap {$synced} data absensi. ({$skipped} PIN tidak cocok)",
            'synced'  => $synced,
            'skipped' => $skipped,
            'errors'  => $errors,
        ]);
    }

    /**
     * DataTables — tampilkan data fingerprint_recaps
     * Menggunakan prefix DB name untuk cross-database JOIN
     * fingerprints_recap ada di mysql_second (absensi)
     * employees_tables, stores, dll ada di mysql utama
     */
    public function getData(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());
        $storeName = $request->input('store_name');

        // Ambil nama database dari config
        $mainDb   = config('database.connections.mysql.database');        // DB utama (employees, stores, dll)
        $secondDb = config('database.connections.mysql_second.database'); // DB absensi (fingerprints_recap)

        $query = DB::connection('mysql_second')
            ->table("{$secondDb}.fingerprints_recap")
            ->select([
                "{$secondDb}.fingerprints_recap.*",
                "{$mainDb}.employees_tables.employee_name",
                "{$mainDb}.employees_tables.employee_pengenal",
                "{$mainDb}.employees_tables.pin as employee_pin",
                "{$mainDb}.employees_tables.status_employee",
                "{$mainDb}.stores.name as store_name",
                "{$mainDb}.positions.name as position_name",
                "{$mainDb}.schedules.status as attendance_status",
                "{$mainDb}.schedules.day_type",
                "{$mainDb}.shifts.shift_name",
            ])
            ->join(
                "{$mainDb}.employees_tables",
                "{$mainDb}.employees_tables.id",
                '=',
                "{$secondDb}.fingerprints_recap.employee_id"
            )
            ->join(
                "{$mainDb}.stores",
                "{$mainDb}.stores.id",
                '=',
                "{$mainDb}.employees_tables.store_id"
            )
            ->leftJoin(
                "{$mainDb}.positions",
                "{$mainDb}.positions.id",
                '=',
                "{$mainDb}.employees_tables.position_id"
            )
            ->leftJoin("{$mainDb}.schedules", function ($join) use ($mainDb, $secondDb) {
                $join->on(
                    "{$mainDb}.schedules.employee_id",
                    '=',
                    "{$secondDb}.fingerprints_recap.employee_id"
                )
                ->on(
                    "{$mainDb}.schedules.date",
                    '=',
                    "{$secondDb}.fingerprints_recap.date"
                );
            })
            ->leftJoin(
                "{$mainDb}.rosters",
                "{$mainDb}.rosters.id",
                '=',
                "{$mainDb}.schedules.roster_id"
            )
            ->leftJoin(
                "{$mainDb}.shifts",
                "{$mainDb}.shifts.id",
                '=',
                "{$mainDb}.rosters.shift_id"
            )
            ->whereBetween("{$secondDb}.fingerprints_recap.date", [$startDate, $endDate])
            ->whereNull("{$mainDb}.employees_tables.deleted_at");

        if ($storeName) {
            $query->where("{$mainDb}.stores.name", $storeName);
        }

        return DataTables::of($query)
            ->addColumn('location', fn($r) => $r->store_name ?? '-')
            ->addColumn('employee_name', fn($r) => $r->employee_name ?? '-')
            ->addColumn('nip', fn($r) => $r->employee_pengenal ?? '-')
            ->addColumn('position', fn($r) => $r->position_name ?? '-')
            ->addColumn('status_employee', fn($r) => $r->status_employee ?? '-')

            // Format tanggal
            ->addColumn('date', fn($r) =>
                $r->date ? Carbon::parse($r->date)->format('d-m-Y') : '-'
            )

            // Format synced_at
            ->addColumn('synced_at', fn($r) =>
                $r->synced_at ? Carbon::parse($r->synced_at)->format('d-m-Y H:i:s') : '-'
            )

            // Nama shift (Pagi/Siang/Malam/Off/Holiday)
            ->addColumn('roster', function ($r) {
                if (!$r->day_type) return '-';
                if ($r->day_type !== 'Work') return $r->day_type;
                return $r->shift_name ?? 'Work';
            })

            // Status kehadiran dengan badge warna
            ->addColumn('attendance_status', function ($r) {
                $status = $r->attendance_status ?? '-';
                $colors = [
                    'Attended'  => 'success',
                    'Late'      => 'warning',
                    'Absent'    => 'danger',
                    'Scheduled' => 'secondary',
                ];
                $color = $colors[$status] ?? 'secondary';
                return "<span class='badge badge-{$color}'>{$status}</span>";
            })

            // Durasi kerja
            ->addColumn('duration_format', function ($r) {
                if (!$r->duration_minutes) return '-';
                $h = intdiv($r->duration_minutes, 60);
                $m = $r->duration_minutes % 60;
                return "{$h} jam {$m} menit";
            })

            ->rawColumns(['attendance_status'])
            ->make(true);
    }
}