<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ManualRecapEvidence extends Model
{
    use HasUuids;

    protected $table = 'manual_recap_evidences';

    protected $fillable = [
        'manual_recap_log_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function log()
    {
        return $this->belongsTo(ManualRecapLog::class, 'manual_recap_log_id');
    }

    // Accessor: URL publik untuk akses file
    public function getFileUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    // Accessor: ukuran file dalam format human-readable
    public function getFileSizeFormattedAttribute(): string
    {
        $size = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }

    // Accessor: apakah file adalah gambar
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }
}