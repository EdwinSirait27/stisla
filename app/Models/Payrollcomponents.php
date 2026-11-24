<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Payrollcomponents extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $attributes = [
        'is_fixed' => false,
        'is_employer_burden' => false,
    ];
    protected $casts = [
        'is_fixed' => 'boolean',
        'is_employer_burden' => 'boolean',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
            }
        });
    }
    protected $table = 'payroll_components';
    protected $fillable = [
        'component_name',
        'type',
        'is_fixed',
        'is_employer_burden',
    ];
    /**
     * Dari mana nilai komponen diambil, PER NAMA KOMPONEN. Ini hanya keterangan untuk halaman
     * Komponen payroll; perhitungannya sendiri ada di PayrollService::calculateComponentAmount()
     * (komponen tetap) dan kolom manual di tabel payrolls (PayrollService::recalculateNet,
     * Payroll/edit). Kalau nama di sana berubah, ubah juga di sini.
     *
     * [mode, keterangan] — mode: 'salary' = otomatis dari data gaji karyawan saat generate,
     * 'manual' = diisi per payroll (kolom di tabel payrolls), 'overtime' = dari lembur Cash disetujui.
     */
    public const SOURCES = [
        'BPJS KESEHATAN'       => ['salary', 'kolom BPJS Kesehatan di data gaji karyawan'],
        'BPJS KETENAGAKERJAAN' => ['salary', 'kolom BPJS Ketenagakerjaan di data gaji karyawan'],
        'MEAL ALLOWANCE'       => ['salary', 'kolom tunjangan makan di data gaji karyawan'],
        'HOUSE ALLOWANCE'      => ['salary', 'kolom tunjangan rumah di data gaji karyawan'],
        'TRANSPORT ALLOWANCE'  => ['salary', 'kolom tunjangan transport di data gaji karyawan'],
        'OVERTIME'             => ['overtime', 'lembur Cash yang disetujui di periode itu, dikali tarif lembur karyawan'],
        'REAMBURSE'            => ['manual', 'kolom reimburse yang diisi HR di setiap payroll'],
        'PUNISHMENT'           => ['manual', 'kolom punishment yang diisi HR di setiap payroll'],
        'SO PUNISHMENT'        => ['manual', 'kolom punishment SO yang diisi HR di setiap payroll'],
        'DEBT'                 => ['manual', 'kolom hutang yang diisi HR di setiap payroll'],
        'TAX'                  => ['manual', 'kolom pajak yang diisi HR di setiap payroll'],
    ];

    /** [mode, keterangan] untuk nama ini, atau null kalau nama tidak dikenal perhitungan payroll. */
    public static function sourceFor(?string $name): ?array
    {
        return self::SOURCES[strtoupper(trim((string) $name))] ?? null;
    }

    public static function getTypeOptions()
    {
        return [
            'Deduction' => 'Deduction',
            'Income' => 'Income'
        ];
    }
    public function setComponentNameAttribute($value)
    {
        $this->attributes['component_name'] = strtoupper($value);
    }
    public function scopeIncome($query)
    {
        return $query->where('type', 'Income');
    }

    public function scopeDeduction($query)
    {
        return $query->where('type', 'Deduction');
    }

    public function scopeFixed($query)
    {
        return $query->where('is_fixed', true);
    }

    public function scopeEmployerBurden($query)
    {
        return $query->where('is_employer_burden', true);
    }

    public function scopeEmployeeBurden($query)
    {
        return $query->where('is_employer_burden', false);
    }
}
