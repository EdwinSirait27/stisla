<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Documents extends Model
{
    use HasFactory;    
    protected $table = 'documents';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'company_document_config_id',
        'employee_id',
        'issued_by',
        'issued_date',
        'status',
        'file_path',
        'document_number',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // UUID
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }

            // Auto generate document_number
            if (!$model->document_number) {
                $model->document_number = self::generateDocumentNumber($model);
            }
        });
    }

    protected static function generateDocumentNumber(Documents $model): string
    {
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        $now        = now();
        $year       = $now->year;
        $romanMonth = $romanMonths[$now->month];

        // Ambil data relasi
        $config       = Companydocumentconfigs::with(['documenttypes', 'company'])->find($model->company_document_config_id);
        $documentCode = strtoupper(str_replace(' ', '-', $config->documenttypes->nickname));
        $companyNick  = strtoupper($config->company->nickname);

        // Hitung increment — reset tiap tahun baru
        $count = self::whereHas('companydocumentconfigs', function ($q) use ($config) {
                $q->where('company_id', $config->company_id)
                  ->where('document_type_id', $config->document_type_id);
            })
            ->whereYear('issued_date', $year)
            ->count();

        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Format: 001/SURAT-PERINGATAN-PT-ABC/V/2026
        return "{$sequence}/{$documentCode}-{$companyNick}/{$romanMonth}/{$year}";
    }

    // Relationships
    public function companydocumentconfigs()
    {
        return $this->belongsTo(Companydocumentconfigs::class, 'company_document_config_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    // ⚠️ Fix: issued_by harusnya pakai kolom issued_by, bukan employee_id
    public function issued()
    {
        return $this->belongsTo(Employee::class, 'issued_by', 'id');
    }
}