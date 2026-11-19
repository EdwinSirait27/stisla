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

    /** @param string|null $causerId user yang menekan Kirim, untuk log aktivitas */
    public function __construct(public string $documentId, public ?string $causerId = null)
    {
    }

    /** Catat pengiriman di log aktivitas dokumen (dipakai halaman Dokumen untuk kolom "Pengiriman"). */
    private function logSend(?Documents $document, string $event, string $description, array $props = []): void
    {
        if (!$document) {
            return;
        }
        $log = activity('document')->performedOn($document)->event($event)->withProperties($props);
        if ($this->causerId && ($causer = \App\Models\User::find($this->causerId))) {
            $log->causedBy($causer);
        }
        $log->log($description);
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

        if (!$document->employee->email) {
            Log::warning("SendDocumentEmailJob: employee for document {$this->documentId} has no email");
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

            Mail::to($document->employee->email)
                ->send(new DocumentMail($document, $tempPath));

            $this->logSend($document, 'sent', "dokumen {$document->document_number} dikirim ke {$document->employee->email}", ['to' => $document->employee->email]);

            Log::info("SendDocumentEmailJob: document sent successfully", [
                'document_id' => $this->documentId,
                'sent_to'     => $document->employee->email,
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
        $document = Documents::find($this->documentId);
        $this->logSend($document, 'send_failed', "pengiriman dokumen {$document?->document_number} gagal: " . \Illuminate\Support\Str::limit($exception->getMessage(), 150), ['error' => $exception->getMessage()]);
    }
}
