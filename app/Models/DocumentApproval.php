<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/** Satu langkah approval berurutan. Lihat STATUS_LABELS untuk arti status. */
class DocumentApproval extends Model
{
    protected $table = 'document_approvals';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    public const STATUS_LABELS = [
        'waiting'  => 'Menunggu giliran',
        'pending'  => 'Perlu persetujuan',
        'approved' => 'Disetujui',
        'returned' => 'Dikembalikan untuk revisi',
        'voided'   => 'Batal (diajukan ulang)',
    ];

    protected $casts = ['acted_at' => 'datetime', 'notified_at' => 'datetime'];
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

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}
