<?php

namespace App\Mail;

use App\Models\Documents;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email berita acara ke company_email, dikirim lewat queue (BeritaAcaraService::mail).
 * Event: approval_needed (giliran approver), returned (ke pengaju), approved (ke pengaju), cc (tembusan),
 * reminder (pengingat dari HR ke atasan / approver berikutnya / alamat lain), shared (tautan BA yang sudah selesai).
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
            'reminder'        => "Pengingat: {$type} {$document->document_number} menunggu persetujuan",
            'shared'          => "{$type} {$document->document_number}",
        ];
        // Untuk pengingat: siapa yang sedang ditunggu
        $waiting = \App\Models\DocumentApproval::with('employee:id,employee_name')->where('document_id', $document->id)
            ->where('status', 'pending')->orderByDesc('round')->first();

        return $this->subject($titles[$this->event] ?? $type)
            ->view('emails.berita-acara', [
                'document' => $document,
                'type'     => $type,
                'subject'  => $subject,
                'name'     => $this->recipientName,
                'event'    => $this->event,
                'note'     => $this->note,
                'url'      => route('beritaacara.show', $document->id),
                'waiting'  => $waiting?->employee?->employee_name,
                'waitingSince' => $waiting?->updated_at,
            ]);
    }
}
