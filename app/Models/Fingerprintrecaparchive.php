<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Fingerprintrecaparchive extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }

    protected $table = 'fingerprint_recap_archives';

    protected $fillable = [
        'employee_id',
        'employee_name',
        'store_name',
        'period_start',
        'period_end',
        'total_hari_kerja',
        'total_hari_telat',
        'remarks',
        'archived_by',
    ];

    protected $casts = [
        'period_start'     => 'date',
        'period_end'       => 'date',
        'total_hari_kerja' => 'integer',
        'total_hari_telat' => 'integer',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}