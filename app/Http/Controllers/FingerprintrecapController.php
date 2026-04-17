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
     * Alur:
     * 1. Ambil PIN dari employees_tables (DB: db / mysql utama)
     * 2. Cari raw scan di att_log (DB: absensi / mysql_second)
     * 3. Group per PIN per hari → IN pertama, OUT terakhir
     * 4. Simpan ke fingerprints_recap (DB: db / mysql utama)
     * 5. Auto update status di schedules (DB: db / mysql utama)
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

        // ── 1. Ambil karyawan aktif yang punya PIN dari DB utama ──
        $employeesQuery = Employee::select('id', 'pin', 'store_id')
            ->whereNotNull('pin')
            ->whereNull('deleted_at');

        if ($request->store_name) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('name', $request->store_name));
        }

        $employees = $employeesQuery->get()->keyBy(fn($e) => (string) $e->pin);

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada karyawan dengan PIN terdaftar.',
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

        // ── 4. Simpan ke fingerprints_recap & update schedules (DB utama) ──
        $synced  = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($grouped as $pin => $dates) {

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

                // Simpan ke fingerprints_recap di DB utama via Model
                // Model FingerprintRecap tidak set $connection → otomatis pakai mysql (DB: db)
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

                // ── 5. Auto update status di schedules (DB utama) ──
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
                            $newStatus = $actualIn->gt($shiftStart->copy()->addMinutes(10))
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
     * ═══════════════════════════════════════════════════════
     * DataTables — tampilkan data fingerprints_recap
     * Semua tabel ada di DB utama (db) → tidak perlu cross-DB JOIN
     * fingerprints_recap → JOIN employees_tables, stores, positions, schedules, shifts
     * ═══════════════════════════════════════════════════════
     */
    public function getData(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());
        $storeName = $request->input('store_name');

        // Semua tabel ada di DB utama → pakai DB::table() biasa tanpa prefix
        $query = DB::table('fingerprints_recap as fr')
            ->join('employees_tables as e', 'e.id', '=', 'fr.employee_id')
            ->join('stores_tables as s', 's.id', '=', 'e.store_id')
            ->leftJoin('position_tables as p', 'p.id', '=', 'e.position_id')
            ->leftJoin('schedules as sch', function ($join) {
                $join->on('sch.employee_id', '=', 'fr.employee_id')
                     ->on('sch.date', '=', 'fr.date');
            })
            ->leftJoin('roster as r', 'r.id', '=', 'sch.roster_id')
            ->leftJoin('shifts_tables as sh', 'sh.id', '=', 'r.shift_id')
            ->select([
                'fr.*',
                'e.employee_name',
                'e.employee_pengenal',
                'e.pin as employee_pin',
                'e.status_employee',
                's.name as store_name',
                'p.name as position_name',
                'sch.status as attendance_status',
                'sch.day_type',
                'sh.shift_name',
            ])
            ->whereBetween('fr.date', [$startDate, $endDate])
            ->whereNull('e.deleted_at');

        if ($storeName) {
            $query->where('s.name', $storeName);
        }

        return DataTables::of($query)
            ->addColumn('location', fn($r) => $r->store_name ?? '-')
            ->addColumn('employee_name', fn($r) => $r->employee_name ?? '-')
            ->addColumn('nip', fn($r) => $r->employee_pengenal ?? '-')
            ->addColumn('position', fn($r) => $r->position_name ?? '-')
            ->addColumn('status_employee', fn($r) => $r->status_employee ?? '-')

            ->addColumn('date', fn($r) =>
                $r->date ? Carbon::parse($r->date)->format('d-m-Y') : '-'
            )

            ->addColumn('synced_at', fn($r) =>
                $r->synced_at ? Carbon::parse($r->synced_at)->format('d-m-Y H:i:s') : '-'
            )

            ->addColumn('roster', function ($r) {
                if (!$r->day_type) return '-';
                if ($r->day_type !== 'Work') return $r->day_type;
                return $r->shift_name ?? 'Work';
            })

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