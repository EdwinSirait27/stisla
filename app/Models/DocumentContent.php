<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/** Isi dokumen yang ditulis pengaju (berita acara): perihal, waktu, tempat, uraian. */
class DocumentContent extends Model
{
    protected $table = 'document_contents';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    protected $casts = ['store_ids' => 'array', 'event_at' => 'datetime', 'submitted_at' => 'datetime', 'completed_at' => 'datetime'];
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
