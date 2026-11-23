<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Mengecilkan dan membersihkan gambar unggahan sebelum disimpan:
 * - arah foto diperbaiki dari EXIF (foto HP sering tersimpan miring);
 * - sisi terpanjang dibatasi MAX_SIDE piksel;
 * - disimpan ulang (encode ulang) sehingga metadata (EXIF: lokasi GPS, model HP) dan data
 *   sisipan di luar gambar ikut terbuang;
 * - foto biasa jadi JPEG kualitas QUALITY (latar transparan jadi putih);
 *   mode $keepAlpha (tanda tangan, logo) jadi PNG yang tetap transparan.
 *
 * Imagick dipakai lebih dulu: JPEG bisa dibaca langsung di ukuran kecil (jpeg:size), jadi foto
 * 12-48 MP dari kamera HP tidak menghabiskan memory_limit PHP. GD hanya cadangan, dan dilewati
 * kalau memorinya tidak cukup. Kalau encode ulang gagal, file asli yang dikembalikan
 * ('method' => 'none'). File selain gambar (PDF, dokumen) dikembalikan apa adanya.
 * Ekstensi selalu dari isi file, tidak pernah dari nama file kiriman pengguna.
 */
class ImageCompressor
{
    public const MAX_SIDE = 1600;
    public const QUALITY  = 72;

    private const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * @return array{contents: string, extension: string, mime: string, original_bytes: int, bytes: int, method: string}
     */
    public function compress(UploadedFile $file, bool $keepAlpha = false): array
    {
        $path = $file->getRealPath();

        return $this->compressBlob(
            (string) file_get_contents($path),
            (string) $file->getMimeType(),
            $path,
            $keepAlpha,
            $file->guessExtension() ?: 'bin',
        );
    }

    /**
     * Sama dengan compress(), untuk isi file di memori (mis. tanda tangan base64 dari canvas).
     *
     * @return array{contents: string, extension: string, mime: string, original_bytes: int, bytes: int, method: string}
     */
    public function compressBlob(string $original, ?string $mime = null, ?string $path = null, bool $keepAlpha = false, ?string $extension = null): array
    {
        $mime ??= (string) (new \finfo(FILEINFO_MIME_TYPE))->buffer($original);
        $extension ??= ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? 'bin';
        $asIs = [
            'contents' => $original, 'extension' => strtolower($extension), 'mime' => $mime,
            'original_bytes' => strlen($original), 'bytes' => strlen($original), 'method' => 'none',
        ];

        if (!in_array($mime, self::IMAGE_MIMES, true)) {
            return $asIs;
        }

        try {
            [$out, $method] = extension_loaded('imagick')
                ? [$this->withImagick($original, $keepAlpha), 'imagick']
                : [$this->withGd($path, $original, $mime, $keepAlpha), 'gd'];
        } catch (\Throwable $e) {
            Log::warning('Image re-encode failed, storing original', ['error' => $e->getMessage()]);
            return $asIs;
        }

        if (!$out) {
            return $asIs;
        }

        // Hasil encode ulang selalu dipakai, walaupun sedikit lebih besar: metadata dan data sisipan harus terbuang
        return [
            'contents' => $out,
            'extension' => $keepAlpha ? 'png' : 'jpg',
            'mime' => $keepAlpha ? 'image/png' : 'image/jpeg',
            'original_bytes' => strlen($original), 'bytes' => strlen($out), 'method' => $method,
        ];
    }

    private function withImagick(string $blob, bool $keepAlpha = false): ?string
    {
        $im = new \Imagick();
        // Petunjuk ke decoder JPEG: cukup baca di sekitar 2x ukuran target, hemat memori dan waktu
        $im->setOption('jpeg:size', (self::MAX_SIDE * 2) . 'x' . (self::MAX_SIDE * 2));
        $im->readImageBlob($blob);
        $im = $im->getImage(); // hanya frame pertama (GIF/HEIC berlapis)

        $this->imagickAutoOrient($im);

        // Latar putih untuk gambar transparan (kecuali tanda tangan/logo yang harus tetap transparan)
        if (!$keepAlpha && $im->getImageAlphaChannel()) {
            $im->setImageBackgroundColor('white');
            $im = $im->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
        }

        if (max($im->getImageWidth(), $im->getImageHeight()) > self::MAX_SIDE) {
            $im->thumbnailImage(self::MAX_SIDE, self::MAX_SIDE, true);
        }

        if ($keepAlpha) {
            $im->setImageFormat('png');
            $im->setOption('png:compression-level', '9');
        } else {
            $im->setImageFormat('jpeg');
            $im->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $im->setImageCompressionQuality(self::QUALITY);
            $im->setInterlaceScheme(\Imagick::INTERLACE_PLANE);
        }
        $im->stripImage(); // buang EXIF (lokasi GPS kamera, model HP)

        $out = $im->getImageBlob();
        $im->clear();
        return $out;
    }

    private function imagickAutoOrient(\Imagick $im): void
    {
        switch ($im->getImageOrientation()) {
            case \Imagick::ORIENTATION_BOTTOMRIGHT: $im->rotateImage('#000', 180); break;
            case \Imagick::ORIENTATION_RIGHTTOP:    $im->rotateImage('#000', 90);  break;
            case \Imagick::ORIENTATION_LEFTBOTTOM:  $im->rotateImage('#000', -90); break;
        }
        $im->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
    }

    private function withGd(?string $path, string $blob, string $mime, bool $keepAlpha = false): ?string
    {
        if (!function_exists('imagecreatefromstring')) return null;

        $size = @getimagesizefromstring($blob);
        if (!$size) return null;
        [$w, $h] = $size;

        // GD memakai ~5 byte per piksel; ditambah kemungkinan rotasi (salinan kedua). Jangan sampai PHP mati.
        $needed = (int) ($w * $h * 5 * 2) + 32 * 1024 * 1024;
        if (!$this->ensureMemory($needed)) {
            Log::info('Image too large for GD, storing original', ['width' => $w, 'height' => $h]);
            return null;
        }

        $img = @imagecreatefromstring($blob);
        if (!$img) return null;

        if ($path && $mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $angle = [3 => 180, 6 => -90, 8 => 90][(int) (@exif_read_data($path)['Orientation'] ?? 1)] ?? 0;
            if ($angle && ($rotated = imagerotate($img, $angle, 0))) {
                imagedestroy($img);
                $img = $rotated;
            }
        }

        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1, self::MAX_SIDE / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));

        $canvas = imagecreatetruecolor($nw, $nh);
        if ($keepAlpha) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        } else {
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        }
        imagecopyresampled($canvas, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($img);

        ob_start();
        if ($keepAlpha) {
            imagepng($canvas, null, 9);
        } else {
            imageinterlace($canvas, true);
            imagejpeg($canvas, null, self::QUALITY);
        }
        $out = (string) ob_get_clean();
        imagedestroy($canvas);
        return $out;
    }

    /** Naikkan memory_limit sementara kalau perlu; false kalau tetap tidak cukup. */
    private function ensureMemory(int $bytes): bool
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') return true;

        $toBytes = function (string $v): int {
            $n = (int) $v;
            return match (strtolower(substr(trim($v), -1))) { 'g' => $n << 30, 'm' => $n << 20, 'k' => $n << 10, default => $n };
        };
        $available = $toBytes($limit) - memory_get_usage(true);
        if ($available >= $bytes) return true;

        $wanted = memory_get_usage(true) + $bytes;
        return @ini_set('memory_limit', (string) $wanted) !== false && $toBytes((string) ini_get('memory_limit')) >= $wanted;
    }
}
