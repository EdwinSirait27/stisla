<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/** Tembusan: karyawan yang perlu tahu ada dokumen ini. */
class DocumentRecipient extends Model
{
    protected $table = 'document_recipients';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    protected $casts = ['read_at' => 'datetime', 'notified_at' => 'datetime'];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }

    public function document()
    {
        return $this->belongsTo(Documents::class, 'document_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
