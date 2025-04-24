<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categories extends Model
{
    use HasFactory;
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
    protected $table = 'categories_tables'; 
    protected $fillable = [
        'parent_id',
        'category_code',
        'category_name',
    ];
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }
/**
 * Relasi ke child categories
 */
public function children(): HasMany
{
    return $this->hasMany(Categories::class, 'parent_id');
}
/**
     * Relasi ke parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Categories::class, 'parent_id');
    }

    /**
     * Accessor untuk nama kategori + parent (contoh: "Elektronik > Gadget")
     */
    public function getFullCategoryNameAttribute(): string
    {
        return $this->parent_id 
            ? "{$this->parent->category_name} > {$this->category_name}"
            : $this->category_name;
    }


    public function getAllChildrenIds()
{
    $ids = collect();

    foreach ($this->children as $child) {
        $ids->push($child->id);
        $ids = $ids->merge($child->getAllChildrenIds()); // rekursif
    }

    return $ids;
}

}

