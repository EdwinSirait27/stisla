<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Documents;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function index()
    {
        $user     = auth()->user();
        /** @var \App\Models\User|null $user */
        if (!$user->hasPermissionTo('ManageDocument')) {
            abort(403, 'Unauthorized');
        }
        return view('pages.document.index');
    }
    public function getDocuments()
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403, 'Unauthorized');
        }

        $query = Documents::with('companydocumentconfigs.documenttypes','employee:id,employee_name')->select([
                'id',
                'company_document_config_id',
                'employee_id',
                'issued_by',
                'document_number'
            ]);

        return DataTables::of($query)
            ->addColumn('employee_name', function ($document) {
                return $document->employee->employee_name ?? '-';
            })
            ->addColumn('document_name', function ($document) {
                return $document->companydocumentconfigs->documenttypes->document_name ?? '-';
            })
            ->addColumn('action', function ($document) {

                $downloadUrl = route(
                    'documents.download',
                    $document->id
                );

                return '
            <a href="' . $downloadUrl . '"
               class="btn btn-sm btn-primary"
               target="_blank">
                <i class="fas fa-download"></i> Download
            </a>
        ';
            })
            ->filterColumn('employee_name', function ($query, $keyword) {
                $query->whereHas('employee', function ($q) use ($keyword) {
                    $q->where('employee_name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    public function downloadDocument(string $documentId)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (!$user || !$user->hasPermissionTo('ManageDocument')) {
            abort(403);
        }

        $document = Documents::with([
            'employee',
            'employee.position',
            'issued.position',
            'companydocumentconfigs.company',
            'companydocumentconfigs.documenttypes',
        ])->findOrFail($documentId);

        if (
            !$document->companydocumentconfigs ||
            !$document->companydocumentconfigs->documenttypes
        ) {
            abort(404);
        }

        $viewName = $document
            ->companydocumentconfigs
            ->documenttypes
            ->view_name;
        $allowedViews = [
            'documents.types.SPK',
            'documents.types.SPPRP',
        ];
        if (!in_array($viewName, $allowedViews)) {
            abort(403);
        }
        $signatureData = null;
        if ($document->issued && $document->issued->signature) {
            $path = 'employees-signatures-photos/' . basename($document->issued->signature);
            if (Storage::disk('s3')->exists($path)) {
                $signatureData = 'data:image/png;base64,' . base64_encode(
                    Storage::disk('s3')->get($path)
                );
            }
        }
        $pdf = Pdf::loadView($viewName, [
            'document'      => $document,
            'employee'      => $document->employee,
            'issued'        => $document->issued,
            'config'        => $document->companydocumentconfigs,
            'company'       => $document->companydocumentconfigs->company,
            'signatureData' => $signatureData,
        ])->setPaper('a4');

        $password = Carbon::parse(
            $document->employee->date_of_birth
        )->format('Ymd');
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->getCanvas();

        if (method_exists($canvas, 'get_cpdf')) {
            $cpdf = $canvas->get_cpdf();
            $cpdf->setEncryption($password, $password);
        }

        $filename = str_replace('/', '-', $document->document_number) . '.pdf';
        return $pdf->download($filename);
    }
    public function generateOfferingLetter(string $employeeId)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    if (!$user || !$user->hasPermissionTo('ManageDocument')) {
        abort(403);
    }

    $employee = \App\Models\Employee::with([
        'primaryStore',
        'primaryDepartment',
        'primaryPosition',
        'grading',
        'salary' => fn($q) => $q->latest('effective_date'),
    ])->findOrFail($employeeId);

    // Pastikan status_employee DW atau On Job Training
    if (!in_array($employee->status_employee, ['DW', 'On Job Training'])) {
        abort(403, 'Employee tidak eligible untuk Offering Letter');
    }

    return view('pages.document.offering-letter-form', [
        'employee' => $employee,
    ]);
}

public function storeOfferingLetter(Request $request, string $employeeId)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    if (!$user || !$user->hasPermissionTo('ManageDocument')) {
        abort(403);
    }

    $employee = \App\Models\Employee::with([
        'primaryStore',
        'primaryDepartment',
        'primaryPosition',
        'grading',
    ])->findOrFail($employeeId);

    if (!in_array($employee->status_employee, ['DW', 'On Job Training'])) {
        abort(403);
    }

    // Validasi sesuai status_employee
    $rules = [
        'point_of_hire' => 'required|string|max:255',
    ];

    if ($employee->status_employee === 'On Job Training') {
        $rules['basic_salary']        = 'required|numeric|min:0';
        $rules['position_allowance']  = 'required|numeric|min:0';
    } else {
        // DW
        $rules['daily_rate'] = 'required|numeric|min:0';
    }

    $validated = $request->validate($rules);

    DB::transaction(function () use ($employee, $validated, $user) {
        // 1. Simpan ke EmployeeSalary
        \App\Models\EmployeeSalary::create([
            'employee_id'        => $employee->id,
            'basic_salary'       => $validated['basic_salary'] ?? 0,
            'position_allowance' => $validated['position_allowance'] ?? 0,
            'daily_rate'         => $validated['daily_rate'] ?? 0,
            'effective_date'     => $employee->join_date,
            'created_by'         => $user->employee_id,
        ]);

        // 2. Simpan point_of_hire ke Employee
        $employee->update([
            'point_of_hire' => $validated['point_of_hire'],
        ]);

        // 3. Resolve config berdasarkan status_employee
        $nickname = $employee->status_employee === 'DW' ? 'OL-DW' : 'OL-OJT';

        $config = \App\Models\Companydocumentconfigs::with(['documenttypes'])
            ->where('company_id', $employee->company_id)
            ->whereHas('documenttypes', fn($q) => $q->where('nickname', $nickname))
            ->where('is_active', true)
            ->firstOrFail();

        // 4. Cek apakah sudah ada offering letter
        $existing = Documents::where('company_document_config_id', $config->id)
            ->where('employee_id', $employee->id)
            ->exists();

        if ($existing) {
            abort(422, 'Offering Letter sudah pernah di-generate untuk employee ini');
        }

        // 5. Buat dokumen
        $headHR = \App\Models\User::role('HeadHR')
            ->whereHas('Employee', fn($q) => $q->where('status', 'Active'))
            ->first();

        Documents::create([
            'company_document_config_id' => $config->id,
            'employee_id'                => $employee->id,
            'issued_by'                  => $headHR?->employee_id ?? $user->employee_id,
            'issued_date'                => $employee->join_date,
            'status'                     => 'draft',
        ]);
    });

    return redirect()
        ->route('documents.index')
        ->with('success', 'Offering Letter berhasil di-generate');
}

public function downloadOfferingLetter(string $documentId)
{
    /** @var \App\Models\User|null $user */
    $user = auth()->user();

    if (!$user || !$user->hasPermissionTo('ManageDocument')) {
        abort(403);
    }

    $document = Documents::with([
        'employee.primaryStore',
        'employee.primaryDepartment',
        'employee.primaryPosition',
        'employee.grading',
        'employee.salaries' => fn($q) => $q->latest('effective_date'),
        'issued.position',
        'companydocumentconfigs.company',
        'companydocumentconfigs.documenttypes',
    ])->findOrFail($documentId);

    $viewName = $document->companydocumentconfigs->documenttypes->view_name;

    $allowedViews = [
        'documents.types.OLOJT',
        'documents.types.OLDW',
    ];

    if (!in_array($viewName, $allowedViews)) {
        abort(403);
    }

    $signatureData = null;
    if ($document->issued && $document->issued->signature) {
        $path = 'employees-signatures-photos/' . basename($document->issued->signature);
        if (Storage::disk('s3')->exists($path)) {
            $signatureData = 'data:image/png;base64,' . base64_encode(
                Storage::disk('s3')->get($path)
            );
        }
    }

    $salary = \App\Models\EmployeeSalary::where('employee_id', $document->employee_id)
    ->where('effective_date', $document->employee->join_date)
    ->latest('effective_date')
    ->first();

    $pdf = Pdf::loadView($viewName, [
        'document'      => $document,
        'employee'      => $document->employee,
        'issued'        => $document->issued,
        'config'        => $document->companydocumentconfigs,
        'company'       => $document->companydocumentconfigs->company,
        'salary'        => $salary,
        'signatureData' => $signatureData,
    ])->setPaper('a4');

    $password = Carbon::parse($document->employee->date_of_birth)->format('Ymd');
    $domPdf   = $pdf->getDomPDF();
    $canvas   = $domPdf->getCanvas();

    if (method_exists($canvas, 'get_cpdf')) {
        $cpdf = $canvas->get_cpdf();
        $cpdf->setEncryption($password, $password);
    }

    $filename = str_replace('/', '-', $document->document_number) . '.pdf';
    return $pdf->download($filename);
}
}
