<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Roster;
use App\Models\Shifts;
use App\Models\Stores;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RosterController extends Controller
{
    public function index(Request $request)
    {
        $stores    = Stores::select('id', 'name')->whereNotNull('name')->orderBy('name')->get();
        $startDate = $request->start_date ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate   = $request->end_date   ?? Carbon::now()->endOfMonth()->toDateString();
        $storeId   = $request->store_id;

        // ── Wajib pilih Store dulu ──
        $employees = collect();
        $shifts    = collect();
        $dates     = [];

        if ($storeId) {
            $employees = Employee::with([
                'position:id,name',
                'store:id,name',
                'rosters' => function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                      ->with('shift:id,shift_name,start_time,end_time');
                },
            ])
            ->whereNull('deleted_at')
            ->where('store_id', $storeId)
            ->orderBy('employee_name')
            ->get();

            $shifts = Shifts::where('store_id', $storeId)
                ->orderBy('shift_name')->get();

            $current = Carbon::parse($startDate);
            while ($current->lte(Carbon::parse($endDate))) {
                $dates[] = $current->copy();
                $current->addDay();
            }
        }

        return view('pages.Roster.Roster', compact(
            'employees', 'shifts', 'stores', 'dates', 'startDate', 'endDate', 'storeId'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'shift_id'    => 'nullable|exists:shifts_tables,id',
            'date'        => 'required|date',
            'day_type'    => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan'
        ]);

        $roster = Roster::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
                'day_type' => $request->day_type,
                'notes'    => $request->notes,
            ]
        );

        return response()->json([
            'success'     => true,
            'roster'      => $roster->load('shift'),
            'roster_name' => $roster->shift?->shift_name ?? $request->day_type,
            'roster_time' => $roster->shift
                ? substr($roster->shift->start_time, 0, 5) . '-' . substr($roster->shift->end_time, 0, 5)
                : '',
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees_tables,id',
            'date'        => 'required|date',
        ]);

        Roster::where('employee_id', $request->employee_id)
            ->where('date', $request->date)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function bulkAssign(Request $request)
    {
        $request->validate([
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees_tables,id',
            'shift_id'       => 'nullable|exists:shifts_tables,id',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'day_type'       => 'required|in:Work,Off,Public Holiday,Leave,Cuti Melahirkan',
            'skip_weekend'   => 'boolean',
        ]);

        $dates   = [];
        $current = Carbon::parse($request->start_date);
        $end     = Carbon::parse($request->end_date);

        while ($current->lte($end)) {
            if ($request->skip_weekend && $current->isWeekend()) {
                $current->addDay();
                continue;
            }
            $dates[] = $current->toDateString();
            $current->addDay();
        }

        $count = 0;
        foreach ($request->employee_ids as $empId) {
            foreach ($dates as $date) {
                Roster::updateOrCreate(
                    ['employee_id' => $empId, 'date' => $date],
                    [
                        'shift_id' => $request->day_type === 'Work' ? $request->shift_id : null,
                        'day_type' => $request->day_type,
                    ]
                );
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil assign {$count} jadwal.",
        ]);
    }

    public function copyRoster(Request $request)
    {
        $request->validate([
            'store_id'     => 'nullable|exists:stores_tables,id',
            'source_start' => 'required|date',
            'source_end'   => 'required|date|after_or_equal:source_start',
            'target_start' => 'required|date',
        ]);

        $diffDays = Carbon::parse($request->source_start)
            ->diffInDays(Carbon::parse($request->target_start));

        $sourceRosters = Roster::whereBetween('date', [
            $request->source_start, $request->source_end
        ])
        ->when($request->store_id, fn($q) =>
            $q->whereHas('employee', fn($eq) => $eq->where('store_id', $request->store_id))
        )
        ->get();

        $count = 0;
        foreach ($sourceRosters as $src) {
            $newDate = Carbon::parse($src->date)->addDays($diffDays)->toDateString();
            Roster::updateOrCreate(
                ['employee_id' => $src->employee_id, 'date' => $newDate],
                ['shift_id' => $src->shift_id, 'day_type' => $src->day_type]
            );
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil copy {$count} jadwal.",
        ]);
    }
}