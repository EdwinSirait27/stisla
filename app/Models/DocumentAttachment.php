<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/** Lampiran dokumen (file di S3). */
class DocumentAttachment extends Model
{
    protected $table = 'document_attachments';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];

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

}
