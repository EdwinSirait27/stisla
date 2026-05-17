<?php
namespace App\Http\Controllers;
use App\Models\Banks;
use App\Models\Company;
use App\Models\Departments;
use App\Models\Employee;
use App\Models\Grading;
use App\Models\Payrolls;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\DataTables;
use App\Models\Stores;
use App\Models\Structuresnew;
use Illuminate\Support\Facades\Hash;
use App\Rules\NoXSSInput;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Mail\WelcomeEmployeeMail;
use App\Models\Groups;
use App\Models\Contract;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ContractController extends Controller
{
    public function index()
    {
        $countactivecontracts = Contract::where('contract_status', 'Active')->count();
        $countexpiredcontracts = Contract::where('contract_status', 'Expired')->count();
        $countterminatedcontracts = Contract::where('contract_status', 'Terminated')->count();
        $contractstatuses = Contract::getContractStatusOptions();
        $contracttypes = Contract::getContractTypeOptions();
        $employeestatuses = Employee::getStatusOptions();
        $structures = Structuresnew::with('submissionposition')->where('', 'Active');
        $gradings = Grading::pluck('grading_name', 'id');
        $groups = Groups::pluck('remark', 'id');
        $companies = Company::pluck('name', 'id');
        $departments = Departments::pluck('department_name', 'id');
        $locations = Stores::pluck('name', 'id');
        return view('pages.Contract.Contract', compact('contracttypes', 'employeestatuses', 'companies', 'locations', 'departments', 'countactivecontracts', 'countexpiredcontracts', 'countterminatedcontracts', 'contractstatuses', 'structures', 'gradings', 'groups'));
    }
    public function getActivities(Request $request)
    {
        if ($request->ajax()) {
            $query = Activity::where('log_name', 'contract')
                ->where('subject_type', Contract::class)
                ->with(['causer.employee'])  // eager load sampai employee
                ->latest();

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('description', function ($row) {
                    return $row->description ?? '-';
                })
                ->addColumn('causer', function ($row) {
                    return $row->causer?->employee?->employee_name ?? '-';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('d M Y H:i');
                })
                ->addColumn('event', function ($row) {
                    return ucfirst($row->event ?? '-');
                })
                ->addColumn('changes', function ($row) {
                    $old = $row->properties['old'] ?? [];
                    $new = $row->properties['attributes'] ?? [];

                    $diff = [];
                    foreach ($new as $key => $value) {
                        $diff[$key] = [
                            'old' => $old[$key] ?? null,
                            'new' => $value,
                        ];
                    }

                    return json_encode($diff);
                })
                ->filter(function ($instance) use ($request) {
                    if ($request->has('search') && $request->get('search')['value'] != '') {
                        $search = $request->get('search')['value'];

                        $instance->where(function ($q) use ($search) {
                            $q->where('description', 'like', "%{$search}%")
                                ->orWhere('event', 'like', "%{$search}%")
                                ->orWhereHas('causer.employee', function ($q2) use ($search) {
                                    $q2->where('employee_name', 'like', "%{$search}%");
                                });
                        });
                    }
                })
                ->rawColumns(['description'])
                ->make(true);
        }
    }

    public function getContracts(Request $request)
    {
        $query = Contract::query()

            ->leftJoin('employees_tables as e', 'contract.employee_id', '=', 'e.id')
            ->leftJoin('structures_tables as st', 'st.id', '=', 'e.structure_id')
            ->leftJoin('submission_position_tables as sp', 'sp.id', '=', 'st.submission_position_id')

            ->leftJoin('position_tables as p', 'p.id', '=', 'sp.position_id')
            ->leftJoin('departments_tables as d', 'd.id', '=', 'sp.department_id')
            ->leftJoin('stores_tables as s', 's.id', '=', 'sp.store_id')
            ->leftJoin('company_tables as c', 'c.id', '=', 'sp.company_id')

            ->leftJoin('grading as g', 'g.id', '=', 'e.grading_id')
            ->leftJoin('groups_tables as gr', 'gr.id', '=', 'e.group_id')

            ->select([
                'contract.*',
                'e.employee_name',
                'e.employee_pengenal',

                'p.name as position_name',
                'd.department_name',
                's.name as store_name',
                'g.grading_name',
                'gr.remark',
                'contract.contract_type',
                'contract.contract_status',
                'e.status as employee_status',
                'contract.created_at',
                'c.name as company_name',
            ]);

        $query->when(
            $request->filled('filter_company'),
            fn($q) => $q->where('c.name', $request->filter_company)
        );
        $query->when(
            $request->filled('filter_position'),
            fn($q) => $q->where('p.name', $request->filter_position)
        );
        $query->when(
            $request->filled('filter_store'),
            fn($q) => $q->where('s.name', $request->filter_store)
        );
        $query->when(
            $request->filled('filter_department'),
            fn($q) => $q->where('d.department_name', $request->filter_department)
        );
        $query->when(
            $request->filled('filter_group'),
            fn($q) =>
            $q->where('gr.remark', $request->filter_group)
        );
        $query->when(
            $request->filled('filter_grading'),
            fn($q) =>
            $q->where('g.grading_name', $request->filter_grading)
        );
        $query->when(
            $request->filled('filter_contract_type'),
            fn($q) =>
            $q->where('contract.contract_type', $request->filter_contract_type)
        );
        $query->when(
            $request->filled('filter_contract_status'),
            fn($q) =>
            $q->where('contract.contract_status', $request->filter_contract_status)
        );
        $query->when(
            $request->filled('filter_employee_status'),
            fn($q) =>
            $q->where('e.status', $request->filter_employee_status)
        );
        return DataTables::of($query)
            ->addColumn('action', function ($e) {
                $id = $e->id;
                return '
                <a href="' . route('editcontract', $id) . '" class="mx-2">
                    <i class="fas fa-user-edit text-secondary"></i>
                </a>
                <a href="' . route('showcontract', $id) . '" class="mx-2">
                    <i class="fas fa-eye text-secondary"></i>
                </a>';
            })
            ->filterColumn('employee_name', fn($q, $k) =>
            $q->where('e.employee_name', 'like', "%$k%"))
            ->filterColumn('employee_pengenal', fn($q, $k) =>
            $q->where('e.employee_pengenal', 'like', "%$k%"))
            ->filterColumn('position_name', fn($q, $k) =>
            $q->where('p.name', 'like', "%$k%"))
            ->filterColumn('remark', fn($q, $k) =>
            $q->where('gr.remark', 'like', "%$k%"))
            ->filterColumn('department_name', fn($q, $k) =>
            $q->where('d.department_name', 'like', "%$k%"))
            ->filterColumn('store_name', fn($q, $k) =>
            $q->where('s.name', 'like', "%$k%"))
            ->filterColumn('company_name', fn($q, $k) =>
            $q->where('c.name', 'like', "%$k%"))
            ->filterColumn('grading_name', fn($q, $k) =>
            $q->where('g.grading_name', 'like', "%$k%"))
            ->filterColumn('contract_type', fn($q, $k) =>
            $q->where('contract.contract_type', 'like', "%$k%"))
            ->filterColumn('contract_status', fn($q, $k) =>
            $q->where('contract.contract_status', 'like', "%$k%"))
            ->filterColumn('employee_status', fn($q, $k) =>
            $q->where('e.status', 'like', "%$k%"))
             ->editColumn('created_at', function ($e) {
                return optional($e->created_at)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y');
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    public function edit($id) {}

    public function show($id) {}
//     public function create()
// {
// $employeeData = Employee::whereIn('status', ['Active', 'Mutation', 'Pending'])
//     ->select('id', 'employee_name', 'join_date')
//     ->get();

// $employees = $employeeData->pluck('employee_name', 'id');
// $employeeJoinDates = $employeeData
//     ->mapWithKeys(function ($item) {
//         return [$item->id => \Carbon\Carbon::parse($item->join_date)->format('Y-m-d')];
//     })
//     ->toArray();
//   $structures = Structuresnew::with('submissionposition.positionRelation')
//     ->get()
//     ->pluck('submissionposition.positionRelation.name', 'id');
//     $statusOptions = Contract::getContractStatusOptions();
//     $typeOptions   = Contract::getContractTypeOptions();
//     return view('pages.contract.createcontract', compact(
//         'employees',
//         'employeeJoinDates',
//         'structures',
//         'statusOptions',
//         'typeOptions'
//     ));
// }
public function create()
{
    if (!auth()->user()->hasRole('HeadHR')) {
        abort(403, 'Unauthorized');
    }
    $isHeadHR = auth()->user()->hasRole('HeadHR'); // ✅ WAJIB ADA

    $employeeData = Employee::with('structuresnew')->whereIn('status', ['Active', 'Mutation', 'Pending'])
        ->select('id', 'employee_name', 'join_date')
        ->get();

    // $employees = $employeeData->pluck('employee_name', 'id');
$employees = Employee::with('structuresnew.submissionposition.positionRelation')
    ->whereIn('status', ['Active', 'Mutation', 'Pending'])
    ->get();
    $employeeJoinDates = $employeeData
        ->mapWithKeys(function ($item) {
            return [$item->id => \Carbon\Carbon::parse($item->join_date)->format('Y-m-d')];
        })
        ->toArray();

    $structures = Structuresnew::with('submissionposition.positionRelation')
        ->get()
        ->pluck('submissionposition.positionRelation.name', 'id');

    $statusOptions = Contract::getContractStatusOptions();
    $typeOptions   = Contract::getContractTypeOptions();
$confirmedAt = session('contract_password_confirmed_at');

$isPasswordExpired = true;

if ($confirmedAt) {
    $isPasswordExpired = now()->diffInMinutes($confirmedAt) >= 5;
}
    return view('pages.contract.createcontract', compact(
        'employees',
        'employeeJoinDates',
        'structures',
        'statusOptions',
        'typeOptions',
        'isHeadHR',
    'isPasswordExpired'
    ));
}
public function checkPassword(Request $request)
{
    if (Hash::check($request->password, auth()->user()->password)) {

        session([
            'contract_password_confirmed_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false]);
}


public function store(Request $request)
{
    $validated = $request->validate([
        'employee_id' => 'required|exists:employees_tables,id',
        'contract_type' => 'required|in:PKWT,On Job Training,DW',
        'start_date' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'basic_salary' => 'nullable|numeric',
        'positional_allowance' => 'nullable|numeric',
        'daily_rate' => 'nullable|numeric',
        'contract_status' => 'required|in:Active,Expired,Terminated',
        'file_path' => 'nullable|file|mimes:pdf|max:2048',
        'notes' => 'nullable|string',
    ]);
    DB::beginTransaction();

    try {

        // ========================================================
    // LOCK + CEK ACTIVE CONTRACT
    // ========================================================
    if ($validated['contract_status'] === 'Active') {

        $existsActive = Contract::where('employee_id', $validated['employee_id'])
            ->where('contract_status', 'Active')
            ->lockForUpdate() // 🔥 penting
            ->exists();

        if ($existsActive) {
            throw new \Exception('Employee ini sudah memiliki contract Active');
        }
    }

        // ========================================================
        // AMBIL STRUCTURE
        // ========================================================
        $employee = Employee::findOrFail($validated['employee_id']);

        if (!$employee->structure_id) {
            throw new \Exception('Employee belum memiliki structure');
        }
        $validated['structure_id'] = $employee->structure_id;
        // ========================================================
        // FILE
        // ========================================================
        if ($request->hasFile('file_path')) {
            $validated['file_path'] = $request->file('file_path')
                ->store('contracts', 'public');
        }
        // ========================================================
        // CREATE
        // ========================================================
        Contract::create($validated);
        DB::commit();
        return redirect()
            ->route('contracts.index')
            ->with('success', 'Contract berhasil dibuat');
    } catch (\Exception $e) {

        DB::rollBack();

        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }
}
    public function transferAllToPayroll(Request $request)
    {
        try {
            $month_year = $request->input('month_year', date('Y-m-d'));

            $month = date('m', strtotime($month_year));
            $year = date('Y', strtotime($month_year));

            $employeeIds = User::whereNotNull('employee_id')
                ->whereHas('employee', function ($query) {
                    $query->whereIn('status', ['Mutation', 'Active', 'On Leave']);
                })
                ->pluck('employee_id')
                ->toArray();

            $transferred = 0;
            $skipped = 0;

            foreach ($employeeIds as $employeeId) {
                $exists = Payrolls::where('employee_id', $employeeId)
                    ->whereMonth('month_year', $month)
                    ->whereYear('month_year', $year)
                    ->exists();

                if (!$exists) {
                    Payrolls::create([
                        'employee_id' => $employeeId,
                        'month_year' => $month_year,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $transferred++;
                } else {
                    $skipped++;
                }
            }
            $message = "Transfer completed: $transferred employee(s) transferred for period " . date('F Y', strtotime($month_year)) .
                ", $skipped employee(s) skipped (already exist for this month)";

            return response()->json([
                'success' => true,
                'message' => $message,
                'transferred' => $transferred,
                'skipped' => $skipped,
                'period' => date('F Y', strtotime($month_year))
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to transfer: ' . $e->getMessage()]);
        }
    }
}
