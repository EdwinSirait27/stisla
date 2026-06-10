<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Models\Sktype;
use App\Models\Company;
class SkLetter extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'sk_letters';
    public $incrementing = false;
    protected $keyType = 'string';
    public static function getRomanMonth($month): string
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];
        return $map[$month] ?? '';
    }
 
    protected static function booted()
{
    static::creating(function ($model) {

        if (!$model->getKey()) {
            $model->{$model->getKeyName()} = Uuid::uuid7()->toString();
        }

        $year  = now()->year;
        $month = now()->month;

        $romanMonth = self::getRomanMonth($month);

        $skType  = Sktype::find($model->sk_type_id);
        $company = Company::find($model->company_id);

        $skTypeName  = strtoupper($skType?->nickname ?? 'SK');
        $companyName = strtoupper($company?->nickname ?? 'COMP');

        $count = self::whereYear('created_at', $year)
            ->where('company_id', $model->company_id)
            ->lockForUpdate()
            ->count() + 1;

        $number = str_pad($count, 3, '0', STR_PAD_LEFT);

        $model->sk_number =
            "{$number}/{$skTypeName}-{$companyName}/{$romanMonth}/{$year}";
    });
}
    protected $casts = [
        'effective_date'   => 'date',
        'inactive_date'    => 'date',
        'approver_1_at'    => 'datetime',
        'approver_2_at'    => 'datetime',
        'approver_3_at'    => 'datetime',
        ];
    protected $fillable = [
        'sk_type_id',
        'title',
        'sk_number',
        'company_id',
        'approver_1',
        'approver_2',
        'approver_3',
        'approver_1_at',
        'approver_2_at',
        'approver_3_at',
        'effective_date',
        'location',
        'inactive_date',
        'status',
        'menetapkan_text',
        'notes',
    ];
     public function setTitleAttribute($value)
    {
        $this->attributes['title'] = strtoupper($value);
    }
    public static function getStatusOptions(): array
    {
        return [
            'Draft'                      => 'Draft',
            'Cancelled'                  => 'Cancelled',
            'Approved HR'                => 'Approved HR',
            'Approved Director'          => 'Approved Director',
            'Approved Managing Director' => 'Approved Managing Director',
        ];
    }
    public function skType()
    {
        return $this->belongsTo(Sktype::class, 'sk_type_id', 'id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
   
    public function approver1()
    {
        return $this->belongsTo(Employee::class, 'approver_1', 'id');
    }
    public function approver2()
    {
        return $this->belongsTo(Employee::class, 'approver_2', 'id');
    }
    public function approver3()
    {
        return $this->belongsTo(Employee::class, 'approver_3', 'id');
    }
    public function employees()
    {
        return $this->belongsToMany(
            Employee::class,
            'sk_letter_employees',
            'sk_letter_id',
            'employee_id'
        )->using(SkLetterEmployee::class)
            ->withPivot([
                'id',
                'previous_structure_id',
                'new_structure_id',
                'position_id',
                'group_id',
                'grading_id',
                'department_id',
                'basic_salary',
                'positional_allowance',
                'daily_rate',
                'notes',
            ])
            ->withTimestamps();
    }
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'sk_letter_id', 'id');
    }
        public function menimbang()
{
    return $this->hasMany(SkMenimbang::class, 'sk_letter_id', 'id')
                ->orderBy('order_no');
}

public function mengingat()
{
    return $this->hasMany(SkMengingat::class, 'sk_letter_id', 'id')
                ->orderBy('order_no');
}

public function keputusan()
{
    return $this->hasMany(SkKeputusan::class, 'sk_letter_id', 'id')
                ->orderBy('order_no');
}
    public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logFillable()
        ->useLogName('SkLetter')
        ->setDescriptionForEvent(function (string $eventName) {

            $actor = auth()->user()?->employee?->employee_name
                ?? auth()->user()?->name
                ?? 'system';

            $changes  = $this->getChanges();
            $original = $this->getOriginal();

            $relationNames = [
                'sk_type_id' => fn($id) => optional(Sktype::find($id))->sk_name,
                'company_id' => fn($id) => optional(Company::find($id))->name,
                'approver_1' => fn($id) => optional(Employee::find($id))->employee_name,
                'approver_2' => fn($id) => optional(Employee::find($id))->employee_name,
                'approver_3' => fn($id) => optional(Employee::find($id))->employee_name,
            ];

            $fieldLabels = [
                'sk_type_id'     => 'SK Type',
                'title'          => 'Title',
                'sk_number'      => 'SK Number',
                'company_id'     => 'Company',
                'approver_1'     => 'Approver HR',
                'approver_2'     => 'Approver Director',
                'approver_3'     => 'Approver Managing Director',
                'approver_1_at'  => 'Approved HR At',
                'approver_2_at'  => 'Approved Director At',
                'approver_3_at'  => 'Approved Managing Director At',
                'effective_date' => 'Effective Date',
                'inactive_date'  => 'Inactive Date',
                'status'         => 'Status',
                'menetapkan_text'=> 'Menetapkan',
                'location'       => 'Location',
                'notes'          => 'Notes',
            ];

            $details = collect($changes)
                ->map(function ($new, $field) use ($original, $relationNames, $fieldLabels) {

                    $old = $original[$field] ?? 'null';

                    $label = $fieldLabels[$field]
                        ?? ucfirst(str_replace('_', ' ', $field));

                    if (isset($relationNames[$field])) {

                        $oldLabel = $relationNames[$field]($old) ?? $old;
                        $newLabel = $relationNames[$field]($new) ?? $new;

                        return "{$label}: {$oldLabel} → {$newLabel}";
                    }

                    return "{$label}: {$old} → {$new}";
                })
                ->implode(', ');

            return match ($eventName) {

                'created' =>
                    "SK Letter created by {$actor}. {$details}",

                'updated' =>
                    "SK Letter updated by {$actor}. {$details}",

                'deleted' =>
                    "SK Letter deleted by {$actor}",

                default =>
                    "SK Letter {$eventName} by {$actor}",
            };
        });
}

}

    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logFillable()
    //         ->logOnlyDirty()
    //         ->dontSubmitEmptyLogs()
    //         ->useLogName('SkLetter')
    //         ->setDescriptionForEvent(function (string $eventName) {
    //             $actor = auth()->user()?->employee?->employee_name
    //                 ?? auth()->user()?->name
    //                 ?? 'system';
    //             $changes  = $this->getChanges();
    //             $original = $this->getOriginal();
    //             $relationNames = [
    //                 'sk_type_id'  => fn($id) => optional(Sktype::find($id))->sk_name,
    //                 'company_id'  => fn($id) => optional(Company::find($id))->name,
    //                 'approver_1'  => fn($id) => optional(Employee::find($id))->employee_name,
    //                 'approver_2'  => fn($id) => optional(Employee::find($id))->employee_name,
    //                 'approver_3'  => fn($id) => optional(Employee::find($id))->employee_name,
    //             ];
    //             $fieldLabels = [
    //                 'sk_type_id'     => 'SK type',
    //                 'title'            => 'Title',        // tambah
    // 'sk_number'        => 'SK number',    // tambah
    //                 'company_id'     => 'Company',
    //                 'approver_1'     => 'Approver HR',
    //                 'approver_2'     => 'Approver Director',
    //                 'approver_3'     => 'Approver Managing Director',
    //                 'approver_1_at'  => 'Approved HR at',
    //                 'approver_2_at'  => 'Approved Director at',
    //                 'approver_3_at'  => 'Approved Managing Director at',
    //                 'effective_date' => 'Effective date',
    //                 'inactive_date'  => 'Inactive date',
    //                 'status'         => 'Status',
    //                 'menetapkan_text'  => 'Menetapkan',  
    //                   'location'         => 'Location',
    //                 'notes'          => 'Notes',
    //             ];
    //             $changesInfo = '';
    //             if ($eventName === 'updated' && !empty($changes)) {
    //                 $details = collect($changes)
    //                     ->map(function ($new, $field) use ($original, $relationNames, $fieldLabels) {
    //                         $old   = $original[$field] ?? 'null';
    //                         $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));

    //                         if (isset($relationNames[$field])) {
    //                             $oldLabel = $relationNames[$field]($old) ?? $old;
    //                             $newLabel = $relationNames[$field]($new) ?? $new;
    //                             return "{$label}: {$oldLabel} → {$newLabel}";
    //                         }

    //                         if ($old == $new) return null;
    //                         return "{$label}: {$old} → {$new}";
    //                     })
    //                     ->filter()
    //                     ->values()
    //                     ->implode(', ');

    //                 $changesInfo = $details ? "Changes: {$details}" : '';
    //             }

    //             return "SK Letter has been {$eventName} by {$actor}. {$changesInfo}";
    //         });
    // }