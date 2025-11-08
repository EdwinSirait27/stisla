<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Submissionposition;
use App\Models\Stores;
use App\Models\Position;
use Illuminate\Support\Facades\DB;

class PositionapprovalController extends Controller
{
    public function index()
    {
        return view('pages.PositionApproval.PositionApproval');
    }
    public function getPositionapprovals()
    {
        $positions = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store'])
            ->select(['id', 'employee_id', 'status', 'position_id', 'store_id'])
            ->whereIn('status', ['Approved HR','Reject','Accepted','Done'])
            ->get()
            ->map(function ($position) {
                if ($position->status === 'Approved HR') {
                    $position->status = 'Draft';
                }
                if ($position->status === 'On Review HR') {
                    $position->status = 'Reject';
                }
                $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
                $lockedStatuses = ['On Review HR', 'Done', 'Accepted'];
                $showButton = '
        <a href="' . route('PositionApproval.show', $position->id_hashed) . '" 
           class="mx-2" 
           data-bs-toggle="tooltip" 
           data-bs-original-title="View details" 
           title="Show Position Request: ' . e($position->positionRelation->name) . '">
            <i class="fas fa-eye "></i>
        </a>';
                if (in_array($position->status, $lockedStatuses)) {
                    $editButton = '
            <i class="fas fa-lock text-muted mx-2" 
               data-bs-toggle="tooltip" 
               title="Edit locked because status: ' . e($position->status) . '"></i>';
                } else {
                    $editButton = '
            <a href="' . route('PositionApproval.edit', $position->id_hashed) . '" 
               class="mx-2" 
               data-bs-toggle="tooltip" 
               data-bs-original-title="Edit request" 
               title="Edit Positionrequest: ' . e($position->positionRelation->name) . '">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>';
                }
                $position->action = $showButton . $editButton;
                return $position;
            });
        return DataTables::of($positions)
            ->addColumn('sub', fn($e) => optional($e->submitter)->employee_name ?? 'Empty')
            ->addColumn('position_name', fn($e) => optional($e->positionRelation)->name ?? 'Pending Approval')
            ->addColumn('store_name', fn($e) => optional($e->store)->name ?? 'Pending Approval')
            ->addColumn('remark', function ($e) {
                return match ($e->status) {
                    'Draft' => ' Please check this application',
                    'On Review DIR' => ' This application is being checked by you',
                    'Accepted' => ' This application is accepted by you',
                    'Reject' => ' This application is rejected by you',
                    'Done' => ' This application has entered into the structure',
                    default => '-',
                };
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $position = Submissionposition::with('submitter', 'approver1', 'approver2', 'positionRelation', 'store')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$position) {
            abort(404, 'Position not found.');
        }
        if ($position->status === 'Approved HR') {
            $position->status = 'Draft';
        }
        if ($position->status === 'Reject') {
            $position->status = 'On Review HR';
        }
        $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote'];
        $statuses = ['Reject', 'Draft','Accepted'];
        return view('pages.PositionApproval.edit', [
            'position' => $position,
            'statuses' => $statuses,
            'types' => $types,
            'hashedId' => $hashedId
        ]);
    }
    public function show($hashedId)
    {
        $submission = Submissionposition::with('submitter', 'approver1', 'approver2', 'positionRelation', 'store')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$submission) {
            abort(404, 'submission not found.');
        }
        if ($submission->status === 'Approved HR') {
            $submission->status = 'Draft';
        }
        $badgeColors = [
            'Accepted'   => 'success',
            'On Review HR '   => 'info',
            'Approved HR '   => 'success',
            'Draft'      => 'secondary',
            'Reject'      => 'danger',
            'Done'      => 'success',
        ];
        $submission->type_badges = collect(
            is_array($submission->status) ? $submission->status : explode(',', $submission->status)
        )->map(function ($t) use ($badgeColors) {
            $t = trim($t);
            return [
                'name' => $t,
                'color' => $badgeColors[$t] ?? 'success',
            ];
        });
        $stores = Stores::get()->pluck('name', 'id');
        $positions = Position::get()->pluck('name', 'id');
        $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote'];
        return view('pages.PositionApproval.show', [
            'submission' => $submission,
            'positions' => $positions,
            'stores' => $stores,
            'types' => $types,
            'hashedId' => $hashedId
        ]);
    }
    public function update(Request $request, $hashedId)
    {
        $position = Submissionposition::with('submitter', 'approver1', 'approver2')
            ->get()
            ->first(function ($u) use ($hashedId) {
                $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
                return $expectedHash === $hashedId;
            });

        if (!$position) {
            return redirect()->route('pages.PositionApproval')->with('error', 'ID tidak valid.');
        }

        $validatedData = $request->validate([
            'status'        => ['required', 'string', 'max:255'],
            'salary_counter'        => ['required', 'regex:/^[0-9]+$/'],
            'salary_counter_end'        => ['required', 'regex:/^[0-9]+$/'],
            'notes_dir' => ['nullable', 'string', 'max:255'],
        ], [
            'status.required' => 'Status must be filled.',
        ]);
        $positionData = [
            'status'        => $validatedData['status'],
            'salary_counter'        => $validatedData['salary_counter'],
            'salary_counter_end'        => $validatedData['salary_counter_end'],
            'notes_dir' => $validatedData['notes_dir'] ?? null,
        ];
        if (in_array($validatedData['status'], ['On Review HR', 'Accepted', 'Reject'])) {
            $positionData['approver_2'] = auth()->user()->employee_id;
        }
        DB::beginTransaction();
        try {
            $position->update($positionData);
            DB::commit();
            return redirect()->route('pages.PositionApproval')->with('success', 'Position Request updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update Position Request: ' . $e->getMessage());
        }
    }
}
