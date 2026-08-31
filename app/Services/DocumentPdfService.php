<?php

namespace App\Services;

use App\Models\Documents;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DocumentPdfService
{
    protected const ALLOWED_VIEWS = [
        'documents.types.SPK',
        'documents.types.SPPRP',
    ];

    /**
     * Render a Documents record to a password-protected PDF.
     *
     * @return array{pdf: \Barryvdh\DomPDF\PDF, password: string, filename: string}
     */
    public function generate(Documents $document): array
    {
        $document->loadMissing([
            'employee',
            'employee.position',
            'issued.position',
            'companydocumentconfigs.company',
            'companydocumentconfigs.documenttypes',
        ]);

        if (!$document->companydocumentconfigs || !$document->companydocumentconfigs->documenttypes) {
            throw new RuntimeException("Document {$document->id} has no document type configured.");
        }

        $viewName = $document->companydocumentconfigs->documenttypes->view_name;

        if (!in_array($viewName, self::ALLOWED_VIEWS)) {
            throw new RuntimeException("Document type '{$viewName}' is not supported for PDF generation.");
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

        $password = Carbon::parse($document->employee->date_of_birth)->format('Ymd');
        $domPdf   = $pdf->getDomPDF();
        $canvas   = $domPdf->getCanvas();

        if (method_exists($canvas, 'get_cpdf')) {
            $canvas->get_cpdf()->setEncryption($password, $password);
        }

        $filename = str_replace('/', '-', $document->document_number) . '.pdf';

        return [
            'pdf'      => $pdf,
            'password' => $password,
            'filename' => $filename,
        ];
    }
}
