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

        $query = Documents::with('employee:id,employee_name')->
        select([
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
}
