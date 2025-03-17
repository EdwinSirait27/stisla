<?php

namespace App\Models;
use Milon\Barcode\DNS1D;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'description', 'price', 'cost_price', 'stock', 
        'category_id', 'barcode', 'sku', 'image', 'is_active',
        'track_stock', 'min_stock'
    ];
    
    // Relasi ke tabel Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    // Relasi ke tabel SaleItem
    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
    
    // Relasi ke tabel StockAdjustment
    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }
    
    // Relasi ke tabel PurchaseItem
    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }
    
    // Menghasilkan gambar barcode
    public function getBarcodeImageAttribute()
    {
        if(!$this->barcode) {
            return null;
        }
        
        $barcode = new DNS1D();
        return $barcode->getBarcodePNG($this->barcode, 'C128', 2, 50);
    }
    
    // Cek apakah stok menipis
    public function isLowStock()
    {
        return $this->track_stock && $this->stock <= $this->min_stock;
    }
    
    // Generate SKU baru
    public static function generateSKU()
    {
        $lastProduct = self::orderBy('id', 'desc')->first();
        $number = $lastProduct ? intval(substr($lastProduct->sku, 3)) + 1 : 1;
        return 'PRD' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
