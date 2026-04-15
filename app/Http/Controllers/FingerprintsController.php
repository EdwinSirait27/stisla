<?php

namespace App\Http\Controllers;

use App\Models\Fingerprints;
use App\Models\EditedFingerprint;
use App\Models\Employee;
use App\Models\Stores;
use App\Models\Schedule;
use App\Models\Devicefingerprint;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
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

    public function getFingerprints(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(300);

        $storeName = $request->input('store_name');
        $startDate = Carbon::parse($request->input('start_date', now()->startOfMonth()))->startOfDay();
        $endDate   = Carbon::parse($request->input('end_date', now()))->endOfDay();

        // ── 1. Edited fingerprint keys (lightweight) ──
        $editedKeys = EditedFingerprint::whereBetween('scan_date', [$startDate, $endDate])
            ->pluck('scan_date', 'pin')
            ->map(fn($date, $pin) => $pin . '_' . Carbon::parse($date)->toDateString())
            ->values()
            ->toArray();

        // ── 2. Ambil Employees (hanya kolom yang diperlukan) ──
        $employeesQuery = Employee::with([
            'position:id,name',
            'store:id,name',
        ])
        ->select('id', 'pin', 'employee_name', 'employee_pengenal', 'position_id', 'store_id', 'status_employee')
        ->whereNotNull('pin');

        if ($storeName) {
            $employeesQuery->whereHas('store', fn($q) => $q->where('name', $storeName));
        }

        $employees    = $employeesQuery->get()->keyBy('pin');
        $employeeIds  = $employees->pluck('id')->filter()->values()->toArray();

        // ── 3. Ambil Schedules dalam rentang tanggal ──
        // Relasi: schedule → roster → shift (Pagi/Siang/Malam)
        // Gunakan 1 query saja, key by "employee_id_date"
        $schedules = Schedule::with('roster.shift:id,shift_name,start_time,end_time')
            ->select('id', 'employee_id', 'roster_id', 'date', 'day_type', 'status')
            ->whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn($s) => $s->employee_id . '_' . Carbon::parse($s->date)->toDateString());

        // ── 4. Ambil Fingerprints - selalu filter by PIN aktif ──
        $pins = $employees->keys()->toArray();

        $fingerprints = Fingerprints::select(['sn', 'scan_date', 'pin', 'inoutmode'])
            ->whereIn('pin', $pins)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->orderBy('pin')
            ->orderBy('scan_date')
            ->get();

        // ── 5. Load device names dari tabel devicefingerprints langsung ──
        $deviceNames = Devicefingerprint::select('sn', 'device_name')
            ->get()
            ->keyBy('sn')
            ->map(fn($d) => $d->device_name ?? '-');

        // ── 6. Hitung total hari per PIN ──
        $totalHariPerPin = $fingerprints
            ->groupBy('pin')
            ->map(fn($items) =>
                $items->pluck('scan_date')
                    ->map(fn($d) => Carbon::parse($d)->toDateString())
                    ->unique()
                    ->count()
            );

        // ── 7. Group per PIN per tanggal ──
        $grouped = $fingerprints
            ->groupBy(fn($f) => $f->pin . '_' . Carbon::parse($f->scan_date)->toDateString());

        // ── 8. Build result ──
        $result = $grouped->map(function ($group, $key) use (
            $employees, $totalHariPerPin, $editedKeys, $schedules, $deviceNames
        ) {
            $first    = $group->first();
            $pin      = $first->pin;
            $scanDate = Carbon::parse($first->scan_date)->toDateString();
            $employee = $employees->get($pin);

            if (!$employee) return null;

            // Ambil roster dari schedule → roster → shift
            $scheduleKey = $employee->id . '_' . $scanDate;
            $schedule    = $schedules->get($scheduleKey);
            $rosterName  = '-';
            $rosterTime  = '';

            if ($schedule) {
                if ($schedule->day_type !== 'Work') {
                    $rosterName = $schedule->day_type; // Off / Holiday / Leave
                } elseif ($schedule->roster?->shift) {
                    $rosterName = $schedule->roster->shift->shift_name;
                    $rosterTime = substr($schedule->roster->shift->start_time, 0, 5)
                        . ' - '
                        . substr($schedule->roster->shift->end_time, 0, 5);
                }
            }

            $row = [
                'pin'               => $pin,
                'employee_name'     => $employee->employee_name ?? '-',
                'status_employee'   => $employee->status_employee ?? '-',
                'employee_pengenal' => $employee->employee_pengenal ?? '-',
                'name'              => $employee->store->name ?? '-',
                'position_name'     => $employee->position->name ?? '-',
                'device_name'       => $deviceNames->get($first->sn) ?? '-',
                'scan_date'         => $scanDate,
                'total_hari'        => $totalHariPerPin[$pin] ?? 0,
                'roster_name'       => $rosterName,
                'roster_time'       => $rosterTime,
            ];

            for ($i = 1; $i <= 10; $i++) {
                $row["in_$i"] = $row["device_$i"] = $row["combine_$i"] = null;
            }

            $group->groupBy('inoutmode')->each(function ($items, $mode) use (&$row, $deviceNames) {
                if ($mode < 1 || $mode > 10) return;

                $sorted = $items->sortBy('scan_date');

                $times = $sorted->pluck('scan_date')
                    ->map(fn($d) => Carbon::parse($d)->format('H:i:s'))
                    ->implode(', ');

                $devices = $sorted
                    ->map(fn($i) => $deviceNames->get($i->sn) ?? '')
                    ->implode(', ');

                $row["in_$mode"]      = $times;
                $row["device_$mode"]  = $devices;
                $row["combine_$mode"] = trim($times . ' ' . $devices);
            });

            // Hitung durasi
            $times = collect(range(1, 10))
                ->flatMap(function ($i) use ($row) {
                    if (!$row["in_$i"]) return [];
                    return explode(', ', $row["in_$i"]);
                })
                ->map(fn($t) => Carbon::parse($t))
                ->sort()
                ->values();

            if ($times->count() >= 2) {
                $start   = $times->first();
                $end     = $times->last();
                $minutes = $start->diffInMinutes($end);

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

        return DataTables::of($result)
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
            ->rawColumns(['action'])
            ->make(true);
    }

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
                'pin'        => 'required|string',
                'scan_date'  => 'required|date',
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
                ->with('success', 'Fingerprint berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal updateFingerprint', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data.');
        }
    }

    public function getTotalHariBekerja(Request $request)
    {
        $pin       = $request->input('pin');
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());

        $total = Fingerprints::where('pin', $pin)
            ->whereBetween('scan_date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn($item) => Carbon::parse($item->scan_date)->toDateString())
            ->count();

        return response()->json(['total' => $total]);
    }
}