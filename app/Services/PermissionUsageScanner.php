<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

/**
 * Cari di file mana sebuah permission dipakai (routes, controller, view).
 * Dipakai halaman Permissions supaya admin tahu dampak sebelum mengganti nama.
 *
 * Yang dihitung hanya konteks permission: string persis 'nama' / "nama",
 * atau nama di dalam middleware permission:a|b. Hasilnya petunjuk, bukan jaminan
 * (mis. nama yang dibentuk dinamis tidak terdeteksi).
 */
class PermissionUsageScanner
{
    private const DIRS = ['routes', 'app', 'resources/views'];
    private const CACHE_KEY = 'permission-usage-scan';
    private const CACHE_SECONDS = 300;

    /** @return array<string, array<int, array{file:string, line:int, text:string}>> nama → daftar lokasi */
    public function usages(array $names): array
    {
        $index = Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, fn() => $this->buildIndex());

        $result = [];
        foreach ($names as $name) {
            $result[$name] = $index[$name] ?? [];
        }
        return $result;
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** Satu kali baca semua file, kumpulkan setiap token yang tampak seperti nama permission. */
    private function buildIndex(): array
    {
        $index = [];
        $base = base_path() . DIRECTORY_SEPARATOR;

        foreach (self::DIRS as $dir) {
            $path = base_path($dir);
            if (!is_dir($path)) {
                continue;
            }
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if (!str_ends_with($file->getFilename(), '.php')) {
                    continue;
                }
                $relative = str_replace($base, '', $file->getPathname());
                foreach (file($file->getPathname()) as $i => $line) {
                    $trim = ltrim($line);
                    if (str_starts_with($trim, '//') || str_starts_with($trim, '*') || str_starts_with($trim, '{{--')) {
                        continue; // abaikan komentar
                    }
                    foreach ($this->tokens($line) as $token) {
                        $index[$token][] = [
                            'file' => $relative,
                            'line' => $i + 1,
                            'text' => trim(mb_strimwidth(trim($line), 0, 140, '…')),
                        ];
                    }
                }
            }
        }

        return $index;
    }

    /** Token permission dalam satu baris. */
    private function tokens(string $line): array
    {
        $found = [];

        // 'nama' atau "nama" persis
        if (preg_match_all('/([\'"])([A-Za-z][A-Za-z0-9_.\-]{1,120})\1/', $line, $m)) {
            foreach ($m[2] as $t) {
                $found[$t] = true;
            }
        }

        // permission:a|b|c (middleware)
        if (preg_match_all('/permission:([A-Za-z0-9_.\-|]+)/', $line, $m)) {
            foreach ($m[1] as $group) {
                foreach (explode('|', $group) as $t) {
                    if ($t !== '') {
                        $found[$t] = true;
                    }
                }
            }
        }

        return array_keys($found);
    }
}
