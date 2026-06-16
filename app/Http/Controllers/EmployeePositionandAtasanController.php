<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\EmployeePosition;
use App\Models\EmployeeAtasan;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Departments;
use App\Models\Stores;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class EmployeePositionandAtasanController extends Controller
{

public function bulkIndex()
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
 
    if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
    // Data untuk dropdown Select2 Position
    $positions = Position::orderBy('name')->get(['id', 'name']);
    $companies = Company::orderBy('name')->get(['id', 'name']);
    $stores = Stores::orderBy('name')->get(['id', 'name']);
    $departments = Departments::orderBy('department_name')->get(['id', 'department_name']);
 
    $atasanList = Employee::where('status', 'Active')  // sesuaikan value status aktif kamu
        ->orderBy('employee_name')
        ->get(['id', 'employee_name', 'employee_pengenal']);
 
    return view('Pages.Bulk.index', compact('companies','stores','positions', 'atasanList'));
}



    public function getEmployeePositionandAtasans(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
 
    if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
 
    $query = Employee::query()
        ->with([
            // Semua posisi (primary diutamakan di addColumn)
            'position' => fn($q) => $q->orderByPivot('is_primary', 'desc'),
            // Semua atasan (primary diutamakan)
            'atasanList'  => fn($q) => $q->orderByPivot('is_primary', 'desc')
                ->select('employees_tables.id', 'employees_tables.employee_name', 'employees_tables.employee_pengenal'),
        ])
        ->leftJoin('employee_positions', function ($join) {
            $join->on('employee_positions.employee_id', '=', 'employees_tables.id')
                ->where('employee_positions.is_primary', true);
        })
        ->leftJoin('position_tables', 'position_tables.id', '=', 'employee_positions.position_id')

         ->leftJoin('employee_departments', function ($join) {
            $join->on('employee_departments.employee_id', '=', 'employees_tables.id')
                ->where('employee_departments.is_primary', true);
        })
        ->leftJoin('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id')
        // ← Tambah join store primary
        ->leftJoin('employee_stores', function ($join) {
            $join->on('employee_stores.employee_id', '=', 'employees_tables.id')
                ->where('employee_stores.is_primary', true);
        })
        ->leftJoin('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')

        ->leftJoin('company_tables', 'company_tables.id', '=', 'employees_tables.company_id')
        ->select([
            'employees_tables.id',
            'employees_tables.employee_name',
            'employees_tables.employee_pengenal',
            'employees_tables.status',
            'position_tables.name as primary_position_name',
            'company_tables.name as company_name',
            'departments_tables.department_name as primary_department_name', // ←
            'stores_tables.name as primary_store_name',      
        ]);
 
    // Filter opsional dari request
    $query->when($request->filled('filter_company'),
        fn($q) => $q->where('company_tables.name', $request->filter_company));
    $query->when($request->filled('filter_status'),
        fn($q) => $q->where('employees_tables.status', $request->filter_status));
  // Department — whereExists karena pivot (bisa punya banyak department)
    $query->when($request->filled('filter_department'), function ($q) use ($request) {
        $q->whereExists(function ($sub) use ($request) {
            $sub->select(DB::raw(1))
                ->from('employee_departments')
                ->join('departments_tables', 'departments_tables.id', '=', 'employee_departments.department_id')
                ->whereColumn('employee_departments.employee_id', 'employees_tables.id')
                ->where('departments_tables.department_name', $request->filter_department);
        });
    });

    // Store — sama, whereExists
    $query->when($request->filled('filter_store'), function ($q) use ($request) {
        $q->whereExists(function ($sub) use ($request) {
            $sub->select(DB::raw(1))
                ->from('employee_stores')
                ->join('stores_tables', 'stores_tables.id', '=', 'employee_stores.store_id')
                ->whereColumn('employee_stores.employee_id', 'employees_tables.id')
                ->where('stores_tables.name', $request->filter_store);
        });
    });
    return DataTables::of($query)
        ->addColumn('checkbox', fn($e) =>
            '<input type="checkbox" class="employee-checkbox" value="' . $e->id . '">'
        )
        ->addColumn('all_positions', function ($e) {
            return $e->position->map(fn($p) =>
                ($p->pivot->is_primary ? '<span class="badge badge-primary">' : '<span class="badge badge-secondary">')
                . e($p->name) . '</span>'
            )->implode(' ');
        })
        ->addColumn('primary_atasan', function ($e) {
            $atasan = $e->atasanList?->first(); // sudah di-order is_primary desc
            return $atasan
                ? e($atasan->employee_name) . ' <small class="text-muted">(' . e($atasan->employee_pengenal) . ')</small>'
                : '<span class="text-muted">—</span>';
        })
        ->filterColumn('employee_name',
            fn($q, $k) => $q->where('employees_tables.employee_name', 'like', "%$k%"))
        ->filterColumn('employee_pengenal',
            fn($q, $k) => $q->where('employees_tables.employee_pengenal', 'like', "%$k%"))
        ->filterColumn('primary_position_name',
            fn($q, $k) => $q->where('position_tables.name', 'like', "%$k%"))
        ->filterColumn('company_name',
            fn($q, $k) => $q->where('company_tables.name', 'like', "%$k%"))
            ->filterColumn('primary_department_name',
            fn($q, $k) => $q->where('departments_tables.department_name', 'like', "%$k%"))
        ->filterColumn('primary_store_name',
            fn($q, $k) => $q->where('stores_tables.name', 'like', "%$k%"))
        ->rawColumns(['checkbox', 'all_positions', 'primary_atasan'])
        ->make(true);
}
 
// ──────────────────────────────────────────────────────────────
// 2. BULK ASSIGN POSITION
//    POST /employees/bulk-assign-position
//
//  Body:
//    employee_ids[]   → array UUID karyawan yang dicentang
//    position_id      → UUID posisi yang akan di-assign
//    set_as_primary   → boolean (opsional, default false)
//    replace_all      → boolean — jika true, hapus semua posisi lama dulu
// ──────────────────────────────────────────────────────────────
public function bulkAssignPosition(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
 
    if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
 
    $request->validate([
        'employee_ids'   => 'required|array|min:1',
        'employee_ids.*' => 'required|string|exists:employees_tables,id',
        'position_id'    => 'required|string|exists:position_tables,id',
        'set_as_primary' => 'boolean',
        'replace_all'    => 'boolean',
    ]);
 
    $employeeIds  = $request->employee_ids;
    $positionId   = $request->position_id;
    $setAsPrimary = (bool) $request->input('set_as_primary', false);
    $replaceAll   = (bool) $request->input('replace_all', false);
 
    DB::beginTransaction();
 
    try {
        foreach ($employeeIds as $employeeId) {
 
            // Jika replace_all → hapus semua pivot posisi karyawan ini dulu
            if ($replaceAll) {
                EmployeePosition::where('employee_id', $employeeId)->delete();
            }
 
            // Jika set_as_primary → lepas flag primary dari posisi lain
            if ($setAsPrimary) {
                EmployeePosition::where('employee_id', $employeeId)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
 
            // Cek apakah kombinasi ini sudah ada (hindari duplikat)
            $exists = EmployeePosition::where('employee_id', $employeeId)
                ->where('position_id', $positionId)
                ->exists();
 
            if (!$exists) {
                EmployeePosition::create([
                    'employee_id' => $employeeId,
                    'position_id' => $positionId,
                    'is_primary'  => $setAsPrimary,
                ]);
            } elseif ($setAsPrimary) {
                // Record sudah ada tapi perlu di-set primary
                EmployeePosition::where('employee_id', $employeeId)
                    ->where('position_id', $positionId)
                    ->update(['is_primary' => true]);
            }
        }
 
        DB::commit();
 
        return response()->json([
            'success' => true,
            'message' => count($employeeIds) . ' karyawan berhasil di-assign ke posisi.',
        ]);
 
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('bulkAssignPosition failed', ['error' => $e->getMessage()]);
 
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat assign posisi.',
        ], 500);
    }
}
 
// ──────────────────────────────────────────────────────────────
// 3. BULK DELETE POSITION
//    POST /employees/bulk-delete-position
//
//  Body:
//    employee_ids[]  → array UUID karyawan
//    position_id     → UUID posisi yang akan dihapus
//                      (kosongkan / null = hapus SEMUA posisi karyawan tsb)
// ──────────────────────────────────────────────────────────────
public function bulkDeletePosition(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
 
    if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
 
    $request->validate([
        'employee_ids'   => 'required|array|min:1',
        'employee_ids.*' => 'required|string|exists:employees_tables,id',
        'position_id'    => 'nullable|string|exists:position_tables,id',
    ]);
 
    $employeeIds = $request->employee_ids;
    $positionId  = $request->input('position_id'); // null = hapus semua
 
    DB::beginTransaction();
 
    try {
        $baseQuery = EmployeePosition::whereIn('employee_id', $employeeIds);
 
        if ($positionId) {
            $baseQuery->where('position_id', $positionId);
        }
 
        $deleted = $baseQuery->delete();
 
        DB::commit();
 
        return response()->json([
            'success' => true,
            'message' => $deleted . ' record posisi berhasil dihapus.',
        ]);
 
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('bulkDeletePosition failed', ['error' => $e->getMessage()]);
 
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat hapus posisi.',
        ], 500);
    }
}
 
// ──────────────────────────────────────────────────────────────
// 4. BULK ASSIGN ATASAN
//    POST /employees/bulk-assign-atasan
//
//  Body:
//    employee_ids[]  → array UUID karyawan yang dicentang
//    atasan_id       → UUID karyawan yang jadi atasan
//    set_as_primary  → boolean (opsional, default false)
//    replace_all     → boolean — jika true, hapus semua atasan lama dulu
// ──────────────────────────────────────────────────────────────
public function bulkAssignAtasan(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
 
    if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
 
    $request->validate([
        'employee_ids'   => 'required|array|min:1',
        'employee_ids.*' => 'required|string|exists:employees_tables,id',
        'atasan_id'      => 'required|string|exists:employees_tables,id',
        'set_as_primary' => 'boolean',
        'replace_all'    => 'boolean',
    ]);
 
    $employeeIds  = $request->employee_ids;
    $atasanId     = $request->atasan_id;
    $setAsPrimary = (bool) $request->input('set_as_primary', false);
    $replaceAll   = (bool) $request->input('replace_all', false);
 
    // Cegah karyawan assign dirinya sendiri sebagai atasan
    if (in_array($atasanId, $employeeIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Karyawan tidak bisa menjadi atasan dirinya sendiri.',
        ], 422);
    }
 
    DB::beginTransaction();
 
    try {
        foreach ($employeeIds as $employeeId) {
 
            if ($replaceAll) {
                EmployeeAtasan::where('employee_id', $employeeId)->delete();
            }
 
            if ($setAsPrimary) {
                EmployeeAtasan::where('employee_id', $employeeId)
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
 
            $exists = EmployeeAtasan::where('employee_id', $employeeId)
                ->where('atasan_id', $atasanId)
                ->exists();
 
            if (!$exists) {
                EmployeeAtasan::create([
                    'employee_id' => $employeeId,
                    'atasan_id'   => $atasanId,
                    'is_primary'  => $setAsPrimary,
                ]);
            } elseif ($setAsPrimary) {
                EmployeeAtasan::where('employee_id', $employeeId)
                    ->where('atasan_id', $atasanId)
                    ->update(['is_primary' => true]);
            }
        }
 
        DB::commit();
 
        return response()->json([
            'success' => true,
            'message' => count($employeeIds) . ' karyawan berhasil di-assign atasannya.',
        ]);
 
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('bulkAssignAtasan failed', ['error' => $e->getMessage()]);
 
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat assign atasan.',
        ], 500);
    }
}
 
// ──────────────────────────────────────────────────────────────
// 5. BULK DELETE ATASAN
//    POST /employees/bulk-delete-atasan
//
//  Body:
//    employee_ids[]  → array UUID karyawan
//    atasan_id       → UUID atasan yang dihapus
//                      (null = hapus SEMUA atasan karyawan tsb)
// ──────────────────────────────────────────────────────────────
public function bulkDeleteAtasan(Request $request)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();
 
    if (!$user->hasPermissionTo('ManageEmployee')) {
        abort(403);
    }
 
    $request->validate([
        'employee_ids'   => 'required|array|min:1',
        'employee_ids.*' => 'required|string|exists:employees_tables,id',
        'atasan_id'      => 'nullable|string|exists:employees_tables,id',
    ]);
 
    $employeeIds = $request->employee_ids;
    $atasanId    = $request->input('atasan_id'); // null = hapus semua
 
    DB::beginTransaction();
 
    try {
        $baseQuery = EmployeeAtasan::whereIn('employee_id', $employeeIds);
 
        if ($atasanId) {
            $baseQuery->where('atasan_id', $atasanId);
        }
 
        $deleted = $baseQuery->delete();
 
        DB::commit();
 
        return response()->json([
            'success' => true,
            'message' => $deleted . ' record atasan berhasil dihapus.',
        ]);
 
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('bulkDeleteAtasan failed', ['error' => $e->getMessage()]);
 
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat hapus atasan.',
        ], 500);
    }
}
}
