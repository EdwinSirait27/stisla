<?php

namespace App\Http\Controllers;

use App\Models\SkLetter;
use App\Models\SkLetterEmployee;
use App\Models\Sktype;
use App\Models\Company;
use App\Models\Grading;
use App\Models\Groups;
use Yajra\DataTables\DataTables;
use App\Models\Departments;
use App\Models\Stores;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Position;
use App\Models\Structuresnew;
use Carbon\Carbon;
use App\Services\SkLetterService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
class SkLetterController extends Controller
{
    public function __construct(protected SkLetterService $service)
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $countexpiredcontracts = SkLetter::where('status', 'Expired')->count();
        $companies = Company::pluck('name', 'id');
        $countdraftcontracts = SkLetter::where('status', 'Draft')->count();
        $countactivecontracts = SkLetter::where('status', 'Active')->count();
        $countapprovedhrcontracts = SkLetter::where('status', 'Approved HR')->count();
        $countapproveddircontracts = SkLetter::where('status', 'Approved Director')->count();
        $countapprovedmanagingcontracts = SkLetter::where('status', 'Approved Managing Director')->count();
        $sktypes = Sktype::pluck('sk_name', 'id');
        $skstatuses = SkLetter::getStatusOptions();
        return view('pages.SkLetters.index', compact('sktypes','skstatuses','companies','countexpiredcontracts', 'countdraftcontracts', 'countactivecontracts', 'countapprovedhrcontracts', 'countapproveddircontracts', 'countapprovedmanagingcontracts'));
    }
    public function getSkLetters(Request $request)
    {
        $query = SkLetter::query()
            ->leftJoin('sk_type as st', 'st.id', '=', 'sk_letters.sk_type_id')
            ->leftJoin('company_tables as c', 'c.id', '=', 'sk_letters.company_id')
            // ->leftJoin('structures_tables as str', 'str.id', '=', 'sk_letters.structure_id')
            // ->leftJoin('submission_position_tables as sp', 'sp.id', '=', 'str.submission_position_id')
            // ->leftJoin('position_tables as p', 'p.id', '=', 'sp.position_id')
            ->leftJoin('employees_tables as a1', 'a1.id', '=', 'sk_letters.approver_1')
            ->leftJoin('employees_tables as a2', 'a2.id', '=', 'sk_letters.approver_2')
            ->leftJoin('employees_tables as a3', 'a3.id', '=', 'sk_letters.approver_3')
            ->select([
                'sk_letters.*',
                'st.sk_name',
                'st.nickname as sk_nickname',
                'c.name as company_name',
                // 'p.name as position_name',
                'a1.employee_name as approver_1_name',
                'a2.employee_name as approver_2_name',
                'a3.employee_name as approver_3_name',
            ]);
        $query->when(
            $request->filled('filter_company'),
            fn($q) => $q->where('c.name', $request->filter_company)
        );
        $query->when(
            $request->filled('filter_sk_type'),
            fn($q) => $q->where('st.sk_name', $request->filter_sk_type)
        );
        $query->when(
            $request->filled('filter_status'),
            fn($q) => $q->where('sk_letters.status', $request->filter_status)
        );
        $query->when(
            $request->filled('filter_effective_date_start'),
            fn($q) => $q->whereDate('sk_letters.effective_date', '>=', $request->filter_effective_date_start)
        );
        $query->when(
            $request->filled('filter_effective_date_end'),
            fn($q) => $q->whereDate('sk_letters.effective_date', '<=', $request->filter_effective_date_end)
        );
// return DataTables::of($query)
//     ->addColumn('action', function ($row) {
//         $id = $row->id;
//         $actions = '
//         <a href="' . route('SkLetters.show', $id) . '" class="mx-2">
//             <i class="fas fa-eye text-secondary"></i>
//         </a>';

//         $lockedStatuses = ['Approved HR', 'Approved Director', 'Approved Managing Director'];

//         if ($row->status === 'Draft' && auth()->user()->hasRole('HeadHR')) {
//             $actions .= '
//         <a href="' . route('SkLetters.edit', $id) . '" class="mx-2">
//             <i class="fas fa-user-edit text-secondary"></i>
//         </a>';
//         } elseif (in_array($row->status, $lockedStatuses)) {
//             $actions .= '
//         <span class="mx-2 text-secondary" 
//               title="Status terkunci" 
//               data-bs-toggle="tooltip" 
//               data-bs-placement="top"
//               style="cursor: not-allowed;">
//             <i class="fas fa-lock"></i>
//         </span>';
//         }

//         return $actions;
//     })
return DataTables::of($query)
    ->addColumn('action', function ($row) {
        $id = $row->id;
        $actions = '
        <a href="' . route('SkLetters.show', $id) . '" class="mx-2" title="Detail">
            <i class="fas fa-eye text-secondary"></i>
        </a>';

        $lockedStatuses = ['Approved HR', 'Approved Director', 'Approved Managing Director'];
        if ($row->status === 'Draft' && auth()->user()->hasRole('HeadHR')) {
            $actions .= '
        <a href="' . route('SkLetters.edit', $id) . '" class="mx-2" title="Edit">
            <i class="fas fa-user-edit text-secondary"></i>
        </a>';

        } elseif (in_array($row->status, $lockedStatuses)) {
            $actions .= '
        <span class="mx-2 text-secondary" 
              title="Status terkunci" 
              data-bs-toggle="tooltip" 
              data-bs-placement="top"
              style="cursor: not-allowed;">
            <i class="fas fa-lock"></i>
        </span>';

       
        }
         // PDF (pisahkan dari logic lain)
    if (in_array($row->status, $lockedStatuses) || $row->status === 'Draft') {
        $actions .= '
            <a href="' . route('SkLetters.pdf', $id) . '" class="mx-2" title="Export PDF" target="_blank">
                <i class="fas fa-file-pdf text-danger"></i>
            </a>
        ';
    }

        return $actions;
    })
            // Filter columns untuk DataTables server-side
            ->filterColumn('sk_name', fn($q, $k) =>
            $q->where('st.sk_name', 'like', "%$k%"))
            ->filterColumn('company_name', fn($q, $k) =>
            $q->where('c.name', 'like', "%$k%"))
            ->filterColumn('approver_1_name', fn($q, $k) =>
            $q->where('a1.employee_name', 'like', "%$k%"))
            ->filterColumn('approver_2_name', fn($q, $k) =>
            $q->where('a2.employee_name', 'like', "%$k%"))
            ->filterColumn('approver_3_name', fn($q, $k) =>
            $q->where('a3.employee_name', 'like', "%$k%"))
            ->filterColumn('status', fn($q, $k) =>
            $q->where('sk_letters.status', 'like', "%$k%"))

          
            ->editColumn('effective_date', function ($row) {
                return $row->effective_date
                    ? Carbon::parse($row->effective_date)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y')
                    : '-';
            })
            ->editColumn('inactive_date', function ($row) {
                return $row->inactive_date
                    ? Carbon::parse($row->inactive_date)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y')
                    : 'Continuesly';
            })
            ->editColumn('approver_1_at', function ($row) {
                return $row->approver_1_at
                    ? Carbon::parse($row->approver_1_at)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y HH:mm')
                    : '-';
            })

            ->editColumn('approver_2_at', function ($row) {
                return $row->approver_2_at
                    ? Carbon::parse($row->approver_2_at)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y HH:mm')
                    : '-';
            })
            ->editColumn('approver_3_at', function ($row) {
                return $row->approver_3_at
                    ? Carbon::parse($row->approver_3_at)
                    ->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y HH:mm')
                    : '-';
            })
            ->editColumn(
                'created_at',
                fn($row) =>
                optional($row->created_at)
                    ?->timezone('Asia/Makassar')
                    ->translatedFormat('d F Y')
            )
            ->editColumn('status', function ($row) {
                $colors = [
                    'Draft'                      => 'secondary',
                    'Cancelled'                  => 'danger',
                    'Approved HR'                => 'info',
                    'Approved Director'          => 'primary',
                    'Approved Managing Director' => 'success',
                ];
                $color = $colors[$row->status] ?? 'secondary';
                return '<span class="badge badge-' . $color . '">' . $row->status . '</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
    // public function create()
    // {
    //     $skTypeFlags = Sktype::all()->keyBy('id')->map(fn($t) => [
    //     'affects_salary'   => $t->affects_salary,
    //     'affects_position' => $t->affects_position,
    //     'affects_status'   => $t->affects_status,
    //     'generates_contract' => $t->generates_contract,
    // ]);

    // return view('pages.SkLetters.create', [
    //     'sktypes'     => Sktype::pluck('sk_name', 'id'),
    //     'companies'   => Company::pluck('name', 'id'),
    //     'employees'   => Employee::select('id', 'employee_name', 'employee_pengenal')->get(),
    //     // 'employees_approver_2'   => Employee::select('id', 'employee_name', 'employee_pengenal')->get(),
    //     'employees_approver_2' => Employee::select('id', 'employee_name', 'employee_pengenal', 'grading_id')
    // ->with(['grading:id,grading_name'])
    // ->whereHas('grading', function ($q) {
    //     $q->where('grading_name', 'Director');
    // })
    // ->get(),
    //     'employees_approver_3'   => Employee::select('id', 'employee_name', 'employee_pengenal')->get(),
    //     // 'structures'  => Structuresnew::with('submissionposition.positionRelation')->get(),
    //     $usedStructureIds = Employee::whereNotNull('structure_id')->pluck('structure_id')->toArray();

    //  'structures' => Structuresnew::with('submissionposition')
    // ->where(function ($q) use ($usedStructureIds, $employee) {

    //     $q->whereNotIn('id', $usedStructureIds)
    //       ->orWhere('id', optional($employee->Employee)->structure_id);

    // })
    // ->get(),
    
    //     'skTypeFlags' => $skTypeFlags,
    // ]);
    // }
    public function create()
{
    $skTypeFlags = Sktype::all()->keyBy('id')->map(fn($t) => [
        'affects_salary'    => $t->affects_salary,
        'affects_position'  => $t->affects_position,
        'affects_status'    => $t->affects_status,
        'generates_contract'=> $t->generates_contract,
    ]);

    $usedStructureIds = Employee::whereNotNull('structure_id')
        ->pluck('structure_id')
        ->toArray();
    $sktypes = Sktype::pluck('sk_name', 'id');
    $companies = Company::pluck('name', 'id');
$employees = Employee::select('id', 'employee_name', 'employee_pengenal')
    ->whereIn('status', ['Active', 'Pending', 'Mutation', 'On Leave'])
    ->get();
    $employees_approver_2 = Employee::select('id', 'employee_name', 'employee_pengenal', 'grading_id')
        ->with(['grading:id,grading_name'])
        ->whereHas('grading', function ($q) {
            $q->where('grading_name', 'Director');
        })
        ->get();
    $employees_approver_3 = Employee::select('id', 'employee_name', 'employee_pengenal', 'grading_id')
        ->with(['grading:id,grading_name'])
        ->whereHas('grading', function ($q) {
            $q->where('grading_name', 'Director');
        })
        ->get();
    $structures = Structuresnew::with('submissionposition')
        ->whereNotIn('id', $usedStructureIds)
        ->get();
    return view('pages.SkLetters.create', compact(
        'sktypes',
        'companies',
        'employees',
        'employees_approver_2',
        'employees_approver_3',
        'structures',
        'skTypeFlags'
    ));
}
//     public function store(Request $request): RedirectResponse
// {
//     $validated = $request->validate([
//         // SK Header
//         'sk_type_id'      => 'required|exists:sk_type,id',
//         'title'           => 'nullable|string|max:255',
//         'company_id'      => 'required|exists:company_tables,id',
//         // 'structure_id'    => 'nullable|exists:structures_tables,id',
//         'approver_1'      => 'nullable|exists:employees_tables,id',
//         'approver_2'      => 'nullable|exists:employees_tables,id',
//         'approver_3'      => 'nullable|exists:employees_tables,id',
//         'effective_date'  => 'required|date',
//         'inactive_date'   => 'nullable|date|after:effective_date',
//         'location'        => 'nullable|string|max:255',
//         'menetapkan_text' => 'nullable|string',
//         'notes'           => 'nullable|string',
//         'employees'                        => 'required|array|min:1',
//         'employees.*.employee_id'          => 'required|exists:employees_tables,id',
//         'employees.*.new_structure_id'     => 'nullable|exists:structures_tables,id',
//         'employees.*.position_id'          => 'nullable|exists:position_tables,id',
//         'employees.*.group_id'             => 'nullable|exists:groups_tables,id',
//         'employees.*.grading_id'           => 'nullable|exists:grading,id',
//         'employees.*.department_id'        => 'nullable|exists:departments_tables,id',
//         'employees.*.basic_salary'         => 'nullable|numeric|min:0',
//         'employees.*.positional_allowance' => 'nullable|numeric|min:0',
//         'employees.*.daily_rate'           => 'nullable|numeric|min:0',
//         'employees.*.notes'                => 'nullable|string',

//         // Menimbang
//         'menimbang'   => 'nullable|array',
//         'menimbang.*' => 'nullable|string|max:500',

//         // Mengingat
//         'mengingat'   => 'nullable|array',
//         'mengingat.*' => 'nullable|string|max:500',

//         // Keputusan
//         'keputusan'   => 'nullable|array',
//         'keputusan.*' => 'nullable|string',
//     ]);
//     try {
//         $skLetter = $this->service->store($validated);
//         return redirect()
//             ->route('SkLetters.show', $skLetter)
//             ->with('success', 'SK created Succesfully.');
//     } catch (\Exception $e) {
//         return back()->with('error', $e->getMessage())->withInput();
//     }
// }
// public function store(Request $request): RedirectResponse
// {
//     Log::info('SK Letter store request started', [
//         'user_id' => auth()->id(),
//         'ip'      => $request->ip(),
//     ]);

//     $validated = $request->validate([
//         // SK Header
//         'sk_type_id'      => 'required|exists:sk_type,id',
//         'title'           => 'required|string|max:255',
//         'company_id'      => 'required|exists:company_tables,id',
//         // 'structure_id'    => 'nullable|exists:structures_tables,id',
//         'approver_1'      => 'nullable|exists:employees_tables,id',
//         'approver_2'      => 'nullable|exists:employees_tables,id',
//         'approver_3'      => 'nullable|exists:employees_tables,id',
//         'effective_date'  => 'required|date',
//         'inactive_date'   => 'nullable|date|after:effective_date',
//         'location'        => 'nullable|string|max:255',
//         'menetapkan_text' => 'nullable|string',
//         'notes'           => 'nullable|string',

//         'employees'                        => 'required|array|min:1',
//         'employees.*.employee_id'          => 'required|exists:employees_tables,id',
//         'employees.*.new_structure_id'     => 'nullable|exists:structures_tables,id',
//         'employees.*.position_id'          => 'nullable|exists:position_tables,id',
//         'employees.*.group_id'             => 'nullable|exists:groups_tables,id',
//         'employees.*.grading_id'           => 'nullable|exists:grading,id',
//         'employees.*.department_id'        => 'nullable|exists:departments_tables,id',
//         'employees.*.basic_salary'         => 'nullable|numeric|min:0',
//         'employees.*.positional_allowance' => 'nullable|numeric|min:0',
//         'employees.*.daily_rate'           => 'nullable|numeric|min:0',
//         'employees.*.notes'                => 'nullable|string',

//         // Menimbang
//         'menimbang'   => 'nullable|array',
//         'menimbang.*' => 'nullable|string|max:500',

//         // Mengingat
//         'mengingat'   => 'nullable|array',
//         'mengingat.*' => 'nullable|string|max:500',

//         // Keputusan
//         'keputusan'   => 'nullable|array',
//         'keputusan.*' => 'nullable|string',
//     ]);

//     Log::info('SK Letter validation passed', [
//         'validated_data' => $validated,
//     ]);

//     try {

//         Log::info('Calling SK service store');

//         $skLetter = $this->service->store($validated);

//         Log::info('SK Letter created successfully', [
//             'sk_letter_id' => $skLetter->id,
//         ]);

//         return redirect()
//             ->route('SkLetters.show', $skLetter)
//             ->with('success', 'SK created successfully.');

//     } catch (ValidationException $e) {

//     Log::error('Validation failed', [
//         'errors' => $e->errors(),
//     ]);

//     throw $e;

// }catch (\Exception $e) {

//         Log::error('Failed to create SK Letter', [
//             'message' => $e->getMessage(),
//             'file'    => $e->getFile(),
//             'line'    => $e->getLine(),
//             'trace'   => $e->getTraceAsString(),
//         ]);

//         return back()
//             ->with('error', $e->getMessage())
//             ->withInput();
//     }
// }
public function store(Request $request): RedirectResponse
{
    Log::info('SK Letter store request started', [
        'user_id' => auth()->id(),
        'ip'      => $request->ip(),
    ]);

    try {
        $request->merge([
    'employees' => collect($request->employees)->map(function ($employee) {

        $employee['basic_salary'] = isset($employee['basic_salary'])
            ? str_replace('.', '', $employee['basic_salary'])
            : null;

        $employee['positional_allowance'] = isset($employee['positional_allowance'])
            ? str_replace('.', '', $employee['positional_allowance'])
            : null;

        $employee['daily_rate'] = isset($employee['daily_rate'])
            ? str_replace('.', '', $employee['daily_rate'])
            : null;

        return $employee;
    })->toArray()
]);

        $validated = $request->validate([

            // SK Header
            'sk_type_id'      => 'required|exists:sk_type,id',
            'title'           => 'required|string|max:255',
            'company_id'      => 'required|exists:company_tables,id',

            'approver_1'      => 'nullable|exists:employees_tables,id',
            'approver_2'      => 'nullable|exists:employees_tables,id',
            'approver_3'      => 'nullable|exists:employees_tables,id',

            'effective_date'  => 'required|date',
            'inactive_date'   => 'nullable|date|after:effective_date',

            'location'        => 'nullable|string|max:255',
            'menetapkan_text' => 'nullable|string',
            'notes'           => 'nullable|string',

            // Employees
            'employees'                        => 'required|array|min:1',
            'employees.*.employee_id'          => 'required|exists:employees_tables,id',
            'employees.*.new_structure_id'     => 'nullable|exists:structures_tables,id',
            'employees.*.position_id'          => 'nullable|exists:position_tables,id',
            'employees.*.group_id'             => 'nullable|exists:groups_tables,id',
            'employees.*.grading_id'           => 'nullable|exists:grading,id',
            'employees.*.department_id'        => 'nullable|exists:departments_tables,id',
            'employees.*.basic_salary'         => 'nullable|numeric|min:0',
            'employees.*.positional_allowance' => 'nullable|numeric|min:0',
            'employees.*.daily_rate'           => 'nullable|numeric|min:0',
            'employees.*.notes'                => 'nullable|string',

            // Menimbang
            'menimbang'   => 'nullable|array',
            'menimbang.*' => 'nullable|string|max:500',

            // Mengingat
            'mengingat'   => 'nullable|array',
            'mengingat.*' => 'nullable|string|max:500',

            // Keputusan
            'keputusan'   => 'nullable|array',
            'keputusan.*' => 'nullable|string',
        ]);

        Log::info('SK Letter validation passed', [
            'validated_data' => $validated,
        ]);

        Log::info('Calling SK service store');

        $skLetter = $this->service->store($validated);

        Log::info('SK Letter created successfully', [
            'sk_letter_id' => $skLetter->id,
        ]);

        return redirect()
            ->route('SkLetters.show', $skLetter)
            ->with('success', 'SK created successfully.');

    } catch (\Illuminate\Validation\ValidationException $e) {

        Log::error('Validation failed', [
            'errors' => $e->errors(),
            'request' => $request->all(),
        ]);

        return back()
            ->withErrors($e->validator)
            ->withInput();

    } catch (\Exception $e) {

        Log::error('Failed to create SK Letter', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);

        return back()
            ->with('error', $e->getMessage())
            ->withInput();
    }
}
// public function show(SkLetter $skletter)
//     {
//         $skletter->load([
//             'sktype',
//             'company',
//             'approver1',
//             'approver2',
//             'approver3',
//             'employees',
//             'contracts',
//         ]);
//         return view('pages.SkLetters.show', [
//             'skletter'  => $skletter,
//             'employees' => Employee::select('id', 'employee_name', 'employee_pengenal')->get(),
//             'structures' => Structuresnew::with('submissionposition.positionRelation')->get(),
//         ]);
//     }
public function show(SkLetter $skletter)
    {
        $skletter->load([
            'sktype',
            'company',
            'approver1',
            'approver2',
            'approver3',
            'employees',
            'contracts',
        ]);
        return view('pages.SkLetters.show', [
            'skletter'  => $skletter,
            'employees' => Employee::select('id', 'employee_name', 'employee_pengenal')->get(),
            'structures' => Structuresnew::with('submissionposition.positionRelation')->get(),
        ]);
    }
  public function edit(SkLetter $skLetter)
{
    $lockedStatuses = ['Approved HR', 'Approved Director', 'Approved Managing Director'];

    if ($skLetter->status !== 'Draft' || !auth()->user()->hasRole('HeadHR')) {
        return redirect()
            ->route('SkLetters.show', $skLetter)
            ->with('error', 'SK yang sudah diproses tidak dapat diedit.');
    }

    if (in_array($skLetter->status, $lockedStatuses)) {
        return redirect()
            ->route('SkLetters.show', $skLetter)
            ->with('error', 'SK yang sudah diapprove tidak dapat diedit.');
    }

    return view('sk-letters.edit', [
        'skLetter'   => $skLetter,
        'skTypes'    => Sktype::all(),
        'companies'  => Company::all(),
        'structures' => Structuresnew::with('submissionposition.positionRelation')->get(),
        'employees'  => Employee::select('id', 'employee_name', 'employee_pengenal')->get(),
    ]);
}
    public function update(Request $request, SkLetter $skLetter): RedirectResponse
    {
        $validated = $request->validate([
            'sk_type_id'     => 'required|exists:sk_type,id',
            'company_id'     => 'required|exists:company_tables,id',
            'approver_1'     => 'nullable|exists:employees_tables,id',
            'approver_2'     => 'nullable|exists:employees_tables,id',
            'approver_3'     => 'nullable|exists:employees_tables,id',
            'effective_date' => 'required|date',
            'inactive_date'  => 'nullable|date|after:effective_date',
            'notes'          => 'nullable|string',
        ]);
        try {
            $this->service->update($skLetter, $validated);
            return redirect()
                ->route('SkLetters.show', $skLetter)
                ->with('success', 'SK berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
    public function approve(SkLetter $skLetter): RedirectResponse
    {
        $this->authorize('approve', $skLetter);

        try {
            $this->service->approve($skLetter);
            return back()->with('success', 'SK berhasil diapprove.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(SkLetter $skLetter): RedirectResponse
    {
        $this->authorize('cancel', $skLetter);

        try {
            $this->service->cancel($skLetter);
            return back()->with('success', 'SK berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────
    // SK Letter Employee
    // ─────────────────────────────────────────

    public function addEmployee(Request $request, SkLetter $skLetter): RedirectResponse
    {
        $this->authorize('update', $skLetter);

        $validated = $request->validate([
            'employee_id'          => 'required|exists:employees_tables,id',
            'new_structure_id'     => 'nullable|exists:structures_tables,id',
            'basic_salary'         => 'nullable|numeric|min:0',
            'positional_allowance' => 'nullable|numeric|min:0',
            'daily_rate'           => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string',
        ]);

        try {
            $this->service->addEmployee($skLetter, $validated);
            return back()->with('success', 'Karyawan berhasil ditambahkan ke SK.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateEmployee(Request $request, SkLetter $skLetter, SkLetterEmployee $skLetterEmployee): RedirectResponse
    {
        $this->authorize('update', $skLetter);

        $validated = $request->validate([
            'new_structure_id'     => 'nullable|exists:structures_tables,id',
            'basic_salary'         => 'nullable|numeric|min:0',
            'positional_allowance' => 'nullable|numeric|min:0',
            'daily_rate'           => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string',
        ]);

        try {
            $this->service->updateEmployee($skLetterEmployee, $validated);
            return back()->with('success', 'Data karyawan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function removeEmployee(SkLetter $skLetter, SkLetterEmployee $skLetterEmployee): RedirectResponse
    {
        $this->authorize('update', $skLetter);

        try {
            $this->service->removeEmployee($skLetterEmployee);
            return back()->with('success', 'Karyawan berhasil dihapus dari SK.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    // ─────────────────────────────────────────
    // Generate Contract dari SK
    // ─────────────────────────────────────────
    public function generateContract(Request $request, SkLetter $skLetter): RedirectResponse
    {
        $this->authorize('generateContract', $skLetter);

        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees_tables,id',
            'contract_type' => 'required|in:PKWT,On Job Training,DW',
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after:start_date',
            'notes'         => 'nullable|string',
        ]);
        try {
            $contract = $this->service->generateContract($skLetter, $validated);
            return redirect()
                ->route('contracts.show', $contract)
                ->with('success', 'Contract berhasil dibuat dari SK.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    public function viewPdf(SkLetter $skLetter)
{
    $skLetter->load([
        'sktype',
        'company',
        'approver1',
        'approver1',
        'approver2',
        'approver3',
        'menimbang',
        'mengingat',
        'keputusan',
        'employees',
    ]);

    $pdf = Pdf::loadView('pages.SkLetters.pdf', compact('skLetter'))
        ->setPaper('a4', 'portrait');

    $filename = 'SK-' . str_replace('/', '-', $skLetter->sk_number) . '.pdf';

    return $pdf->download($filename);
    // Ganti stream() ke download() jika ingin langsung download
}
}
