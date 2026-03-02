<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Skemployee extends Model
{
   use HasFactory;
    protected $table = 'sk_employee';
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
        'sk_type_id',
        'sk_template_id',
        'store_id',
        'sk_number',
        'title',
        'issued_date',
        'effective_date',
        'header_text',
        'consideration',
        'legas_basis',
        'decision_text',
        'footer_text',
        'status',
        'approver_1',
        'approver_2'
        ];
    public function sktype()
    {
        return $this->belongsTo(Sktype::class, 'sk_type_id', 'id');
    }
    public function sktemplate()
    {
        return $this->belongsTo(Sktemplate::class, 'sk_template_id', 'id');
    }
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }
    public function approver_1()
    {
        return $this->belongsTo(User::class, 'approver_1', 'id');
    }
    public function approver_2()
    {
        return $this->belongsTo(User::class, 'approver_2', 'id');
    }
}
