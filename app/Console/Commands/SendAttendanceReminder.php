<?php

// namespace App\Console\Commands;

// use App\Models\Roster;
// use App\Models\PushToken;
// use Carbon\Carbon;
// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Log;

// class SendAttendanceReminder extends Command
// {
//     protected $signature = 'attendance:send-reminder {type : checkin atau checkout}';
//     protected $description = 'Kirim reminder absensi berdasarkan roster hari ini';

//     public function handle()
//     {
//         $type = $this->argument('type');
//         $today = Carbon::today()->toDateString();

//         // Ambil semua roster hari ini yang jadwalnya Work
//         $rosters = Roster::whereDate('date', $today)
//             ->where('day_type', 'Work')
//             ->with(['employee.user'])
//             ->get();

//         if ($rosters->isEmpty()) {
//             $this->info('Tidak ada jadwal kerja hari ini.');
//             return;
//         }

//         $messages = [];

//         foreach ($rosters as $roster) {
//             $employee = $roster->employee;
//             if (!$employee || !$employee->user) continue;

//             $tokens = PushToken::where('user_id', $employee->user->id)
//                 ->where('is_active', true)
//                 ->pluck('token')
//                 ->toArray();

//             if (empty($tokens)) continue;

//             if ($type === 'checkin') {
//                 $title = 'Reminder Absensi Masuk 🕐';
//                 $body = "Selamat pagi {$employee->employee_name}! Jangan lupa absen masuk hari ini.";
//             } else {
//                 $title = 'Reminder Absensi Pulang 🏠';
//                 $body = "Hei {$employee->employee_name}! Jangan lupa absen pulang sebelum meninggalkan kantor.";
//             }

//             foreach ($tokens as $token) {
//                 $messages[] = [
//                     'to' => $token,
//                     'sound' => 'default',
//                     'title' => $title,
//                     'body' => $body,
//                     'data' => ['type' => $type],
//                 ];
//             }
//         }

//         if (empty($messages)) {
//             $this->info('Tidak ada push token yang terdaftar untuk dikirim.');
//             return;
//         }

//         // Kirim ke Expo Push API (max 100 per request)
//         $chunks = array_chunk($messages, 100);

//         foreach ($chunks as $chunk) {
//             try {
//                 $response = Http::post('https://exp.host/--/api/v2/push/send', $chunk);
//                 Log::info('Expo push response: ' . $response->body());
//             } catch (\Exception $e) {
//                 Log::error('Gagal kirim push notification: ' . $e->getMessage());
//             }
//         }

//         $this->info('Berhasil mengirim ' . count($messages) . ' notifikasi.');
//     }
// }
namespace App\Console\Commands;

use App\Models\Roster;
use App\Models\PushToken;
use App\Models\User;
use App\Models\AttendanceLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// class SendAttendanceReminder extends Command
// {
//     protected $signature = 'attendance:send-reminder';
//     protected $description = 'Kirim reminder absensi berdasarkan jam shift roster hari ini';

//     public function handle()
//     {
//         $now = Carbon::now('Asia/Makassar');
//         $today = $now->toDateString();

//         // Ambil semua roster hari ini yang jadwalnya Work dan punya shift
//         $rosters = Roster::whereDate('date', $today)
//             ->where('day_type', 'Work')
//             ->whereNotNull('shift_id')
//             ->with(['employee.user', 'shift'])
//             ->get();

//         if ($rosters->isEmpty()) {
//             $this->info('Tidak ada jadwal kerja hari ini.');
//             return;
//         }

//         $messages = [];

//         foreach ($rosters as $roster) {
//             $employee = $roster->employee;
//             $shift = $roster->shift;

//             if (!$employee || !$employee->user || !$shift) continue;

//             $userId = $employee->user->id;

//             // Parse jam shift dari roster
//             $shiftStart = Carbon::parse($today . ' ' . $shift->start_time, 'Asia/Makassar');
//             $shiftEnd = Carbon::parse($today . ' ' . $shift->end_time, 'Asia/Makassar');

//             // Reminder checkin: 30 menit sebelum jam masuk
//             $reminderCheckin = $shiftStart->copy()->subMinutes(30);

//             // Reminder checkout: 15 menit sebelum jam pulang
//             $reminderCheckout = $shiftEnd->copy()->subMinutes(15);

//             // Cek apakah sekarang dalam window 15 menit dari waktu reminder
//             $isCheckinTime = $now->between(
//                 $reminderCheckin,
//                 $reminderCheckin->copy()->addMinutes(15)
//             );

//             $isCheckoutTime = $now->between(
//                 $reminderCheckout,
//                 $reminderCheckout->copy()->addMinutes(15)
//             );

//             if (!$isCheckinTime && !$isCheckoutTime) continue;

//             // Cek apakah karyawan sudah absen (hindari kirim notif yang tidak perlu)
//             if ($isCheckinTime) {
//                 $alreadyCheckin = AttendanceLog::where('employee_id', $employee->id)
//                     ->whereDate('logged_at', $today)
//                     ->where('type', 'checkin')
//                     ->where('status', '!=', 'rejected')
//                     ->exists();

//                 if ($alreadyCheckin) continue; // Sudah checkin, skip
//             }

//             if ($isCheckoutTime) {
//                 $alreadyCheckout = AttendanceLog::where('employee_id', $employee->id)
//                     ->whereDate('logged_at', $today)
//                     ->where('type', 'checkout')
//                     ->where('status', '!=', 'rejected')
//                     ->exists();

//                 if ($alreadyCheckout) continue; // Sudah checkout, skip
//             }

//             $tokens = PushToken::where('user_id', $userId)
//                 ->where('is_active', true)
//                 ->pluck('token')
//                 ->toArray();

//             if (empty($tokens)) continue;

//             if ($isCheckinTime) {
//                 $title = 'Reminder Absensi Masuk 🕐';
//                 $body = "Selamat pagi {$employee->employee_name}! Shift Anda mulai jam {$shiftStart->format('H:i')}. Jangan lupa absen masuk.";
//             } else {
//                 $title = 'Reminder Absensi Pulang 🏠';
//                 $body = "Hei {$employee->employee_name}! Shift Anda selesai jam {$shiftEnd->format('H:i')}. Jangan lupa absen pulang.";
//             }

//             foreach ($tokens as $token) {
//                 $messages[] = [
//                     'to' => $token,
//                     'sound' => 'default',
//                     'title' => $title,
//                     'body' => $body,
//                     'data' => [
//                         'type' => $isCheckinTime ? 'checkin' : 'checkout',
//                     ],
//                 ];
//             }
//         }

//         if (empty($messages)) {
//             $this->info('Tidak ada notifikasi yang perlu dikirim saat ini.');
//             return;
//         }

//         // Kirim ke Expo Push API (max 100 per request)
//         $chunks = array_chunk($messages, 100);

//         foreach ($chunks as $chunk) {
//             try {
//                 $response = Http::post('https://exp.host/--/api/v2/push/send', $chunk);
//                 Log::info('Expo push response: ' . $response->body());
//                 $this->info('Terkirim ' . count($chunk) . ' notifikasi.');
//             } catch (\Exception $e) {
//                 Log::error('Gagal kirim push notification: ' . $e->getMessage());
//             }
//         }

//         $this->info('Total ' . count($messages) . ' notifikasi berhasil diproses.');
//     }
// }

class SendAttendanceReminder extends Command
{
    protected $signature = 'attendance:send-reminder';
    protected $description = 'Kirim reminder absensi berdasarkan jam shift roster hari ini';

    public function handle()
    {
        $now = Carbon::now('Asia/Makassar');
        $today = $now->toDateString();

        // Ambil semua roster hari ini yang jadwalnya Work dan punya shift
        $rosters = Roster::whereDate('date', $today)
            ->where('day_type', 'Work')
            ->whereNotNull('shift_id')
            ->with(['employee:id,employee_name', 'shift:id,shift_name,start_time,end_time'])
            ->get();

        if ($rosters->isEmpty()) {
            $this->info('Tidak ada jadwal kerja hari ini.');
            return;
        }

        // Preload semua user sekaligus (hindari N+1 query)
        $employeeIds = $rosters->pluck('employee_id')->filter()->unique();

        $usersByEmployeeId = User::whereIn('employee_id', $employeeIds)
            ->get(['id', 'employee_id'])
            ->keyBy('employee_id');

        // Preload semua push token sekaligus
        $userIds = $usersByEmployeeId->pluck('id');
        $pushTokensByUserId = PushToken::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->get(['user_id', 'token'])
            ->groupBy('user_id');

        // Preload attendance hari ini (checkin & checkout) sekaligus
        $todayAttendance = AttendanceLog::whereIn('employee_id', $employeeIds)
            ->whereDate('logged_at', $today)
            ->where('status', '!=', 'rejected')
            ->get(['employee_id', 'type'])
            ->groupBy('employee_id');

        $messages = [];

        foreach ($rosters as $roster) {
            $employee = $roster->employee;
            $shift = $roster->shift;

            if (!$employee || !$shift) continue;

            $user = $usersByEmployeeId->get($employee->id);
            if (!$user) continue;

            $tokens = $pushTokensByUserId->get($user->id);
            if (!$tokens || $tokens->isEmpty()) continue;

            // Parse jam shift
            $shiftStart = Carbon::parse($today . ' ' . $shift->start_time, 'Asia/Makassar');
            $shiftEnd = Carbon::parse($today . ' ' . $shift->end_time, 'Asia/Makassar');

            // Window reminder: 30 menit sebelum masuk, 15 menit sebelum pulang
            $reminderCheckin = $shiftStart->copy()->subMinutes(30);
            $reminderCheckout = $shiftEnd->copy()->subMinutes(15);

            $isCheckinTime = $now->between($reminderCheckin, $reminderCheckin->copy()->addMinutes(15));
            $isCheckoutTime = $now->between($reminderCheckout, $reminderCheckout->copy()->addMinutes(15));

            if (!$isCheckinTime && !$isCheckoutTime) continue;

            // Cek status absensi dari preloaded data (tanpa query tambahan)
            $employeeAttendance = $todayAttendance->get($employee->id, collect());

            if ($isCheckinTime) {
                $alreadyCheckin = $employeeAttendance->where('type', 'checkin')->isNotEmpty();
                if ($alreadyCheckin) continue;

                $title = 'Reminder Absensi Masuk 🕐';
                $body = "Selamat pagi {$employee->employee_name}! Shift {$shift->shift_name} Anda mulai jam {$shiftStart->format('H:i')}. Jangan lupa absen masuk.";
                $type = 'checkin';
            } else {
                $alreadyCheckout = $employeeAttendance->where('type', 'checkout')->isNotEmpty();
                if ($alreadyCheckout) continue;

                $title = 'Reminder Absensi Pulang 🏠';
                $body = "Hei {$employee->employee_name}! Shift {$shift->shift_name} selesai jam {$shiftEnd->format('H:i')}. Jangan lupa absen pulang.";
                $type = 'checkout';
            }

            foreach ($tokens as $pushToken) {
                $messages[] = [
                    'to' => $pushToken->token,
                    'sound' => 'default',
                    'title' => $title,
                    'body' => $body,
                    'data' => ['type' => $type],
                ];
            }
        }

        if (empty($messages)) {
            $this->info('Tidak ada notifikasi yang perlu dikirim saat ini.');
            return;
        }

        // Kirim ke Expo Push API dalam batch maksimal 100 per request
        $successCount = 0;
        $chunks = array_chunk($messages, 100);

        foreach ($chunks as $chunk) {
            try {
                $response = Http::timeout(10)
                    ->post('https://exp.host/--/api/v2/push/send', $chunk);

                if ($response->successful()) {
                    $successCount += count($chunk);
                } else {
                    Log::warning('Expo push partial failure: ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Gagal kirim push notification: ' . $e->getMessage());
            }
        }

        $this->info("Berhasil mengirim {$successCount} dari " . count($messages) . " notifikasi.");
    }
}