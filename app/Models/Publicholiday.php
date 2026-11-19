<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
class PublicHoliday extends Model
{
    /** Head Office, Holding, Distribution Center: PH di hari Minggu hangus. */
    public const STATIC_STORE_IDS = [
        '019623ad-de58-7368-8873-e3cbff2b0aff',
        '019a230d-6146-7001-848d-046ccdbdf163',
        '019963a7-cdb8-7002-b10b-163645c199d0',
    ];

    /**
     * PH yang berlaku bagi karyawan dalam rentang: [tanggal => keterangan].
     * DW tidak mendapat PH, tipe PH mengikuti agama, dan PH Minggu store statis hangus.
     */
    public static function datesForEmployee(?Employee $employee, string $startDate, string $endDate): array
    {
        if (!$employee || strtoupper($employee->status_employee ?? '') === 'DW') {
            return [];
        }

        $types = $employee->religion === 'Hindu' ? ['Hindu', 'All'] : ['Non Hindu', 'All'];
        $isStaticStore = in_array($employee->primaryStore()->value('stores_tables.id'), self::STATIC_STORE_IDS, true);

        $dates = [];
        foreach (self::whereBetween('date', [$startDate, $endDate])->whereIn('type', $types)->get() as $ph) {
            if ($isStaticStore && $ph->date->isSunday()) {
                continue;
            }
            $dates[$ph->date->toDateString()] ??= $ph->remark;
        }

        return $dates;
    }

    protected $table = 'ph';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'type',
        'date',
        'remark',
    ];

    protected $casts = [
        'date' => 'date',
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
}