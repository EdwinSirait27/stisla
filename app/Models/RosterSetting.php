<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterSetting extends Model
{
    use HasFactory;
    protected $table = 'roster_settings';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $casts = [
        'is_active'   => 'boolean',
    ];

    protected $fillable = [
        'open_day',
        'close_day',
        'is_active'
    ];

    
    public static function isWithinWindow(): bool
    {
        $setting = self::where('is_active', true)->latest()->first();

        if (!$setting) return false; // kalau belum ada setting, tutup by default

        $today     = now()->day;
        $openDay   = $setting->open_day;
        $closeDay  = $setting->close_day;

        // Handle lintas bulan, misal open: 20, close: 5 bulan depan
        if ($openDay <= $closeDay) {
            return $today >= $openDay && $today <= $closeDay;
        }

        // Lintas bulan: misal open 25, close 3
        return $today >= $openDay || $today <= $closeDay;
    }
}
