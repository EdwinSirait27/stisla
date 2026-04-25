<?php

namespace App\Jobs;

use App\Models\Manualrecaplog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk mengirim notifikasi WhatsApp ke Head HR & IT
 * ketika HR melakukan Manual Recap Absensi.
 *
 * STATUS: Skeleton — provider WhatsApp belum ditentukan.
 * Activate salah satu provider di method sendViaProvider()
 * setelah Anda memutuskan (Fonnte / Twilio / Meta Cloud API).
 */
class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 30;

    protected ManualRecapLog $log;

    public function __construct(ManualRecapLog $log)
    {
        $this->log = $log;
    }

    public function handle(): void
    {
        try {
            // Load relasi yang dibutuhkan
            $this->log->load(['employee.store', 'hr', 'evidences']);

            $recipients = $this->getRecipients();

            if (empty($recipients)) {
                Log::warning('WhatsApp: tidak ada penerima terdaftar', [
                    'log_id' => $this->log->id,
                ]);
                return;
            }

            $message = $this->buildMessage();

            $sentCount = 0;
            foreach ($recipients as $recipient) {
                if ($this->sendViaProvider($recipient['phone'], $message)) {
                    $sentCount++;
                }
            }

            $this->log->update([
                'whatsapp_sent'    => $sentCount > 0,
                'whatsapp_sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('SendWhatsAppNotificationJob failed', [
                'log_id' => $this->log->id,
                'error'  => $e->getMessage(),
            ]);

            $this->log->update(['notification_error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Ambil daftar penerima (Head HR & Head IT).
     * TODO: Implementasi setelah Anda pilih cara storage kontak.
     */
    protected function getRecipients(): array
    {
        // Placeholder — return kosong agar tidak kirim apa-apa
        return [];
    }

    /**
     * Buat isi pesan WhatsApp.
     */
    protected function buildMessage(): string
    {
        $employee    = $this->log->employee;
        $empName     = $employee->employee_name ?? 'Unknown';
        $storeName   = optional($employee->store)->name ?? '-';
        $timeIn      = $this->log->time_in ?: '-';
        $timeOut     = $this->log->time_out ?: '-';
        $date        = $this->log->date->format('d/m/Y');
        $hrName      = $this->log->hr_name ?? 'System';
        $reason      = $this->log->reason;
        $evidenceCount = $this->log->evidences->count();

        return "🔔 *MANUAL RECAP ABSENSI*\n\n"
             . "Telah dilakukan penambahan data absensi manual:\n\n"
             . "👤 *Karyawan:* {$empName}\n"
             . "🏢 *Store:* {$storeName}\n"
             . "📅 *Tanggal:* {$date}\n"
             . "🕐 *Time In:* {$timeIn}\n"
             . "🕐 *Time Out:* {$timeOut}\n\n"
             . "📝 *Alasan:*\n{$reason}\n\n"
             . "📎 *Bukti Terlampir:* {$evidenceCount} file\n\n"
             . "👨‍💼 *Diajukan oleh HR:* {$hrName}\n"
             . "⏰ *Waktu:* " . $this->log->submitted_at->format('d/m/Y H:i:s') . "\n\n"
             . "_Pesan otomatis dari sistem HRX_";
    }

    /**
     * Kirim WA via provider.
     * TODO: Activate salah satu provider di bawah.
     */
    protected function sendViaProvider(string $phone, string $message): bool
    {
        // ┌────────────────────────────────────────────────────────┐
        // │ OPSI 1: FONNTE (populer di Indonesia)                 │
        // └────────────────────────────────────────────────────────┘
        /*
        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/send', [
            'target'  => $phone,
            'message' => $message,
        ]);
        return $response->successful() && ($response->json('status') ?? false);
        */

        // ┌────────────────────────────────────────────────────────┐
        // │ OPSI 2: TWILIO                                         │
        // └────────────────────────────────────────────────────────┘
        /* ... */

        // ┌────────────────────────────────────────────────────────┐
        // │ OPSI 3: META Cloud API                                 │
        // └────────────────────────────────────────────────────────┘
        /* ... */

        Log::info('WhatsApp would be sent (provider belum diset)', [
            'to'      => $phone,
            'message' => substr($message, 0, 100) . '...',
        ]);
        return false;
    }
}