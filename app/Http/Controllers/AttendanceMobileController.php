<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Stores;
use App\Models\AttendanceLog;
use App\Models\Company;
use App\Models\Departments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class AttendanceMobileController extends Controller
{

    // public function index()
    // {
    //     $user = auth()->user();
    //     /** @var \App\Models\User|null $user */

    //     if (
    //         !$user->hasPermissionTo('ManageAttendanceMobile') &&
    //         !$user->hasPermissionTo('ManageAttendanceMobileSPVManager') &&
    //         !$user->hasPermissionTo('ViewAttendanceMobile')
    //     ) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $stores      = collect();
    //     $departments = collect();
    //     $companies   = collect();
    //     $employees   = collect();

    //     if ($user->hasPermissionTo('ManageAttendanceMobile')) {
    //         // Full access - semua filter
    //         $stores      = Stores::select('id', 'name')->orderBy('name')->get();
    //         $departments = Departments::select('id', 'department_name')->orderBy('department_name')->get();
    //         $companies   = Company::select('id', 'name')->orderBy('name')->get();
    //         $employees   = Employee::select('id', 'employee_name')->orderBy('employee_name')->get();
    //     } elseif ($user->hasPermissionTo('ManageAttendanceMobileSPVManager')) {
    //         // Hanya bisa filter store (scoped ke store yang dia manage)
    //         $stores = Stores::whereHas('employees', function ($q) use ($user) {
    //             $q->where('employees_tables.id', $user->employee_id);
    //         })
    //             ->select('id', 'name')
    //             ->orderBy('name')
    //             ->get();
    //     }
       
    //     return view('pages.AttendanceMobile.index', compact('stores', 'employees', 'departments', 'companies'));
    // }
    public function index()
{
    $user = auth()->user();
    /** @var \App\Models\User|null $user */

    $canManage     = $user->hasPermissionTo('ManageAttendanceMobile');
    $canSpvManager = $user->hasPermissionTo('ManageAttendanceMobileSPVManager');
    $canView       = $user->hasPermissionTo('ViewAttendanceMobile');

    if (!$canManage && !$canSpvManager && !$canView) {
        abort(403, 'Unauthorized');
    }

    $stores      = collect();
    $departments = collect();
    $companies   = collect();
    $employees   = collect();

    if ($canManage) {
        $stores      = Stores::select('id', 'name')->orderBy('name')->get();
        $departments = Departments::select('id', 'department_name')->orderBy('department_name')->get();
        $companies   = Company::select('id', 'name')->orderBy('name')->get();
        $employees   = Employee::select('id', 'employee_name')->orderBy('employee_name')->get();

    } elseif ($canSpvManager) {
        $employee      = $user->employee;
        $storeIds      = $employee->store()->pluck('stores_tables.id')->toArray();
        $departmentIds = $employee->department()->pluck('departments_tables.id')->toArray();
        $bawahanIds    = $employee->bawahanList()->pluck('employees_tables.id')->toArray();

        // Store yang di-manage SPV
        $stores = Stores::whereIn('id', $storeIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Employee yang bisa dilihat SPV: di store+dept yang sama ATAU bawahan langsung
        $employeeQuery = Employee::select('id', 'employee_name')->orderBy('employee_name');

        if (empty($storeIds) || empty($departmentIds)) {
            // Tidak punya store/dept, hanya tampilkan bawahan langsung
            if (empty($bawahanIds)) {
                $employees = collect();
            } else {
                $employees = $employeeQuery->whereIn('id', $bawahanIds)->get();
            }
        } else {
            $employees = $employeeQuery->where(function ($q) use ($storeIds, $departmentIds, $bawahanIds) {
                // Kondisi 1: di store + department yang sama dengan SPV
                $q->where(function ($q1) use ($storeIds, $departmentIds) {
                    $q1->whereExists(function ($sq) use ($storeIds) {
                        $sq->select(DB::raw(1))
                            ->from('employee_stores')
                            ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                            ->whereIn('employee_stores.store_id', $storeIds);
                    })
                        ->whereExists(function ($sq) use ($departmentIds) {
                            $sq->select(DB::raw(1))
                                ->from('employee_departments')
                                ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                                ->whereIn('employee_departments.department_id', $departmentIds);
                        });
                });

                // Kondisi 2: bawahan langsung via pivot employee_atasans
                if (!empty($bawahanIds)) {
                    $q->orWhereIn('id', $bawahanIds);
                }
            })->get();
        }

    }
    // ViewAttendanceMobile: semua filter tetap collect() kosong

    return view('pages.AttendanceMobile.index', compact('stores', 'employees', 'departments', 'companies'))
        ->with('canManage', $canManage)
        ->with('canSpvManager', $canSpvManager)
        ->with('canView', $canView);
}

    public function getAttendanceMobiles(Request $request)
    {
        $user = auth()->user();
        /** @var \App\Models\User|null $user */

        $canManage     = $user->hasPermissionTo('ManageAttendanceMobile');
        $canSpvManager = $user->hasPermissionTo('ManageAttendanceMobileSPVManager');
        $canView       = $user->hasPermissionTo('ViewAttendanceMobile');

        if (!$canManage && !$canSpvManager && !$canView) {
            abort(403, 'Unauthorized');
        }

        $query = AttendanceLog::with([
            'employee',
            'store',
            'employee.company',
            'employee.department' => fn($q) => $q->wherePivot('is_primary', true),
        ])
            ->select([
                'attendance_logs.id',
                'attendance_logs.employee_id',
                'attendance_logs.store_id',
                'attendance_logs.type',
                'attendance_logs.latitude',
                'attendance_logs.longitude',
                'attendance_logs.distance_from_store',
                'attendance_logs.is_within_geofence',
                'attendance_logs.is_mock_location',
                'attendance_logs.liveness_score',
                'attendance_logs.liveness_passed',
                'attendance_logs.status',
                'attendance_logs.flag_reason',
                'attendance_logs.logged_at',
                'attendance_logs.work_date',
            ]);

        // ── Scoping berdasarkan permission ──────────────────────────────────────
        if (!$canManage) {
            $employee  = $user->employee;
            $companyId = $employee->company_id;

            if ($canSpvManager) {
                $storeIds      = $employee->store()->pluck('stores_tables.id')->toArray();
                $departmentIds = $employee->department()->pluck('departments_tables.id')->toArray();
                $bawahanIds    = $employee->bawahanList()->pluck('employees_tables.id')->toArray();

                if (empty($storeIds) || empty($departmentIds)) {
                    if (empty($bawahanIds)) {
                        return DataTables::of(collect())->make(true);
                    }
                    $query->whereIn('attendance_logs.employee_id', $bawahanIds);
                } else {
                    $query->where(function ($q) use ($storeIds, $departmentIds, $bawahanIds) {
                        // Kondisi 1: employee di store + department yang sama dengan SPV
                        $q->where(function ($q1) use ($storeIds, $departmentIds) {
                            $q1->whereExists(function ($sq) use ($storeIds) {
                                $sq->select(DB::raw(1))
                                    ->from('employee_stores')
                                    ->whereColumn('employee_stores.employee_id', 'attendance_logs.employee_id')
                                    ->whereIn('employee_stores.store_id', $storeIds);
                            })
                                ->whereExists(function ($sq) use ($departmentIds) {
                                    $sq->select(DB::raw(1))
                                        ->from('employee_departments')
                                        ->whereColumn('employee_departments.employee_id', 'attendance_logs.employee_id')
                                        ->whereIn('employee_departments.department_id', $departmentIds);
                                });
                        });

                        // Kondisi 2: bawahan langsung via pivot employee_atasans
                        if (!empty($bawahanIds)) {
                            $q->orWhereIn('attendance_logs.employee_id', $bawahanIds);
                        }
                    });
                }
            } elseif ($canView) {
                $storeId      = $employee->primaryStore()->first()?->id;
                $departmentId = $employee->primaryDepartment()->first()?->id;

                if (!$storeId || !$departmentId) {
                    return DataTables::of(collect())->make(true);
                }

                $query->whereExists(function ($q) use ($storeId) {
                    $q->select(DB::raw(1))
                        ->from('employee_stores')
                        ->whereColumn('employee_stores.employee_id', 'attendance_logs.employee_id')
                        ->where('employee_stores.store_id', $storeId)
                        ->where('employee_stores.is_primary', true);
                })
                    ->whereExists(function ($q) use ($departmentId) {
                        $q->select(DB::raw(1))
                            ->from('employee_departments')
                            ->whereColumn('employee_departments.employee_id', 'attendance_logs.employee_id')
                            ->where('employee_departments.department_id', $departmentId)
                            ->where('employee_departments.is_primary', true);
                    });
            }
        }

        // ── Filter dari request (hanya berlaku sesuai permission) ───────────────
        // ManageAttendanceMobile: semua filter bisa dipakai
        // SPVManager: hanya filter date_from/date_to, type, status (store/company/dept/employee sudah di-scope di atas)
        // ViewAttendanceMobile: hanya filter date_from/date_to, type, status

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('attendance_logs.work_date', [
                $request->date_from,
                $request->date_to,
            ]);
        }

        if ($request->filled('type')) {
            $query->where('attendance_logs.type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('attendance_logs.status', $request->status);
        }

        // Filter berikut hanya untuk ManageAttendanceMobile
        if ($canManage) {
            if ($request->filled('company_id')) {
                $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('company_id', $request->company_id);
                });
            }

            if ($request->filled('department_id')) {
                $query->whereHas('employee.department', function ($q) use ($request) {
                    $q->where('departments_tables.id', $request->department_id);
                });
            }

            if ($request->filled('store_id')) {
                $query->where('attendance_logs.store_id', $request->store_id);
            }

            if ($request->filled('employee_id')) {
                $query->where('attendance_logs.employee_id', $request->employee_id);
            }
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('employee_name', fn($log) => $log->employee?->employee_name ?? '-')
            ->addColumn('company_name', fn($log) => $log->employee?->company?->name ?? '-')
            ->addColumn('department_name', fn($log) => $log->employee?->department?->first()?->department_name ?? '-')
            ->addColumn('store_name', fn($log) => $log->store?->name ?? 'Work From Anywhere')
            ->addColumn(
                'is_within_geofence_label',
                fn($log) =>
                $log->is_within_geofence
                    ? '<span class="badge badge-success">Within</span>'
                    : '<span class="badge badge-danger">Outside</span>'
            )
            ->addColumn(
                'is_mock_location_label',
                fn($log) =>
                $log->is_mock_location
                    ? '<span class="badge badge-danger">Mock</span>'
                    : '<span class="badge badge-success">Real</span>'
            )
            ->addColumn(
                'liveness_passed_label',
                fn($log) =>
                $log->liveness_passed
                    ? '<span class="badge badge-success">Passed</span>'
                    : '<span class="badge badge-danger">Failed</span>'
            )
            ->addColumn(
                'type_label',
                fn($log) =>
                $log->type === 'checkin'
                    ? '<span class="badge badge-primary">Check In</span>'
                    : '<span class="badge badge-warning">Check Out</span>'
            )
            ->addColumn('status_label', fn($log) => match ($log->status) {
                'approved' => '<span class="badge badge-success">Approved</span>',
                'flagged'  => '<span class="badge badge-danger">Flagged</span>',
                'pending'  => '<span class="badge badge-warning">Pending</span>',
                default    => '<span class="badge badge-secondary">' . e($log->status) . '</span>',
            })
            ->editColumn(
                'logged_at',
                fn($log) =>
                optional($log->logged_at)->timezone('Asia/Makassar')->translatedFormat('d F Y H:i') ?? '-'
            )
            ->editColumn(
                'work_date',
                fn($log) =>
                $log->work_date
                    ? \Carbon\Carbon::parse($log->work_date)->translatedFormat('d F Y')
                    : '-'
            )
            ->addColumn('action', function ($log) {
                $idHashed = substr(hash('sha256', $log->id . config('app.key')), 0, 8);

                $btn = '<a href="' . route('attendancemobile.show', $idHashed) . '"
               class="btn btn-sm btn-info" title="Detail">
                <i class="fas fa-eye"></i>
            </a>';

                return $btn;
            })

            ->rawColumns([
                'action',
                'is_within_geofence_label',
                'is_mock_location_label',
                'liveness_passed_label',
                'type_label',
                'status_label',
            ])
            ->orderColumn('employee_name', function ($query, $order) {
                $query->join('employees_tables', 'employees_tables.id', '=', 'attendance_logs.employee_id')
                    ->orderBy('employees_tables.employee_name', $order);
            })
            ->orderColumn('store_name', function ($query, $order) {
                $query->join('stores_tables', 'stores_tables.id', '=', 'attendance_logs.store_id')
                    ->orderBy('stores_tables.name', $order);
            })
            ->orderColumn('company_name', function ($query, $order) {
                $query->join('employees_tables', 'employees_tables.id', '=', 'attendance_logs.employee_id')
                    ->join('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
                    ->orderBy('company_tables.name', $order);
            })
            ->orderColumn('department_name', function ($query, $order) {
                $query->join('employee_departments', function ($join) {
                    $join->on('employee_departments.employee_id', '=', 'attendance_logs.employee_id')
                        ->where('employee_departments.is_primary', true);
                })
                    ->join('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id')
                    ->orderBy('departments_tables.department_name', $order);
            })
            ->make(true);
    }
    // public function store(Request $request)
    // {
    //     $user = auth()->user();
    //     /** @var \App\Models\User|null $user */

    //     if (
    //         !$user->hasPermissionTo('ManageAttendanceMobile') &&
    //         !$user->hasPermissionTo('ManageAttendanceMobileSPVManager')
    //     ) {
    //         abort(403, 'Unauthorized');
    //     }

    //     $rules = [
    //         'employee_id' => 'required|exists:employees_tables,id',
    //         'store_id'    => 'nullable|exists:stores_tables,id',
    //         'type'        => 'required|in:checkin,checkout',
    //         'work_date'   => 'required|date',
    //         'logged_at'   => 'required|date',
    //         'flag_reason' => 'nullable|string|max:255',
    //         'photo_path'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
    //     ];

    //     if ($user->hasPermissionTo('ManageAttendanceMobile')) {
    //         $rules['status'] = 'required|in:approved,pending';
    //     }

    //     $validated = $request->validate($rules);

    //     $status = $user->hasPermissionTo('ManageAttendanceMobile')
    //         ? $validated['status']
    //         : 'pending';

    //     // Upload foto dulu di luar transaction supaya row lock tidak menahan durasi upload
    //     $photoPath = null;
    //     if ($request->hasFile('photo_path')) {
    //         $file     = $request->file('photo_path');
    //         $filename = 'attendance-manual/' . now()->format('Y/m') . '/' . \Ramsey\Uuid\Uuid::uuid7()->toString() . '.' . $file->getClientOriginalExtension();
    //         $photoPath = $file->storeAs('', $filename, 's3');
    //     }

    //     try {
    //         $log = DB::transaction(function () use ($validated, $status, $photoPath) {
    //             $duplicate = AttendanceLog::where('employee_id', $validated['employee_id'])
    //                 ->where('work_date', $validated['work_date'])
    //                 ->where('type', $validated['type'])
    //                 ->lockForUpdate()
    //                 ->exists();

    //             if ($duplicate) {
    //                 throw new \App\Exceptions\DuplicateAttendanceException(
    //                     'Attendance record untuk employee, tanggal, dan tipe ini sudah ada.'
    //                 );
    //             }
    //             return AttendanceLog::create([
    //                 'employee_id'         => $validated['employee_id'],
    //                 'store_id'            => $validated['store_id'] ?? null,
    //                 'type'                => $validated['type'],
    //                 'work_date'           => $validated['work_date'],
    //                 'logged_at'           => \Carbon\Carbon::parse($validated['logged_at'], 'Asia/Makassar'),
    //                 'status'              => $status,
    //                 'flag_reason'         => $validated['flag_reason']
    //                     ?? 'Manual entry - lupa ' . ($validated['type'] === 'checkin' ? 'check in' : 'check out'),
    //                 'photo_path'          => $photoPath,
    //                 'is_within_geofence'  => true,
    //                 'is_mock_location'    => false,
    //                 'liveness_passed'     => true,
    //                 'liveness_score'      => null,
    //                 'latitude'            => null,
    //                 'longitude'           => null,
    //                 'distance_from_store' => null,
    //             ]);
    //         });
    //     } catch (\App\Exceptions\DuplicateAttendanceException $e) {
    //         if ($photoPath) Storage::disk('s3')->delete($photoPath);
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    //     } catch (\Illuminate\Database\QueryException $e) {
    //         if ($photoPath) Storage::disk('s3')->delete($photoPath);
    //         if ($e->getCode() === '23000') {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Attendance record untuk employee, tanggal, dan tipe ini sudah ada.',
    //             ], 422);
    //         }
    //         throw $e;
    //     }

    //     activity()
    //         ->causedBy($user)
    //         ->performedOn($log)
    //         ->withProperties([
    //             'attributes' => array_merge($validated, [
    //                 'status'     => $status,
    //                 'photo_path' => $photoPath,
    //             ]),
    //         ])
    //         ->log('Manual attendance entry created');

    //     return response()->json([
    //         'success'    => true,
    //         'message'    => 'Attendance record created successfully.',
    //         'photo_path' => $photoPath,
    //     ]);
    // }
    public function store(Request $request)
{
    $user = auth()->user();
    /** @var \App\Models\User|null $user */

    Log::info('AttendanceMobile store: request received', [
        'user_id' => $user?->id,
        'payload' => $request->except('photo_path'),
        'has_photo' => $request->hasFile('photo_path'),
    ]);

    if (
        !$user->hasPermissionTo('ManageAttendanceMobile') &&
        !$user->hasPermissionTo('ManageAttendanceMobileSPVManager')
    ) {
        Log::warning('AttendanceMobile store: unauthorized attempt', [
            'user_id' => $user?->id,
        ]);
        abort(403, 'Unauthorized');
    }

    $rules = [
        'employee_id' => 'required|exists:employees_tables,id',
        'store_id'    => 'nullable|exists:stores_tables,id',
        'type'        => 'required|in:checkin,checkout',
        'work_date'   => 'required|date',
        'logged_at'   => 'required|date',
        'flag_reason' => 'nullable|string|max:255',
        'photo_path'  => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
    ];

    if ($user->hasPermissionTo('ManageAttendanceMobile')) {
        $rules['status'] = 'required|in:approved,pending';
    }

    try {
        $validated = $request->validate($rules);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::warning('AttendanceMobile store: validation failed', [
            'user_id' => $user?->id,
            'errors'  => $e->errors(),
        ]);
        throw $e;
    }

    $status = $user->hasPermissionTo('ManageAttendanceMobile')
        ? $validated['status']
        : 'pending';

    // Upload foto dulu di luar transaction supaya row lock tidak menahan durasi upload
    $photoPath = null;
    if ($request->hasFile('photo_path')) {
        $file     = $request->file('photo_path');
        $filename = 'attendance-manual/' . now()->format('Y/m') . '/' . \Ramsey\Uuid\Uuid::uuid7()->toString() . '.' . $file->getClientOriginalExtension();

        try {
            $photoPath = $file->storeAs('', $filename, 's3');
            Log::info('AttendanceMobile store: photo uploaded', [
                'photo_path' => $photoPath,
                'size'       => $file->getSize(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AttendanceMobile store: photo upload failed', [
                'user_id' => $user?->id,
                'error'   => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'Gagal upload foto/surat pendukung.'], 500);
        }
    }

    try {
        $log = DB::transaction(function () use ($validated, $status, $photoPath) {
            $duplicate = AttendanceLog::where('employee_id', $validated['employee_id'])
                ->where('work_date', $validated['work_date'])
                ->where('type', $validated['type'])
                ->lockForUpdate()
                ->exists();

            if ($duplicate) {
                throw new \App\Exceptions\DuplicateAttendanceException(
                    'Attendance record untuk employee, tanggal, dan tipe ini sudah ada.'
                );
            }
            return AttendanceLog::create([
                'employee_id'         => $validated['employee_id'],
                'store_id'            => $validated['store_id'] ?? null,
                'type'                => $validated['type'],
                'work_date'           => $validated['work_date'],
                'logged_at'           => \Carbon\Carbon::parse($validated['logged_at'], 'Asia/Makassar'),
                'status'              => $status,
                'flag_reason'         => $validated['flag_reason']
                    ?? 'Manual entry - lupa ' . ($validated['type'] === 'checkin' ? 'check in' : 'check out'),
                'photo_path'          => $photoPath,
                'is_within_geofence'  => true,
                'is_mock_location'    => false,
                'liveness_passed'     => true,
                'liveness_score'      => null,
                'latitude'            => null,
                'longitude'           => null,
                'distance_from_store' => null,
            ]);
        });
    } catch (\App\Exceptions\DuplicateAttendanceException $e) {
        Log::warning('AttendanceMobile store: duplicate entry blocked', [
            'user_id'     => $user?->id,
            'employee_id' => $validated['employee_id'],
            'work_date'   => $validated['work_date'],
            'type'        => $validated['type'],
        ]);
        if ($photoPath) Storage::disk('s3')->delete($photoPath);
        return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
    } catch (\Illuminate\Database\QueryException $e) {
        if ($e->getCode() === '23000') {
            Log::warning('AttendanceMobile store: duplicate entry (DB constraint)', [
                'user_id'     => $user?->id,
                'employee_id' => $validated['employee_id'],
            ]);
            if ($photoPath) Storage::disk('s3')->delete($photoPath);
            return response()->json([
                'success' => false,
                'message' => 'Attendance record untuk employee, tanggal, dan tipe ini sudah ada.',
            ], 422);
        }

        Log::error('AttendanceMobile store: query exception', [
            'user_id' => $user?->id,
            'error'   => $e->getMessage(),
        ]);
        if ($photoPath) Storage::disk('s3')->delete($photoPath);
        throw $e;
    } catch (\Throwable $e) {
        Log::error('AttendanceMobile store: unexpected error', [
            'user_id' => $user?->id,
            'error'   => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
        if ($photoPath) Storage::disk('s3')->delete($photoPath);
        throw $e;
    }

    activity()
        ->causedBy($user)
        ->performedOn($log)
        ->withProperties([
            'attributes' => array_merge($validated, [
                'status'     => $status,
                'photo_path' => $photoPath,
            ]),
        ])
        ->log('Manual attendance entry created');

    Log::info('AttendanceMobile store: success', [
        'user_id'   => $user?->id,
        'log_id'    => $log->id,
        'status'    => $status,
    ]);

    return response()->json([
        'success'    => true,
        'message'    => 'Attendance record created successfully.',
        'photo_path' => $photoPath,
    ]);
}
    public function show(string $hash)
    {
        $log = AttendanceLog::with(['employee', 'store'])
            ->get()
            ->first(fn($l) => hash_equals(
                substr(hash('sha256', $l->id . config('app.key')), 0, 8),
                $hash
            ));
        if (!$log) {
            abort(404, 'Attendance log not found');
        }

        // Generate signed URL untuk foto dari MinIO
        $photoUrl = null;
        if ($log->photo_path) {
            try {
                $photoUrl = Storage::disk('s3')->temporaryUrl(
                    $log->photo_path,
                    now()->addMinutes(10)
                );
            } catch (\Throwable $e) {
                Log::warning('ATTENDANCE_PHOTO_URL_FAILED', [
                    'log_id'     => $log->id,
                    'photo_path' => $log->photo_path,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return view('pages.AttendanceMobile.show', compact('log', 'photoUrl'));
    }
}
