<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Position extends Model
{
    use HasFactory;

    protected $table = 'position_tables';
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
        'name',
        'status',
        'reason_reject',
        'approval_1',
        'approval_2',
        'role_summary',
        'key_respon',
        'qualifications',
        'work_location'
    ];
    public function approval1()
    {
        return $this->belongsTo(Structuresnew::class, 'approval_1', 'id');
    }
    public function approval2()
    {
        return $this->belongsTo(Structuresnew::class, 'approval_2', 'id');
    }
}
