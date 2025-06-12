<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banks;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Payrolls;
use App\Models\User;
use App\Models\Submissions;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\DataTables;
use App\Models\Stores;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SubmissionsController extends Controller
{
  public function getSubmissionsall()
    {
        $typeFilter = request()->get('type');

        $query = Submissions::with([
            'user',
            'approval'
            
        ])->select(['id', 'user_id', 'approval_id','type','duration','status']);

        if (!empty($typeFilter)) {
            $query->whereHas('type', function ($q) use ($typeFilter) {
                $q->where('type', $typeFilter);
            });
        }

      

        $submissions = $query->get()->map(function ($submission) {
            $submission->id_hashed = substr(hash('sha256', $submission->id . env('APP_KEY')), 0, 8);
            if (auth()->user()->hasRole('HeadHR')) {

                $submission->action = '
            <a href="' . route('Submissions.edit', $submission->id_hashed) . '" class="mx-3" data-bs-toggle="tooltip" data-bs-original-title="Edit submission" title="Edit Submissions: ' . e(optional($submission->user->employee)->employee_name) . '">
                <i class="fas fa-user-edit text-secondary"></i>
            </a>';
            } else {
                $submission->action = ''; // Optional: kosongkan jika tidak punya akses
            }

            return $submission;
        });
        // Daftar kolom dari relasi Employee yang ingin ditampilkan
        $columns = [
            'user_id' => 'user.employee.employee_name',
            'approval_id' => 'user.employee.employee_name',
            'type',
            'duration',
            
            'status'
        ];

        $dataTable = DataTables::of($submissions);

        foreach ($columns as $key => $relationPath) {
            $column = is_string($key) ? $key : $relationPath;

            $dataTable->addColumn($column, function ($submission) use ($relationPath) {
                // Mendapatkan nilai dari relasi dengan dot notation
                $value = data_get($employee->Employee, $relationPath);
                return $value ?: 'Empty';
            });
        }

        return $dataTable
            ->addColumn('action', function ($employee) {
                return $employee->action;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
