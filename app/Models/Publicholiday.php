<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    protected $table = 'ph';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'type',
        'date',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}