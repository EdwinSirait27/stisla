<?php

namespace App\Mail;

use App\Models\Documents;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Documents $document,
        public string $pdfPath,
    ) {}

    public function build()
    {
        return $this->subject('Document - ' . $this->document->document_number)
            ->view('emails.document')
            ->with([
                'employee'       => $this->document->employee,
                'documentNumber' => $this->document->document_number,
                'documentName'   => $this->document->companydocumentconfigs->documenttypes->document_name ?? '-',
                'companyName'    => $this->document->companydocumentconfigs->company->name ?? 'PT. Asian Bay Development',
            ])
            ->attach($this->pdfPath, [
                'as'   => str_replace('/', '-', $this->document->document_number) . '.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
