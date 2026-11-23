<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Satu pintu untuk menyimpan file unggahan.
 *
 * - Gambar (jpg/png/webp) dikompres & dibersihkan lewat ImageCompressor: diperkecil, EXIF (lokasi GPS) dan
 *   data sisipan terbuang. Foto jadi JPEG; tanda tangan & logo ($keepAlpha) jadi PNG transparan.
 * - PDF / dokumen disimpan apa adanya.
 * - Nama file dibuat server: slug nama + waktu + acak, ekstensi dari ISI file (bukan nama kiriman pengguna),
 *   sehingga file "bukti.html" berisi JPEG tidak pernah tersimpan sebagai .html.
 * - ContentType di storage diset sesuai isi file.
 *
 * Validasi tipe & ukuran tetap di controller (aturan `mimes` / `max`); kelas ini menolak lagi kalau
 * ekstensi hasil deteksi tidak ada di daftar $allowed.
 */
class UploadStorage
{
    public const IMAGES    = ['jpg', 'jpeg', 'png', 'webp'];
    public const DOCUMENTS = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    /** Batas tanda tangan dari canvas (base64), setelah didekode. */
    private const SIGNATURE_MAX_BYTES = 1024 * 1024;

    public function __construct(private ImageCompressor $compressor)
    {
    }

    /**
     * Simpan file unggahan. Mengembalikan path relatif di disk.
     *
     * @param string[] $allowed ekstensi yang boleh (hasil deteksi isi file)
     */
    public function put(UploadedFile $file, string $folder, string $baseName, array $allowed = self::IMAGES, string $disk = 's3', bool $keepAlpha = false, string $field = 'file'): string
    {
        $packed = $this->compressor->compress($file, $keepAlpha);
        $this->assertAllowed($packed['extension'], $allowed, $file->getClientOriginalName(), $field);

        return $this->write($packed, $folder, $baseName, $disk);
    }

    /**
     * Tanda tangan dari canvas: "data:image/png;base64,...". Hanya PNG asli yang diterima,
     * lalu di-encode ulang jadi PNG transparan. Melempar ValidationException ke $field kalau tidak valid.
     */
    public function putSignatureDataUrl(string $dataUrl, string $folder, string $baseName, string $field = 'signature', string $disk = 's3'): string
    {
        $raw = preg_replace('#^data:image/png;base64,#', '', trim($dataUrl));
        $raw = str_replace(' ', '+', (string) $raw);

        if (strlen($raw) > self::SIGNATURE_MAX_BYTES * 4 / 3 + 4) {
            throw ValidationException::withMessages([$field => 'Tanda tangan terlalu besar.']);
        }

        $binary = base64_decode($raw, true);
        $info   = $binary !== false ? @getimagesizefromstring($binary) : false;
        if ($binary === false || !$info || ($info['mime'] ?? null) !== 'image/png') {
            throw ValidationException::withMessages([$field => 'Tanda tangan tidak valid. Gambar ulang lalu simpan lagi.']);
        }

        $packed = $this->compressor->compressBlob($binary, 'image/png', null, true, 'png');

        return $this->write($packed, $folder, $baseName, $disk);
    }

    private function assertAllowed(string $extension, array $allowed, string $clientName, string $field): void
    {
        $allowed = array_map('strtolower', $allowed);
        if (in_array('jpg', $allowed, true) || in_array('jpeg', $allowed, true)) {
            $allowed = array_unique(array_merge($allowed, ['jpg', 'jpeg']));
        }
        if (!in_array(strtolower($extension), $allowed, true)) {
            Log::warning('Upload rejected: detected type not allowed', ['detected' => $extension, 'client_name' => $clientName]);
            throw ValidationException::withMessages([$field => 'Tipe file tidak diizinkan.']);
        }
    }

    /** @param array{contents: string, extension: string, mime: string, original_bytes: int, bytes: int, method: string} $packed */
    private function write(array $packed, string $folder, string $baseName, string $disk): string
    {
        $slug = Str::slug($baseName) ?: 'file';
        $path = trim($folder, '/') . '/' . $slug . '-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(6)) . '.' . $packed['extension'];

        if (!Storage::disk($disk)->put($path, $packed['contents'], ['ContentType' => $packed['mime']])) {
            throw new \RuntimeException("Gagal menyimpan file ke {$disk}:{$path}");
        }

        if ($packed['method'] !== 'none') {
            Log::info('Upload compressed', [
                'path' => $path, 'from' => $packed['original_bytes'], 'to' => $packed['bytes'], 'method' => $packed['method'],
            ]);
        }

        return $path;
    }
}
