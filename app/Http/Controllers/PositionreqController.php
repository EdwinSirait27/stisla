<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Submissionposition;
use App\Models\Stores;
use App\Models\User;
use App\Models\Position;
use App\Rules\NoXSSInput;
use App\Mail\Sendpositionrequesttodir;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
class PositionreqController extends Controller
{
    public function index()
    {

        return view('pages.Positionreqlist.Positionreqlist');
    }
    public function getPositionreqlists()
    {

        $positions = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store','company','department'])
            ->select(['id', 'employee_id', 'status', 'position_id', 'store_id','company_id','department_id'])
            ->whereIn('status', ['Sent', 'On Review HR', 'Approved HR', 'Accepted', 'Done', 'Reject'])
            ->get()
            ->map(function ($position) {
                // manipulasi status di sini
                if ($position->status === 'Sent') {
                    $position->status = 'Draft';
                }
                $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
                $lockedStatuses = ['Done', 'Accepted', 'Approved HR', 'Reject'];
                $showButton = '
        <a href="' . route('Positionreqlist.show', $position->id_hashed) . '" 
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
            <a href="' . route('Positionreqlist.edit', $position->id_hashed) . '" 
               class="mx-2" 
               data-bs-toggle="tooltip" 
               data-bs-original-title="Edit request" 
               title="Edit Positionrequest: ' . e($position->positionRelation->name) . '">
                <i class="fas fa-user-edit "></i>
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
                    'On Review HR' => ' This application is being checked by you',
                    'Approved HR' => ' This application is approved by you, waiting directors confirmation',
                    'Reject' => ' This application is rejected by directors, see details on show button',
                    'Accepted' => ' This application is approved by directors',
                    'Done' => ' This application has entered into the structure',
                    default => '-',
                };
            })
            ->rawColumns(['action'])
            ->make(true);
    }
      public function getReqactivities(Request $request)
      {
        if ($request->ajax()) {
            $query = Activity::where('log_name', 'Submissionposition')
                ->with(['causer.employee'])
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('description', function ($row) {
                    return $row->description ?? '-';
                })
                ->addColumn('causer', function ($row) {
                    return $row->causer->employee->employee_name;
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y H:i');
                })
                ->addColumn('changes', function ($row) {
                    return json_encode($row->properties['attributes'] ?? []);
                })

                // 🔍 Tambahkan bagian filter untuk search
                ->filter(function ($instance) use ($request) {
                    if ($request->has('search') && $request->get('search')['value'] != '') {
                        $search = $request->get('search')['value'];

                        $instance->where(function ($q) use ($search) {
                            $q->where('description', 'like', "%{$search}%")
                                ->orWhereHas('causer.employee', function ($q2) use ($search) {
                                    $q2->where('employee_name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('causer.employee', function ($q3) use ($search) {
                                    $q3->where('employee_name', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['description'])
                ->make(true);
        }
    }
    
    /**
     * Format nilai untuk ditampilkan dengan lebih readable
     */
    private function formatActivityValue($field, $value)
    {
        if (is_null($value) || $value === '') {
            return '<em class="text-muted">(kosong)</em>';
        }
        
        // Format untuk relasi
        switch ($field) {
            case 'employee_id':
            case 'approver_1':
            case 'approver_2':
                $employee = \App\Models\Employee::find($value);
                return $employee ? $employee->employee_name : $value;
                
            case 'store_id':
                $store = \App\Models\Stores::find($value);
                return $store ? $store->name : $value;
                
            case 'position_id':
                $position = \App\Models\Position::find($value);
                return $position ? $position->name : $value;
                
            case 'salary_hr':
            case 'salary_hr_end':
            case 'salary_counter':
            case 'salary_counter_end':
                return 'Rp ' . number_format($value, 0, ',', '.');
            
            case 'status':
                // Tambahkan badge untuk status jika perlu
                $statusLabels = [
                    'pending' => '<span class="badge bg-warning">Pending</span>',
                    'approved' => '<span class="badge bg-success">Approved</span>',
                    'rejected' => '<span class="badge bg-danger">Rejected</span>',
                ];
                return $statusLabels[$value] ?? $value;
                
            default:
                // Potong text yang terlalu panjang
                if (strlen($value) > 100) {
                    return substr($value, 0, 100) . '...';
                }
                return $value;
        }
    }
    
    /**
     * Method untuk menampilkan detail activity log (opsional)
     */
    public function showActivityDetail($id)
    {
        $activity = Activity::with(['causer.employee', 'subject'])
            ->findOrFail($id);
        
        $changes = [];
        $labels = Submissionposition::getFieldLabels();
        
        if ($activity->event === 'updated') {
            $attributes = $activity->properties->get('attributes', []);
            $old = $activity->properties->get('old', []);
            
            foreach ($attributes as $key => $newValue) {
                if (isset($old[$key]) && $old[$key] != $newValue) {
                    $changes[] = [
                        'field' => $labels[$key] ?? $key,
                        'field_key' => $key,
                        'old_value' => $this->formatActivityValue($key, $old[$key]),
                        'new_value' => $this->formatActivityValue($key, $newValue),
                        'old_raw' => $old[$key],
                        'new_raw' => $newValue,
                    ];
                }
            }
        }
        
        return response()->json([
            'activity' => $activity,
            'changes' => $changes,
        ]);
    }
    // public function getPositionreqlists()
    // {

    //     $positions = Submissionposition::with(['submitter', 'approver1', 'approver2', 'positionRelation', 'store'])
    //         ->select(['id', 'employee_id', 'approver_1', 'approver_2', 'status', 'position_id', 'store_id'])
    //         ->get()
    //         ->map(function ($position) {
    //             $position->id_hashed = substr(hash('sha256', $position->id . env('APP_KEY')), 0, 8);
    //             $lockedStatuses = ['On review', 'Accepted'];
    //             $showButton = '
    //             <a href="' . route('Positionreqlist.show', $position->id_hashed) . '" 
    //                class="mx-2" 
    //                data-bs-toggle="tooltip" 
    //                data-bs-original-title="View details" 
    //                title="Show Position Request: ' . e($position->positionRelation->name) . '">
    //                 <i class="fas fa-eye "></i>
    //             </a>';
    //             if (in_array($position->status, $lockedStatuses)) {
    //                 $editButton = '
    //                 <i class="fas fa-lock text-muted mx-2" 
    //                    data-bs-toggle="tooltip" 
    //                    title="Edit locked because status: ' . e($position->status) . '"></i>';
    //             } else {
    //                 $editButton = '
    //                 <a href="' . route('Positionreqlist.edit', $position->id_hashed) . '" 
    //                    class="mx-2" 
    //                    data-bs-toggle="tooltip" 
    //                    data-bs-original-title="Edit request" 
    //                    title="Edit Positionrequest: ' . e($position->positionRelation->name) . '">
    //                     <i class="fas fa-user-edit text-secondary"></i>
    //                 </a>';
    //             }
    //             $position->action = $showButton . $editButton;
    //             return $position;
    //         });
    //     return DataTables::of($positions)
    //         ->addColumn('sub', fn($e) => optional($e->submitter)->employee_name ?? 'Empty')
    //         ->addColumn('position_name', fn($e) => optional($e->positionRelation)->name ?? 'Pending Approval')
    //         ->addColumn('store_name', fn($e) => optional($e->store)->name ?? 'Pending Approval')
    //         ->addColumn('approver1', fn($e) => optional($e->approver1)->employee_name ?? 'Pending Approval')
    //         ->addColumn('approver2', fn($e) => optional($e->approver2)->employee_name ?? 'Pending Approval')
    //         ->addColumn('remark', function ($e) {
    //             return match ($e->status) {
    //                 'Draft' => ' Please check this application',
    //                 default => '-',
    //             };
    //         })
    //         ->rawColumns(['action'])
    //         ->make(true);
    // }

    public function edit($hashedId)
    {
        $position = Submissionposition::with('submitter', 'approver1', 'approver2', 'positionRelation', 'store')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$position) {
            abort(404, 'Position not found.');
        }
        $types = ['Full Time', 'Part Time', 'Contract', 'Internship', 'Remote'];
        $statuses = ['On Review HR', 'Reject', 'Draft', 'Approved HR'];
        return view('pages.Positionreqlist.edit', [
            'position' => $position,
            'statuses' => $statuses,
            'types' => $types,
            'hashedId' => $hashedId
        ]);
    }
    public function show($hashedId)
    {
        $submission = Submissionposition::with('submitter', 'approver1', 'approver2', 'positionRelation', 'store','company','department')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$submission) {
            abort(404, 'submission not found.');
        }
        if ($submission->status === 'Sent') {
            $submission->status = 'Draft';
        }
        $badgeColors = [
            'Accepted'   => 'success',
            'On Review HR'   => 'info',
            'Approved HR'   => 'success',
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
        return view('pages.Positionreqlist.show', [
            'submission' => $submission,
            'positions' => $positions,
            'stores' => $stores,
            'types' => $types,
            'hashedId' => $hashedId
        ]);
    }
//     public function update(Request $request, $hashedId)
//     {
//         $position = Submissionposition::with('submitter', 'approver1', 'approver2')
//             ->get()
//             ->first(function ($u) use ($hashedId) {
//                 $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
//                 return $expectedHash === $hashedId;
//             });

//         if (!$position) {
//             return redirect()->route('pages.Position')->with('error', 'ID tidak valid.');
//         }

//         $validatedData = $request->validate([
//             'status'        => ['required', 'string', 'max:255'],
//             'notes_hr'        => ['required', 'string'],
//             'type'        => ['required'],
//             'salary_hr'        => ['required', 'regex:/^[0-9]+$/'],
//             'salary_hr_end'        => ['required', 'regex:/^[0-9]+$/'],
//             'reason_reject' => ['nullable', 'string', 'max:255'],
//             'reason_reject_dir' => ['nullable', 'string'],
//         ], [
//             'status.required' => 'Status must be filled.',
//         ]);
//         $positionData = [
//             'status'        => $validatedData['status'],
//             'salary_hr'        => $validatedData['salary_hr'],
//             'salary_hr_end'        => $validatedData['salary_hr_end'],
//             'type'          => is_array($validatedData['type'])
//                 ? implode(',', $validatedData['type'])
//                 : $validatedData['type'],

//             'notes_hr' => $validatedData['notes_hr'] ?? null,
//             'reason_reject' => $validatedData['reason_reject'] ?? null,
//         ];
//         if (in_array($validatedData['status'], ['On Review HR', 'Reviewed HR', 'Reject'])) {
//             $positionData['approver_1'] = auth()->user()->employee_id;
//         }
//         DB::beginTransaction();
//         try {
//             $position->update($positionData);
//             DB::commit();
//             if ($validatedData['status'] === 'Approved HR') {
//     $directorUsers = User::role('Director')
//         ->whereHas('employee', function ($query) {
//             $query->where('status', 'Active');
//         })
//         ->with('employee')
//         ->get();

//     foreach ($directorUsers as $director) {
//         if ($director->employee && $director->employee->email) {
//             Mail::to($director->employee->email)->send(new Sendpositionrequesttodir($position));
//         }
//     }
// }
//             return redirect()->route('pages.Positionreqlist')->with('success', 'Position Request updated successfully.');
//         } catch (\Exception $e) {
//             DB::rollBack();
//             return redirect()->back()->with('error', 'Failed to update Position Request: ' . $e->getMessage());
//         }
//     }
public function update(Request $request, $hashedId)
{
    $position = Submissionposition::all()->first(function ($u) use ($hashedId) {
        $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
        return $expectedHash === $hashedId;
    });

    if (!$position) {
        return redirect()->route('pages.Position')->with('error', 'ID tidak valid.');
    }

    $validatedData = $request->validate([
        'status'        => ['required', 'string', 'max:255'],
        'notes_hr'      => ['required', 'string'],
        'type'          => ['required'],
        'salary_hr'     => ['required', 'regex:/^[0-9]+$/'],
        'salary_hr_end' => ['required', 'regex:/^[0-9]+$/'],
        'reason_reject' => ['nullable', 'string', 'max:255'],
        'reason_reject_dir' => ['nullable', 'string'],
    ]);

    $position->status = $validatedData['status'];
    $position->salary_hr = $validatedData['salary_hr'];
    $position->salary_hr_end = $validatedData['salary_hr_end'];
    $position->type = is_array($validatedData['type'])
        ? implode(',', $validatedData['type'])
        : $validatedData['type'];
    $position->notes_hr = $validatedData['notes_hr'] ?? null;
    $position->reason_reject = $validatedData['reason_reject'] ?? null;

    if (in_array($validatedData['status'], ['On Review HR','Reject','Approved HR'])) {
        $position->approver_1 = auth()->user()->employee_id;
    }

    DB::beginTransaction();
    try {
        $position->save(); // ✅ ini memicu Spatie log dengan old/new
        DB::commit();

        // kirim email kalau Approved HR
        if ($validatedData['status'] === 'Approved HR') {
            $directorUsers = User::role('Director')
                ->whereHas('employee', fn($q) => $q->where('status', 'Active'))
                ->with('employee')
                ->get();

            foreach ($directorUsers as $director) {
                if ($director->employee && $director->employee->email) {
                    Mail::to($director->employee->email)->send(new Sendpositionrequesttodir($position));
                }
            }
        }

        return redirect()->route('pages.Positionreqlist')->with('success', 'Position Request updated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Failed to update Position Request: ' . $e->getMessage());
    }
}

}
