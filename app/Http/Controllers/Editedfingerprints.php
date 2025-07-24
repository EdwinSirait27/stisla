<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Stores;
use App\Models\EditedFingerprint;


class Editedfingerprints extends Controller
{
    public function index()
    {
        $stores = Stores::select('id', 'name')
            ->whereNotNull('name')
            ->distinct()
            ->pluck('name');
        return view('pages.Editedingerprints.Editedingerprints', compact('stores'));
    }
    public function getDepartments()
    {
        $departments = Departments::with('user.Employee')->select(['id', 'department_name','manager_id'])
            ->get()
            ->map(function ($department) {
                $department->id_hashed = substr(hash('sha256', $department->id . env('APP_KEY')), 0, 8);
                $department->action = '
                    <a href="' . route('Department.edit', $department->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit user"title="Edit Department: ' . e($department->department_name) . '">
                        <i class="fas fa-user-edit text-secondary"></i>
                    </a>';
                return $department;
            });
        return DataTables::of($departments)
        ->addColumn('employee_name', function ($department) {
            return !empty($department->user->Employee) && !empty($department->user->Employee->employee_name)
                ? $department->user->Employee->employee_name
                : 'Empty';
        })
            ->rawColumns(['action','employee_name'])
            ->make(true);
    }
}
