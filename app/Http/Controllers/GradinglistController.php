<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Yajra\DataTables\DataTables;
use App\Models\Structure;
use App\Models\Grading;
use App\Models\User;
use App\Rules\NoXSSInput;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class GradinglistController extends Controller
{
    public function index()
    {
        return view('pages.Gradinglist.Gradinglist');
    }
    public function getGradinglists()
    {
        $gradinglists = User::whereHas('Employee', function ($q) {
            $q->whereIn('status', ['Active', 'Pending','Mutation']);
        })
            ->with([
                'Employee' => function ($q) {
                    $q->whereIn('status', ['Active', 'Pending','Mutation']);
                },
                'Employee.employees',
                'Employee.company',
                'Employee.grading',
                'Employee.grading.groups',
            ])
            ->select(['id', 'employee_id'])
            ->get();
        return DataTables::of($gradinglists)
            ->addColumn('employee_name', function ($gradinglist) {
                return !empty($gradinglist->Employee) && !empty($gradinglist->Employee->employee_name)
                    ? $gradinglist->Employee->employee_name
                    : 'Empty';
            })

            ->addColumn('grading_name', function ($gradinglist) {
                return !empty($gradinglist->Employee->grading) && !empty($gradinglist->Employee->grading->grading_name)
                    ? $gradinglist->Employee->grading->grading_name
                    : 'Empty';
            })
            ->addColumn('group_name', function ($gradinglist) {
                return !empty($gradinglist->Employee->grading->groups) && !empty($gradinglist->Employee->grading->groups->grading_name)
                    ? $gradinglist->Employee->grading->groups->grading_name
                    : 'Empty';
            })
            ->addColumn('remark', function ($gradinglist) {
                return !empty($gradinglist->Employee->grading->groups) && !empty($gradinglist->Employee->grading->groups->remark)
                    ? $gradinglist->Employee->grading->groups->remark
                    : 'Empty';
            })
            
            ->rawColumns(['employee_name', 'grading_name','group_name','remark'])
            ->make(true);
    }
    public function edit($hashedId)
    {
        $gradinglist = User::with('Employee.employees', 'Employee.company', 'Employee','Employee.grading')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });

        if (!$gradinglist) {
            abort(404, 'gradinglist not found.');
        }

        // $employees = Employee::where('status', ['Active','Pending'])->pluck('employee_name', 'id');
$gradings = Grading::pluck('grading_name', 'id');
        return view('pages.Gradinglist.edit', [
            'gradinglist' => $gradinglist,
            'hashedId' => $hashedId,
            'gradings' => $gradings,
        ]);
    }
    public function update(Request $request, $hashedId)
    {
        $gradinglist = User::with('Employee.employees', 'Employee.company','Employee.grading')->get()->first(function ($u) use ($hashedId) {
            $expectedHash = substr(hash('sha256', $u->id . env('APP_KEY')), 0, 8);
            return $expectedHash === $hashedId;
        });
        if (!$gradinglist) {
            return redirect()->route('pages.Gradinglist')->with('error', 'ID tidak valid.');
        }
        $validatedData = $request->validate([
            'grading_id' => [
                'required',
                'string',
                'max:255',
                new NoXSSInput()
            ]
        ]);
        $employeeData = [
            'grading_id' => $validatedData['grading_id'],
        ];
        DB::beginTransaction();
        $gradinglist->Employee->update($employeeData);
        DB::commit();
        return redirect()->route('pages.Gradinglist')->with('success', 'grading Updated Successfully.');
    }
}
