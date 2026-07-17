<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\Stores;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeSalaryImport;
use App\Exports\EmployeeSalaryExport;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Grading;
use Yajra\DataTables\Facades\DataTables;

class AttendanceMobileController extends Controller
{
    public function index()
    {
         $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManageAttendanceMobile')) {
            abort(403, 'Unauthorized');
        }
        $stores    = Stores::select('id', 'name')->orderBy('name')->get();
    $employees = Employee::select('id', 'employee_name')->orderBy('employee_name')->get();

        return view('pages.AttendanceMobile.index',compact('stores','employees'));
    }
    public function getAttendanceMobiles(Request $request)
    {
        $query = AttendanceLog::with(['employee', 'store'])
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

        // Filter by date range
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('attendance_logs.work_date', [
                $request->date_from,
                $request->date_to,
            ]);
        }

        // Filter by store
        if ($request->filled('store_id')) {
            $query->where('attendance_logs.store_id', $request->store_id);
        }

        // Filter by type (check_in / check_out)
        if ($request->filled('type')) {
            $query->where('attendance_logs.type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('attendance_logs.status', $request->status);
        }

        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('attendance_logs.employee_id', $request->employee_id);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn(
                'employee_name',
                fn($log) =>
                $log->employee?->employee_name ?? '-'
            )
            ->addColumn(
                'store_name',
                fn($log) =>
                $log->store?->name ?? 'Work From Anywhere'
            )
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
                return '
                <a href="' . route('attendancemobile.show', $idHashed) . '"
                   class="btn btn-sm btn-info"
                   title="Detail">
                    <i class="fas fa-eye"></i>
                </a>';
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
            ->make(true);
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
