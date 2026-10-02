<?php

namespace App\Jobs;

use App\Services\DocumentPaklaringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePaklaringJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('document');
    }

    public function handle(DocumentPaklaringService $service): void
    {
        $service->generatePaklaring();
    }
}
