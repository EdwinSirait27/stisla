<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Crypt;
class Payrolls extends Model
{
    use HasFactory;
    protected $table = 'payrolls_tables';
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
    protected $casts = [
            'month_year' => 'date:Y-m-d',
       
    ];
    protected $fillable = [
        'id',
        'employee_id',
        'attendance',
        'allowance',
        'reamburse',
        'basic_salary',
        'bonus',
        'overtime',
        'overtime_deduction',
        'house_allowance',
        'daily_allowance',
        'meal_allowance',
        'transport_allowance',
        'bpjs_ket',
        'bpjs_kes',
        'debt',
        'punishment',
        'late_fine',
        'deductions',
        'gross_salary',
        'salary',
        'take_home',
        'tax',
        'period',
        'month_year',
        'information',
        'attachment_file',
        'attachment_path',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id','id');
    }
    public function getDailyAllowanceAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }
    public function getGrossSalaryAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }
    public function getReamburseAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }
    public function getAllowanceAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }
    public function getBasicSalaryAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getHouseAllowanceAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }
    public function getOvertimeDeductionAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getMealAllowanceAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getTransportAllowanceAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getBonusAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getOvertimeAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getLateFineAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getPunishmentAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getBpjsKesAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getBpjsKetAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getTaxAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getDebtAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getDeductionsAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getSalaryAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }

    public function getTakeHomeAttribute($value)
    {
        return $value ? (float) Crypt::decryptString($value) : null;
    }
}
