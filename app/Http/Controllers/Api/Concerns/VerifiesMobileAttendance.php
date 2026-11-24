<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Employee;
use App\Services\ImageCompressor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Verifikasi absen mobile yang dipakai bersama oleh absensi biasa (AttendanceController)
 * dan absensi lembur (OvertimeAttendanceController): lokasi per attendance_type + wajah DeepFace.
 */
trait VerifiesMobileAttendance
{
    /**
     * Tentukan lokasi absen sesuai attendance_type karyawan.
     *
     * - store_bound       : harus di radius store utama.
     * - flexible          : harus di radius salah satu store yang ditugaskan (dipilih yang terdekat).
     * - location_flexible : selalu diterima; kalau di luar radius semua store yang ditugaskan,
     *                       absen ditandai is_offsite + catatan supaya HR bisa membedakan.
     *
     * Hasil: ['store_id','store_name','distance','within','offsite','note'] atau ['error','code'].
     */
    private function resolveLocation(Employee $employee, float $lat, float $lng, ?string $type = null): array
    {
        $type = $type ?? ($employee->attendance_type ?: Employee::ATTENDANCE_STORE_BOUND);

        $stores = $type === Employee::ATTENDANCE_STORE_BOUND
            ? $employee->store()->wherePivot('is_primary', true)->get()
            : $employee->store()->get();
        $stores = $stores->filter(fn($s) => $s->latitude !== null && $s->longitude !== null && $s->geofence_radius);

        // Store terdekat, diutamakan yang radiusnya mencakup posisi karyawan
        $nearest = $stores
            ->map(fn($s) => ['store' => $s, 'distance' => $this->calculateDistance($lat, $lng, $s->latitude, $s->longitude)])
            ->sortBy(fn($c) => [$c['distance'] > $c['store']->geofence_radius ? 1 : 0, $c['distance']])
            ->first();
        $within = $nearest && $nearest['distance'] <= $nearest['store']->geofence_radius;

        if ($type === Employee::ATTENDANCE_LOCATION_FLEXIBLE) {
            $note = null;
            if (!$within) {
                $note = $nearest
                    ? 'Absen di luar lokasi kerja (bebas lokasi): ' . number_format($nearest['distance'], 0, ',', '.') . 'm dari ' . $nearest['store']->name
                    : 'Absen di luar lokasi kerja (bebas lokasi): store yang ditugaskan belum punya titik lokasi';
            }
            return [
                'store_id'   => $nearest['store']->id ?? null,
                'store_name' => $nearest['store']->name ?? null,
                'distance'   => $nearest['distance'] ?? null,
                'within'     => $within,
                'offsite'    => !$within,
                'note'       => $note,
            ];
        }

        // store_bound & flexible: wajib di dalam radius
        if (!$nearest) {
            return ['error' => 'Lokasi store Anda belum diset, hubungi admin', 'code' => 'STORE_LOCATION_MISSING'];
        }
        if (!$within) {
            $where = $type === Employee::ATTENDANCE_FLEXIBLE ? ' dari store terdekat (' . $nearest['store']->name . ')' : ' dari lokasi';
            return [
                'error' => 'Absen ditolak: Anda berada ' . number_format($nearest['distance'], 0, ',', '.') . "m{$where}, di luar radius yang diizinkan ({$nearest['store']->geofence_radius}m)",
                'code'  => 'OUTSIDE_GEOFENCE',
            ];
        }

        return [
            'store_id'   => $nearest['store']->id,
            'store_name' => $nearest['store']->name,
            'distance'   => $nearest['distance'],
            'within'     => true,
            'offsite'    => false,
            'note'       => null,
        ];
    }

    /**
     * Selfie yang dikirim ke DeepFace: diperkecil (sisi terpanjang ImageCompressor::MAX_SIDE), arah
     * diperbaiki dari EXIF, metadata dibuang. Aplikasi versi baru sudah mengirim foto kecil; ini menjaga
     * aplikasi versi lama / foto besar supaya upload ke DeepFace dan prosesnya tidak lambat.
     * Kalau kompresi gagal, ImageCompressor mengembalikan file asli.
     */
    private function selfieForVerification($attendancePhoto): string
    {
        $started = microtime(true);
        $packed  = app(ImageCompressor::class)->compress($attendancePhoto);
        Log::info('Selfie untuk DeepFace', [
            'from_kb' => round($packed['original_bytes'] / 1024),
            'to_kb'   => round($packed['bytes'] / 1024),
            'method'  => $packed['method'],
            'seconds' => round(microtime(true) - $started, 2),
        ]);
        return $packed['contents'];
    }

    private function verifyFace($attendancePhoto, $referencePhotoPath)
    {
        $startTotal = microtime(true);

        try {
            $startCache = microtime(true);
            // Foto referensi lama bisa berukuran penuh (diunggah sebelum ada kompresi): dikecilkan sekali lalu di-cache
            $cacheKey = 'reference_photo_v2_' . md5($referencePhotoPath);
            $referenceContent = Cache::remember($cacheKey, now()->addHours(6), function () use ($referencePhotoPath) {
                $raw = Storage::disk('s3')->get($referencePhotoPath);
                return $raw === null ? null : app(ImageCompressor::class)->compressBlob($raw)['contents'];
            });
            Log::info('Waktu ambil foto referensi: ' . round(microtime(true) - $startCache, 2) . 's');

            $startHttp = microtime(true);
            // Di bawah batas waktu request (120 detik) supaya error-nya tertangani, bukan fatal
            $response = Http::connectTimeout(10)->timeout(75)
                ->attach('photo', $this->selfieForVerification($attendancePhoto), 'attendance.jpg')
                ->attach('reference_photo', $referenceContent, 'reference.jpg')
                ->post(config('services.deepface.url') . '/verify');
            Log::info('Waktu request ke DeepFace: ' . round(microtime(true) - $startHttp, 2) . 's');
            Log::info('Waktu total verifyFace: ' . round(microtime(true) - $startTotal, 2) . 's');

            $result = $response->json();

            Log::info('DeepFace response', [
                'status' => $response->status(),
                'body'   => $result,
            ]);

            // Error input (400/422) — masalah dari foto, bukan dari layanan
            if ($response->status() === 400 || $response->status() === 422) {
                $errorMessage = $result['message'] ?? $result['error'] ?? null;

                $userMessage = match (true) {
                    str_contains($errorMessage ?? '', 'Face could not be detected')
                    => 'Wajah tidak terdeteksi, pastikan wajah terlihat jelas dan pencahayaan cukup',
                    str_contains($errorMessage ?? '', 'Multiple faces')
                    => 'Terdeteksi lebih dari satu wajah, pastikan hanya ada satu wajah',
                    str_contains($errorMessage ?? '', 'blurry') || str_contains($errorMessage ?? '', 'Low quality')
                    => 'Foto terlalu buram atau gelap, coba lagi dengan pencahayaan lebih baik',
                    default
                    => 'Foto tidak valid, pastikan wajah terlihat jelas dan coba lagi',
                };

                Log::warning('DeepFace input error', [
                    'raw_message' => $errorMessage,
                    'status'      => $response->status(),
                ]);

                return [
                    'success' => false,
                    'message' => $userMessage,
                    'code'    => 'NO_FACE_DETECTED',
                ];
            }

            // Error server (500, dst) — layanan bermasalah
            if (!$response->successful()) {
                Log::error('DeepFace server error', [
                    'status' => $response->status(),
                    'body'   => $result,
                ]);

                return [
                    'success' => false,
                    'message' => 'Layanan verifikasi wajah sedang bermasalah, coba lagi dalam beberapa saat',
                ];
            }

            return [
                'success'     => true,
                'is_real'     => $result['is_real']     ?? false,
                'is_verified' => $result['is_verified'] ?? false,
                'distance'    => $result['distance']    ?? null,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DeepFace connection timeout: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Layanan verifikasi wajah tidak merespons, coba lagi dalam beberapa saat',
            ];
        } catch (\Exception $e) {
            Log::error('DeepFace verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke layanan verifikasi wajah',
            ];
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLon / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        return round(6371000 * $c, 2); // radius bumi dalam meter
    }
}
