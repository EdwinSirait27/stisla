<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NoXSSInput implements Rule
{
    protected $failedReason = '';

    public function passes($attribute, $value)
    {
        // Jika input kosong atau null
        if ($value === null || $value === '') {
            return true;
        }

        // Jika input adalah array, lakukan validasi rekursif
        if (is_array($value)) {
            foreach ($value as $item) {
                if (!$this->validateItem($item)) {
                    return false;
                }
            }
            return true;
        }

        // Jika input adalah string, langsung validasi
        return $this->validateItem($value);
    }

    private function validateItem($value)
    {
        // Pastikan item adalah string
        if (!is_string($value)) {
            $this->failedReason = "Input harus berupa string.";
            return false;
        }

        $xssPatterns = [
            '/<\s*script/i',               // Deteksi awal tag script
            '/script\s*>/i',               // Deteksi akhir tag script
            '/<script.*>.*<\/script>/is',  // Tag script lengkap
            '/javascript:/i',              // Protokol javascript
            '/onerror\s*=/i',              // Event error
            '/onclick\s*=/i',              // Event click
            '/onload\s*=/i',               // Event load
            '/&lt;script/i',               // Script dalam encoded HTML
            '/alert\s*\(/i',               // Fungsi alert
            '/eval\s*\(/i',                // Fungsi eval berbahaya
            '/document\.cookie/i',         // Akses cookie
        ];

        $decodedValue = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

        // Cek semua pola XSS
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $value) || preg_match($pattern, $decodedValue)) {
                $this->failedReason = "Pola XSS terdeteksi.";
                return false;
            }
        }

        // Cek tag HTML
        if (strip_tags($value) !== $value) {
            $this->failedReason = "Terdeteksi tag HTML berbahaya.";
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->failedReason ?: 'Input mengandung potensi serangan XSS.';
    }
}
