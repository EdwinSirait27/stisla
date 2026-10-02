<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class DocumentTemporaryAssignment extends Model
{
    protected $table = 'document_temporary_assignments';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }

    protected $fillable = [
        'document_id',
        'type',
        'previous_pivot_id',
        'previous_reference_id',
        'previous_name',
        'new_pivot_id',
        'new_reference_id',
        'new_name',
        'was_created',
        'is_primary_change',
        'reverted_at',
    ];

    protected $casts = [
        'was_created'       => 'boolean',
        'is_primary_change' => 'boolean',
        'reverted_at'       => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Documents::class, 'document_id', 'id');
    }
}
