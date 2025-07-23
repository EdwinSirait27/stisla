<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditedFingerprint extends Model
{
    // use HasFactory;
     protected $table = 'edited_fingerprints';
  protected $fillable = [
        'pin',
        'employee_name',
        'position_name',
        'store_name',
        'scan_date',
        'duration',
        'in_1', 'device_1',
        'in_2', 'device_2',
        'in_3', 'device_3',
        'in_4', 'device_4',
        'in_5', 'device_5',
        'in_6', 'device_6',
        'in_7', 'device_7',
        'in_8', 'device_8',
        'in_9', 'device_9',
        'in_10', 'device_10',
        'attachment',
    ];
}
