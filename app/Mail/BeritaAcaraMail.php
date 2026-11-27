<?php

namespace App\Mail;

use App\Models\Documents;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email berita acara ke company_email, dikirim lewat queue (BeritaAcaraService::mail).
 * Event: approval_needed (giliran approver), returned (ke pengaju), approved (ke pengaju), cc (tembusan).
 * Isi dokumen tidak ikut di email; penerima membukanya di HRX (akses dicek di sana).
 */
class BeritaAcaraMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $documentId,
        public string $recipientName,
        public string $event,
        public ?string $note = null,
    ) {
    }

    public function build()
    {
        $document = Documents::with(['content', 'employee', 'companydocumentconfigs.documenttypes', 'companydocumentconfigs.company'])
            ->findOrFail($this->documentId);
        $type = $document->companydocumentconfigs?->documenttypes?->document_name ?? 'Berita acara';
        $subject = $document->content?->subject ?? '';

        $titles = [
            'approval_needed' => "Perlu persetujuan Anda: {$type} {$document->document_number}",
            'returned'        => "Dikembalikan untuk revisi: {$type} {$document->document_number}",
            'approved'        => "Disetujui: {$type} {$document->document_number}",
            'cc'              => "Tembusan: {$type} {$document->document_number}",
        ];

        return $this->subject($titles[$this->event] ?? $type)
            ->view('emails.berita-acara', [
                'document' => $document,
                'type'     => $type,
                'subject'  => $subject,
                'name'     => $this->recipientName,
                'event'    => $this->event,
                'note'     => $this->note,
                'url'      => route('beritaacara.show', $document->id),
            ]);
    }
}
