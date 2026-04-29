<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Model ManualRecapLog
 *
 * Skema baru: 1 row per FILE (bukan per log lagi).
 * Kalau HR upload 3 file untuk 2 karyawan × 5 hari = 30 rows.
 *
 * Kolom:
 * - id           : uuid (primary key)
 * - employee_id  : uuid (foreign key ke employees_tables)
 * - reason       : text (alasan klarifikasi)
 * - file_name    : varchar (nama asli file)
 * - file_path    : varchar (path relatif ke storage)
 * - mime_type    : varchar (jenis file)
 * - file_size    : bigint (ukuran dalam bytes)
 */
class ManualRecapLog extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'manual_recap_logs';

    protected $fillable = [
        'employee_id',
        'reason',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // ─────────────────────────────────────────────────
    // RELATIONS
    // ─────────────────────────────────────────────────

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    // ─────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────

    /**
     * URL publik untuk akses file (butuh storage:link)
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    /**
     * Format ukuran file ke human-readable
     */
    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '-';

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Cek apakah file ini gambar
     */
    public function getIsImageAttribute(): bool
    {
        return $this->mime_type && str_starts_with($this->mime_type, 'image/');
    }
}