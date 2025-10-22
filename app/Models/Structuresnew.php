<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Structuresnew extends Model
{
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
    protected $table = 'structures';
    protected $fillable = [
        'company_id',
        'department_id',
        'store_id',
        'position_id',
        'parent_id',
        'structure_code',
        'is_manager_store',
        'is_manager_department',
    ];
    protected $casts = [
        'is_manager_store' => 'boolean',
        'is_manager_department' => 'boolean',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id', 'id');
    }
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id', 'id');
    }
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
    public function parent()
    {
        return $this->belongsTo(Structuresnew::class, 'parent_id', 'id');
    }
public function children()
{
    return $this->hasMany(Structuresnew::class, 'parent_id', 'id');
}

}


