<?php

namespace App\Jobs;

use App\Mail\DocumentMail;
use App\Models\Documents;
use App\Services\DocumentPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendDocumentEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public array $backoff = [30, 90, 180];

    /**
     * TESTING ONLY: while this feature is being tested, every document email is
     * redirected here instead of the employee's real address. Remove this override
     * (send to $document->employee->email instead) before this goes live.
     */
    private const TEST_RECIPIENT_EMAIL = 'edwinsirait@asianbay.co.id';

    public function __construct(public string $documentId)
    {
    }

    public function handle(DocumentPdfService $service): void
    {
        $document = Documents::with([
            'employee',
            'employee.position',
            'issued.position',
            'companydocumentconfigs.company',
            'companydocumentconfigs.documenttypes',
        ])->find($this->documentId);

        if (!$document) {
            Log::warning("SendDocumentEmailJob: document {$this->documentId} not found");
            return;
        }

        if (!$document->employee) {
            Log::warning("SendDocumentEmailJob: document {$this->documentId} has no employee");
            return;
        }

        $tempPath = null;

        try {
            ['pdf' => $pdf, 'filename' => $filename] = $service->generate($document);

            $tempPath = storage_path('app/temp-documents/' . uniqid('doc_', true) . '.pdf');

            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            $pdf->save($tempPath);

            Mail::to(self::TEST_RECIPIENT_EMAIL)
                ->send(new DocumentMail($document, $tempPath));

            Log::info("SendDocumentEmailJob: document sent successfully", [
                'document_id' => $this->documentId,
                'sent_to'     => self::TEST_RECIPIENT_EMAIL,
            ]);
        } catch (\Throwable $e) {
            Log::error("SendDocumentEmailJob: failed to send document", [
                'document_id' => $this->documentId,
                'error'       => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("SendDocumentEmailJob failed permanently for document {$this->documentId}: " . $exception->getMessage());
    }
}
