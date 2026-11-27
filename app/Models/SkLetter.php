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
        'returned_at'      => 'datetime',
        'completed_at'     => 'datetime',
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
        'approver_1_signature',
        'approver_2_signature',
        'approver_3_signature',
        'returned_note',
        'returned_by',
        'returned_at',
        'completed_at',
    ];

    /** Status "sudah disetujui penuh" (nama lama dipertahankan supaya data lama tetap terbaca). */
    public const FINAL_STATUS = 'Approved Managing Director';

    /**
     * Alur approval per SK: Draft → HR mengajukan (approver_1, status Approved HR) → approver_2
     * (status Approved Director, kalau ada approver_3) → approver_3 → disetujui penuh.
     * Langkah aktif: [kolom approver, kolom waktu] atau null kalau tidak sedang menunggu.
     */
    public function currentStep(): ?array
    {
        return match ($this->status) {
            'Approved HR'       => ['approver_2', 'approver_2_at'],
            'Approved Director' => ['approver_3', 'approver_3_at'],
            default             => null,
        };
    }

    public function currentApproverId(): ?string
    {
        $step = $this->currentStep();
        return $step ? $this->{$step[0]} : null;
    }

    public function isFinal(): bool
    {
        return $this->status === self::FINAL_STATUS;
    }

    /** Label untuk tampilan (nilai status di database tetap). */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'Draft'             => $this->returned_at ? 'Dikembalikan' : 'Draft',
            'Approved HR'       => 'Menunggu ' . ($this->approver2?->employee_name ?? 'approver 2'),
            'Approved Director' => 'Menunggu ' . ($this->approver3?->employee_name ?? 'approver 3'),
            self::FINAL_STATUS  => 'Disetujui',
            'Cancelled'         => 'Dibatalkan',
            default             => (string) $this->status,
        };
    }
     public function setTitleAttribute($value)
    {
        $this->attributes['title'] = strtoupper($value);
    }
    public static function getStatusOptions(): array
    {
        return [
            'Draft'                      => 'Draft',
            'Approved HR'                => 'Menunggu approver 2',
            'Approved Director'          => 'Menunggu approver 3',
            'Approved Managing Director' => 'Disetujui',
            'Cancelled'                  => 'Dibatalkan',
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
                'previous_store_id',
                'previous_status_employee',
                'new_position_id',
                'new_department_id',
                'new_store_id',
                'new_grading_id',
                'new_group_id',
                'new_status_employee',
                'applied_at',
                'salary_id',
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

     