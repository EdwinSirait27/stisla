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
     * 1. Ambil semua PIN dari employees_tables
     * 2. Cari di att_log (mysql second) WHERE pin IN [...] AND scan_date BETWEEN
     * 3. Group per PIN per hari → IN pertama (inoutmode=1), OUT terakhir (inoutmode=2)
     * 4. Simpan ke fingerprint_recaps
     * 5. Auto update status di schedules (Attended/Late/Absent)
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

        // ── 1. Ambil semua karyawan aktif yang punya PIN ──
        $employeesQuery = Employee::select('id', 'pin', 'store_id')
            ->whereNotNull('pin')
            ->whereNull('deleted_at');

        if ($request->store_name) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('name', $request->store_name));
        }

        // key by PIN untuk lookup cepat
        $employees = $employeesQuery->get()->keyBy(fn($e) => (string) $e->pin);

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada karyawan dengan PIN terdaftar.',
            ], 422);
        }

        $pins = $employees->keys()->toArray();

        // ── 2. Ambil raw scan dari absensi (mysql second) ──
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

        // ── 3. Group per PIN per tanggal ──
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

        foreach ($grouped as $pin => $dates) {
            $employee = $employees->get($pin);
            if (!$employee) {
                $skipped++;
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

                // Simpan ke fingerprint_recaps
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
                        // Cek terlambat berdasarkan shift di roster
                        $shift = $schedule->roster?->shift;
                        if ($shift) {
                            $shiftStart = Carbon::parse($date . ' ' . $shift->start_time);
                            $actualIn   = Carbon::parse($date . ' ' . $timeIn);
                            // Toleransi 15 menit
                            $newStatus  = $actualIn->gt($shiftStart->addMinutes(15))
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
        ]);
    }

    /**
     * DataTables — tampilkan data fingerprint_recaps
     * Relasi: fingerprint_recap → schedule → roster → shift (Pagi/Siang/Malam)
     */
    public function getData(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->toDateString());
        $storeName = $request->input('store_name');

        $query = FingerprintRecap::with([
            'employee:id,employee_name,employee_pengenal,pin,store_id,position_id,status_employee',
            'employee.store:id,name',
            'employee.position:id,name',
            // schedule → roster → shift (Pagi/Siang/Malam)
            'schedule.roster.shift:id,shift_name,start_time,end_time',
        ])
        ->whereBetween('date', [$startDate, $endDate]);

        if ($storeName) {
            $query->whereHas('employee.store', fn($q) => $q->where('name', $storeName));
        }

        return DataTables::of($query)
            ->addColumn('location',        fn($r) => $r->employee?->store?->name ?? '-')
            ->addColumn('employee_name',   fn($r) => $r->employee?->employee_name ?? '-')
            ->addColumn('nip',             fn($r) => $r->employee?->employee_pengenal ?? '-')
            ->addColumn('position',        fn($r) => $r->employee?->position?->name ?? '-')
            ->addColumn('status_employee', fn($r) => $r->employee?->status_employee ?? '-')

            // Roster → nama shift (Pagi/Siang/Malam) dari schedule → roster → shift
            ->addColumn('roster', function ($r) {
                $schedule = $r->schedule;
                if (!$schedule) return '-';
                if ($schedule->day_type !== 'Work') return $schedule->day_type;
                return $schedule->roster?->shift?->shift_name ?? 'Work';
            })

            // Jam shift misal 08:00-16:00
            ->addColumn('roster_time', function ($r) {
                $shift = $r->schedule?->roster?->shift;
                if (!$shift) return '';
                return substr($shift->start_time, 0, 5) . '-' . substr($shift->end_time, 0, 5);
            })

            // Status kehadiran dari schedules (Attended/Late/Absent/Scheduled)
            ->addColumn('attendance_status', function ($r) {
                $status = $r->schedule?->status ?? '-';
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