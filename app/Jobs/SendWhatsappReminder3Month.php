<?php

namespace App\Jobs;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsappReminder3Month implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan jika job gagal.
     */
    public int $tries = 3;

    /**
     * Timeout job dalam detik.
     */
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
  public function handle(): void
{
    if (!config('services.whatsapp.enabled', false)) {
        Log::info('[WhatsApp] Notifikasi WhatsApp dinonaktifkan via konfigurasi.');
        return;
    }

    $twoMonthsAgo = Carbon::today()->subMonths(2);

    $users = User::with('employee')
        ->whereDate('created_at', $twoMonthsAgo)
        ->whereHas('employee', function ($q) {
            $q->where('status', 'Active'); // langsung filter kolom status
        })
        ->get();

    if ($users->isEmpty()) {
        Log::info('[WhatsApp] Tidak ada user yang tepat 2 bulan masa percobaan hari ini.');
        return;
    }

    $message = $this->buildMessage($users);

    $this->sendWhatsapp($message);
}

private function buildMessage($users): string
{
    $today = Carbon::today();

    $lines = [];
    $lines[] = " Reminder Masa Percobaan Karyawan";
    $lines[] = " Tanggal Hari Ini: " . $today->translatedFormat('d F Y');
    $lines[] = "─────────────────────────";
    $lines[] = " Karyawan berikut telah memasuki bulan ke-2 masa percobaan.";
    $lines[] = "Mohon HR untuk mereview karyawan dibawah ini.";
    $lines[] = "─────────────────────────";

    foreach ($users as $index => $user) {
        $employee     = $user->employee;
        $name         = $employee?->employee_name ?? $user->name ?? 'Tidak diketahui';
        $pengenal         = $employee?->employee_pengenal ?? $user->name ?? 'Tidak diketahui';
        $company         = $employee?->company->name ?? $user->name ?? 'Tidak diketahui';
        $department         = $employee?->department->department_name ?? $user->name ?? 'Tidak diketahui';
        $location         = $employee?->store->name ?? $user->name ?? 'Tidak diketahui';
        $statusEmp    = $employee?->status_employee ?? '-'; // PKWT / DW / On Job Training
        $status       = $employee?->status ?? '-';          // Active / Inactive / dll
        $createdAt    = Carbon::parse($user->created_at);
        $endProbation = $createdAt->copy()->addMonths(3);

        $lines[] = "";
        $lines[] = ($index + 1) .". *{$name}*";
        $lines[] = "NIP : {$pengenal}";
        $lines[] = "Perusahaan : {$company}";
        $lines[] = "Departemen : {$department}";
        $lines[] = "Lokasi : {$location}";
        $lines[] = "Status Karyawan : {$statusEmp}";
        $lines[] = "Status : {$status}";
        $lines[] = "Mulai Bergabung : " . $createdAt->translatedFormat('d F Y');
        $lines[] = "Akhir Masa Coba : " . $endProbation->translatedFormat('d F Y');
        $lines[] = "Sisa Waktu : *1 bulan lagi*";
    }
    $lines[] = "";
    $lines[] = "─────────────────────────";
    $lines[] = "Total: *{$users->count()} karyawan* yang hampir menyentuh 3 bulan.";

    return implode("\n", $lines);
}
    /**
     * Kirim pesan ke WhatsApp via endpoint.
     */
    private function sendWhatsapp(string $message): void
{
    $endpoint = config('services.whatsapp.endpoint');
    $groupId  = config('services.whatsapp.group_id');
    try {
        $response = Http::timeout(30)->post($endpoint, [
            'group_id' => $groupId,  // ubah dari 'to'
            'text'     => $message,  // ubah dari 'message'
        ]);
        if ($response->successful()) {
            Log::info('[WhatsApp] Notifikasi berhasil dikirim.', [
                'status' => $response->status(),
            ]);
        } else {
            Log::error('[WhatsApp] Gagal mengirim notifikasi.', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
        }
    } catch (\Throwable $e) {
        Log::error('[WhatsApp] Exception saat mengirim notifikasi.', [
            'error' => $e->getMessage(),
        ]);
        throw $e;
    }
}
} 