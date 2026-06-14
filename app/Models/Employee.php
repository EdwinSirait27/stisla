<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Roster;
use App\Models\Schedule;

class Employee extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'employees_tables';
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
        'employee_name',
        'photos',
        'kk_photos',
        'ktp_photos',
        'signature',
        'employee_pengenal',
        'position_id',
        'company_id',
        'store_id',
        'bank_account_number',
        'banks_id',
        'department_id',
        'fingerprint_id',
        'grading_id',
        'group_id',
        'submissions_id',
        'status_employee',
        'join_date',
        'blood_type',
        'marriage',
        'child',
        'telp_number',
        'nik',
        'gender',
        'date_of_birth',
        'place_of_birth',
        'biological_mother_name',
        'religion',
        'current_address',
        'id_card_address',
        'last_education',
        'institution',
        'npwp',
        'bpjs_kes',
        'bpjs_ket',
        'email',
        'company_email',
        'emergency_contact_name',
        'status',
        'notes',
        'pin',
        'can_approve',
        'end_date',
        'level_id',
        'is_manager',
        'is_manager_store',
        'pending_email',
        'pending_telp_number',
        'total',
        'pending',
        'approved',
        'remaining',
        'structure_id',
        'daily_duit'
    ];
     protected $casts = [
        'can_approve' => 'boolean',
    ];
    public static function getReligionOptions()
    {
        return [
            'Catholic Christian' => 'Catholic Christian',
            'Christian' => 'Christian',
            'Islam' => 'Islam',
            'Hindu' => 'Hindu',
            'Confucian' => 'Confucian',
            'Buddha' => 'Buddha'
        ];
    }
    public static function getMarriageOptions()
    {
        return [
            'Yes' => 'Yes',
            'No' => 'No'
        ];
    }
    public static function getLastEducationOptions()
    {
        return [
            'Elementary School' => 'Elementary School',
            'Junior High School' => 'Junior High School',
            'Senior High School' => 'Senior High School',
            'Vocational School' => 'Vocational School',
            'Bachelor Degree' => 'Bachelor Degree',
            'Masters degree' => 'Masters degree',
            'Diploma I' => 'Diploma I',
            'Diploma II' => 'Diploma II',
            'Diploma III' => 'Diploma III',
            'Diploma IV' => 'Diploma IV'
        ];
    }
    public static function getGenderOptions()
    {
        return [
            'Male' => 'Male',
            'Female' => 'Female'
        ];
    }
    public static function getStatusEmployeeOptions()
    {
        return [
            'PKWT' => 'PKWT',
            'DW' => 'DW',
            'On Job Training' => 'On Job Training'
        ];
    }
    public static function getStatusOptions()
    {
        return [
            'Active' => 'Active',
            'Inactive' => 'Inactive',
            'Pending' => 'Pending',
            'On Leave' => 'On Leave',
            'Mutation' => 'Mutation',
            'Resign' => 'Resign'
        ];
    }
    public static function getBloodTypeOptions()
    {
        return [
            'AB+' => 'AB+',
            'AB' => 'AB',
            'AB-' => 'AB-',
            'A+' => 'A+',
            'A' => 'A',
            'A-' => 'A-',
            'B+' => 'B+',
            'B' => 'B',
            'B-' => 'B-',
            'O+' => 'O+',
            'O' => 'O',
            'O-' => 'O-'
        ];
    }
    public function submission()
    {
        return $this->belongsTo(Submissions::class, 'submissions_id');
    }
    public function grading()
    {
        return $this->belongsTo(Grading::class, 'grading_id');
    }
  
    public function group()
    {
        return $this->belongsTo(Groups::class, 'group_id');
    }
    public function bank()
    {
        return $this->belongsTo(Banks::class, 'banks_id', 'id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function employees()
    {
        return $this->belongsTo(Employee::class, 'level_id');
    }
    public function finger()
    {
        return $this->belongsTo(Fingerprints::class, 'fingerprint_id');
    }
    public function users()
    {
        return $this->hasOne(User::class, 'employee_id');
    }
    public function documents()
    {
        return $this->hasMany(Documents::class, 'employee_id', 'id');
    }
    public function skletters()
    {
        return $this->belongsToMany(
            SkLetter::class,
            'sk_letter_employees',
            'employee_id',
            'sk_letter_id'
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
    public function getLengthOfServiceAttribute()
    {
        // Jika status bukan Active atau Pending → kosong
        if (!in_array($this->status_employee, ['Active', 'Pending'])) {
            return 'Empty';
        }

        // Jika tidak ada join_date → kosong
        if (!$this->join_date) {
            return 'Empty';
        }

        $joinDate = Carbon::parse($this->join_date);
        $now = Carbon::now();

        $years  = $joinDate->diffInYears($now);
        $months = $joinDate->copy()->addYears($years)->diffInMonths($now);

        return "{$years} Years, {$months} Months";
    }
    public function getJoinDateAttribute($value)
    {
        return Carbon::parse($value)->format('y-m-d');
    }
    public function payrolls()
    {
        return $this->hasMany(Payrolls::class, 'employee_id');
    }
    public function submissions()
    {
        return $this->hasMany(Submissions::class, 'employee_id');
    }

    public function approvedSubmissions()
    {
        return $this->hasMany(Submissions::class, 'approved_id ');
    }
    public function submissionstime_toil()
    {
        return $this->hasMany(Submissions::class, 'employee_id', 'id');
    }
    public function getTotalTimeToilAttribute()
    {
        return $this->submissionstime_toil()->sum('time_toil');
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('employee')
            ->setDescriptionForEvent(function (string $eventName) {
                $actor = auth()->user()->employee->employee_name
                    ?? auth()->user()->name
                    ?? 'system';
                $target = $this->employee_name ?? 'Unknown Employee';
                $changes = $this->getChanges();
                $original = $this->getOriginal();
                $relationNames = [
                    'company_id' => fn($id) => optional(Company::find($id))->name,
                    'banks_id' => fn($id) => optional(Banks::find($id))->name,
                    'grading_id' => fn($id) => optional(Grading::find($id))->grading_name,
                    'group_id' => fn($id) => optional(Groups::find($id))->group_name,
                    'level_id' => fn($id) => optional(Employee::find($id))->employee_name,
                ];
                $fieldLabels = [
                    'employee_name' => 'Employee Name',
                    'photos' => 'Photo',
                    'kk_photos' => 'KK Photos',
                    'ktp_photos' => 'KTP Photos',
                    'company_id' => 'Company',
                    'bank_account_number' => 'Bank Account Number',
                    'banks_id' => 'Bank',
                    'grading_id' => 'Grading',
                    'group_id' => 'Group',
                    'status_employee' => 'Employee Status',
                    'join_date' => 'Join',
                    'marriage' => 'Status Marriage',
                    'child' => 'Child',
                    'telp_number' => 'Telephone Number',
                    'nik' => 'ID Card',
                    'gender' => 'Gender',
                    'date_of_birth' => 'Date of Birth',
                    'place_of_birth' => 'Place of Birth',
                    'biological_mother_name' => 'Mothers Name',
                    'religion' => 'Religion',
                    'current_address' => 'Current Address',
                    'id_card_address' => 'ID Card Address',
                    'last_education' => 'Last Education',
                    'institution' => 'Institution',
                    'npwp' => 'NPWP',
                    'bpjs_kes' => 'BPJS Kesehatan',
                    'bpjs_ket' => 'BPJS Ketenagakerjaan',
                    'email' => 'email',
                    'emergency_contact_name' => 'Emergency Contact',
                    'status' => 'status',
                    'notes' => 'Resign notes',
                    'pin' => 'Employee Fingerprints',
                    'end_date' => 'Leave',
                ];
                $changesInfo = '';
                if ($eventName === 'updated' && !empty($changes)) {
                    $details = collect($changes)->map(function ($new, $field) use ($original, $relationNames, $fieldLabels) {
                        $old = $original[$field] ?? 'null';
                        $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                        if (isset($relationNames[$field])) {
                            $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
                            $oldLabel = $relationNames[$field]($old) ?? $old;
                            $newLabel = $relationNames[$field]($new) ?? $new;
                            return "{$label}: {$oldLabel} → {$newLabel}";
                        }
                        if ($old == $new) return null;
                        return "{$label}: {$old} → {$new}";
                    })
                        ->filter()
                        ->values()
                        ->implode(', ');

                    $changesInfo = $details ? "Changes: {$details}" : '';
                }

                return "Employee Data {$target} has been {$eventName}. {$changesInfo}";
            });
    }
    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logFillable()
    //         ->useLogName('employee')
    //         ->setDescriptionForEvent(function (string $eventName) {
    //             $actor = auth()->user()->employee->employee_name
    //                 ?? auth()->user()->name
    //                 ?? 'system';
    //             $target = $this->employee_name ?? 'Unknown Employee';
    //             $changes = $this->getChanges();
    //             $original = $this->getOriginal();
    //             $relationNames = [
    //                 'company_id' => fn($id) => optional(Company::find($id))->name,
    //                 'store_id' => fn($id) => optional(Stores::find($id))->name,
    //                 'position_id' => fn($id) => optional(Position::find($id))->name,
    //                 'banks_id' => fn($id) => optional(Banks::find($id))->name,
    //                 'department_id' => fn($id) => optional(Departments::find($id))->department_name,
    //                 'grading_id' => fn($id) => optional(Grading::find($id))->grading_name,
    //                 'group_id' => fn($id) => optional(Groups::find($id))->group_name,
    //                 'level_id' => fn($id) => optional(Employee::find($id))->employee_name,
    //             ];
    //             $fieldLabels = [
    //                 'employee_name' => 'Employee Name',
    //                 'photos' => 'Photo',
    //                 'kk_photos' => 'KK Photos',
    //                 'ktp_photos' => 'KTP Photos',
    //                 'position_id' => 'Position',
    //                 'company_id' => 'Company',
    //                 'store_id' => 'Location',
    //                 'bank_account_number' => 'Bank Account Number',
    //                 'banks_id' => 'Bank',
    //                 'department_id' => 'Department',
    //                 'grading_id' => 'Grading',
    //                 'group_id' => 'Group',
    //                 'status_employee' => 'Employee Status',
    //                 'join_date' => 'Join',
    //                 'marriage' => 'Status Marriage',
    //                 'child' => 'Child',
    //                 'telp_number' => 'Telephone Number',
    //                 'nik' => 'ID Card',
    //                 'gender' => 'Gender',
    //                 'date_of_birth' => 'Date of Birth',
    //                 'place_of_birth' => 'Place of Birth',
    //                 'biological_mother_name' => 'Mothers Name',
    //                 'religion' => 'Religion',
    //                 'current_address' => 'Current Address',
    //                 'id_card_address' => 'ID Card Address',
    //                 'last_education' => 'Last Education',
    //                 'institution' => 'Institution',
    //                 'npwp' => 'NPWP',
    //                 'bpjs_kes' => 'BPJS Kesehatan',
    //                 'bpjs_ket' => 'BPJS Ketenagakerjaan',
    //                 'email' => 'email',
    //                 'emergency_contact_name' => 'Emergency Contact',
    //                 'status' => 'status',
    //                 'notes' => 'Resign notes',
    //                 'pin' => 'Employee Fingerprints',
    //                 'end_date' => 'Leave',
    //                 'structure_id' => 'Structures',
    //             ];
    //             $changesInfo = '';
    //             if ($eventName === 'updated' && !empty($changes)) {
    //                 $details = collect($changes)->map(function ($new, $field) use ($original, $relationNames, $fieldLabels) {
    //                     $old = $original[$field] ?? 'null';
    //                     $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    //                     if (isset($relationNames[$field])) {
    //                         $label = $fieldLabels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    //                         $oldLabel = $relationNames[$field]($old) ?? $old;
    //                         $newLabel = $relationNames[$field]($new) ?? $new;
    //                         return "{$label}: {$oldLabel} → {$newLabel}";
    //                     }
    //                     if ($old == $new) return null;
    //                     return "{$label}: {$old} → {$new}";
    //                 })
    //                     ->filter()
    //                     ->values()
    //                     ->implode(', ');

    //                 $changesInfo = $details ? "Changes: {$details}" : '';
    //             }

    //             return "Employee Data {$target} has been {$eventName}. {$changesInfo}";
    //         });
    // }

    public function rosters()
    {
        return $this->hasMany(Roster::class, 'employee_id', 'id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'employee_id', 'id');
    }


    public function store()
    {
        return $this->belongsToMany(
            Stores::class,
            'employee_stores',
            'employee_id',
            'store_id'
        )
            ->withPivot('is_primary')
            ->withTimestamps()
            ->using(EmployeeStore::class);
    }
    public function department()
    {
        return $this->belongsToMany(
            Departments::class,
            'employee_departments',
            'employee_id',
            'department_id'
        )
            ->withPivot('is_primary')
            ->withTimestamps()
            ->using(EmployeeDepartment::class);
    }
    public function position()
    {
        return $this->belongsToMany(
            Position::class,
            'employee_positions',
            'employee_id',
            'position_id'
        )
            ->withPivot('is_primary')
            ->withTimestamps()
            ->using(EmployeePosition::class);
    }
  public function atasanList()
{
    return $this->belongsToMany(Employee::class, 'employee_atasans', 'employee_id', 'atasan_id')
        ->withPivot('is_primary')
        ->withTimestamps();
}

public function bawahanList()
{
    return $this->belongsToMany(Employee::class, 'employee_atasans', 'atasan_id', 'employee_id')
        ->withPivot('is_primary')
        ->withTimestamps();
}
   
    public function primaryStore()
    {
        return $this->store()->wherePivot('is_primary', true);
    }

    public function primaryDepartment()
    {
        return $this->department()->wherePivot('is_primary', true);
    }

    public function primaryPosition()
    {
        return $this->position()->wherePivot('is_primary', true);
    }
    
    public function atasanStruktur()
{
    $atasanManual = $this->atasanList()->first();
    if ($atasanManual) return $atasanManual;

    $store = $this->primaryStore()->first();
    $department = $this->primaryDepartment()->first();

    $atasan = Employee::whereHas('store', fn($q) => $q->where('stores_tables.id', $store?->id))
        ->whereHas('department', fn($q) => $q->where('departments_tables.id', $department?->id))
        ->whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
        ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
        ->orderByDesc('grading.level')
        ->select('employees_tables.*')
        ->first();

    if (!$atasan) {
        $atasan = Employee::whereHas('department', fn($q) => $q->where('departments_tables.id', $department?->id))
            ->whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
            ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
            ->orderByDesc('grading.level')
            ->select('employees_tables.*')
            ->first();
    }

    if (!$atasan) {
        $atasan = Employee::whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
            ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
            ->orderByDesc('grading.level')
            ->select('employees_tables.*')
            ->first();
    }

    return $atasan;
}

public function atasan()
{
    $atasanManual = $this->atasanList()->first();
    if ($atasanManual) return $atasanManual;

    $store = $this->primaryStore()->first();
    $department = $this->primaryDepartment()->first();

    $atasan = Employee::whereHas('store', fn($q) => $q->where('stores_tables.id', $store?->id))
        ->whereHas('department', fn($q) => $q->where('departments_tables.id', $department?->id))
        ->whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
        ->where('can_approve', true)
        ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
        ->orderByDesc('grading.level')
        ->select('employees_tables.*')
        ->first();

    if (!$atasan) {
        $atasan = Employee::whereHas('department', fn($q) => $q->where('departments_tables.id', $department?->id))
            ->whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
            ->where('can_approve', true)
            ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
            ->orderByDesc('grading.level')
            ->select('employees_tables.*')
            ->first();
    }

    if (!$atasan) {
        $atasan = Employee::whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
            ->where('can_approve', true)
            ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
            ->orderByDesc('grading.level')
            ->select('employees_tables.*')
            ->first();
    }

    return $atasan;
}

public function bawahan()
{
    return $this->bawahanList()->get();
}
// public function atasanStruktur()
// {
//     $atasanManual = $this->atasanList()->first();
//     if ($atasanManual) return $atasanManual;

//     $store = $this->primaryStore()->first();
//     $department = $this->primaryDepartment()->first();

//     $atasan = Employee::whereHas('store', fn($q) => $q->where('stores_tables.id', $store?->id))
//         ->whereHas('department', fn($q) => $q->where('departments_tables.id', $department?->id))
//         ->whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();

//     if (!$atasan) {
//         $atasan = Employee::whereHas('department', fn($q) => $q->where('departments_tables.id', $department?->id))
//             ->whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }

//     if (!$atasan) {
//         $atasan = Employee::whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }

//     return $atasan;
// }
// public function atasanStruktur()
// {
//     $atasanManual = $this->atasanList()->first();
//     if ($atasanManual) return $atasanManual;

//     $store = $this->primaryStore()->first();
//     $department = $this->primaryDepartment()->first();

//     $atasan = Employee::whereHas('store', fn($q) => $q->where('stores_tables.id', $store?->id))
//         ->whereHas('department', fn($q) => $q->where('departments_tables.id', $department?->id))
//         ->whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();

//     if (!$atasan) {
//         $atasan = Employee::whereHas('department', fn($q) => $q->where('departments_tables.id', $department?->id))
//             ->whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }

//     if (!$atasan) {
//         $atasan = Employee::whereHas('grading', fn($q) => $q->where('level', '<', $this->grading->level))
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }

//     return $atasan;
// }

// public function atasan()
// {
//     if ($this->atasan_id) {
//         return Employee::find($this->atasan_id);
//     }

//     $store = $this->primaryStore()->first();
//     $department = $this->primaryDepartment()->first();

//     // Fallback — cari di store + department yang sama
//     $atasan = Employee::whereHas('store', fn($q) =>
//             $q->where('stores_tables.id', $store?->id)
//         )
//         ->whereHas('department', fn($q) =>
//             $q->where('departments_tables.id', $department?->id)
//         )
//         ->whereHas('grading', fn($q) =>
//             $q->where('level', '<', $this->grading->level)
//         )
//         ->where('can_approve', true)
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();

//     // Kalau tidak ketemu, fallback department saja
//     if (!$atasan) {
//         $atasan = Employee::whereHas('department', fn($q) =>
//                 $q->where('departments_tables.id', $department?->id)
//             )
//             ->whereHas('grading', fn($q) =>
//                 $q->where('level', '<', $this->grading->level)
//             )
//             ->where('can_approve', true)
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }

//     // Kalau masih tidak ketemu, fallback level saja
//     if (!$atasan) {
//         $atasan = Employee::whereHas('grading', fn($q) =>
//                 $q->where('level', '<', $this->grading->level)
//             )
//             ->where('can_approve', true)
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }

//     return $atasan;
// }

// public function bawahan()
// {
//     // 1. Bawahan manual
//     $bawahanManual = Employee::where('atasan_id', $this->id)
//         ->pluck('id');
//     $bawahanOtomatis = Employee::whereNull('atasan_id')
//         ->whereHas('grading', fn($q) =>
//             $q->where('level', '>', $this->grading->level)
//         )
//         ->where('can_approve', false) // bukan approver
//         ->get()
//         ->filter(fn($emp) => $emp->atasan()?->id === $this->id)
//         ->pluck('id');
//     $allIds = $bawahanManual->merge($bawahanOtomatis);
//     return Employee::whereIn('id', $allIds)->get();
// }





// Relasi untuk eager loading

    // public function atasan()
    // {
    //     $store = $this->primaryStore()->first();
    //     $department = $this->primaryDepartment()->first();

    //     return Employee::whereHas(
    //         'store',
    //         fn($q) =>
    //         $q->where('stores_tables.id', $store?->id)
    //     )
    //         ->whereHas(
    //             'department',
    //             fn($q) =>
    //             $q->where('departments_tables.id', $department?->id)
    //         )
    //         ->whereHas(
    //             'grading',
    //             fn($q) =>
    //             $q->where('level', '<', $this->grading->level)
    //                 ->where('can_approve', true)
    //         )
    //         ->where('can_approve', true)
    //         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
    //         ->orderByDesc('grading.level')
    //         ->select('employees_tables.*')
    //         ->first();
    // }
//     public function atasan()
// {
//     if ($this->atasan_id) {
//         return Employee::find($this->atasan_id);
//     }

//     // Fallback otomatis kalau belum di-set
//     return Employee::whereHas('grading', fn($q) =>
//             $q->where('level', '<', $this->grading->level)
//         )
//         ->where('can_approve', true)
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();
// }

    // public function atasan()
    // {
    //     $store = $this->primaryStore()->first();
    //     $department = $this->primaryDepartment()->first();
    //     $position = $this->primaryPosition()->first();

    //     return Employee::whereHas(
    //         'store',
    //         fn($q) =>
    //         $q->where('stores_tables.id', $store?->id)
    //     )
    //         ->whereHas(
    //             'department',
    //             fn($q) =>
    //             $q->where('departments_tables.id', $department?->id)
    //         )
    //         ->whereHas(
    //             'grading',
    //             fn($q) =>
    //             $q->where('level', '<', $this->grading->level)
    //         )
    //         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
    //         ->orderByDesc('grading.level')
    //         ->select('employees_tables.*')
    //         ->first();
    // }
// public function atasanStruktur()
// {
//     $store = $this->primaryStore()->first();
//     $department = $this->primaryDepartment()->first();

//     return Employee::whereHas('store', fn($q) =>
//             $q->where('stores_tables.id', $store?->id)
//         )
//         ->whereHas('department', fn($q) =>
//             $q->where('departments_tables.id', $department?->id)
//         )
//         ->whereHas('grading', fn($q) =>
//             $q->where('level', '<', $this->grading->level)
//         )
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();
// }
// public function atasanStruktur()
// {
//     $department = $this->primaryDepartment()->first();
//     return Employee::whereHas('department', fn($q) =>
//             $q->where('departments_tables.id', $department?->id)
//         )
//         ->whereHas('grading', fn($q) =>
//             $q->where('level', '<', $this->grading->level)
//         )
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();
// }
// public function atasanStruktur()
// {
//     $department = $this->primaryDepartment()->first();

//     return Employee::whereHas('department', fn($q) =>
//             $q->where('departments_tables.id', $department?->id)
//         )
//         ->whereHas('grading', fn($q) =>
//             $q->where('level', '<', $this->grading->level)
//         )
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();
// }
// public function atasanStruktur()
// {
//     $store = $this->primaryStore()->first();

//     return Employee::whereHas('store', fn($q) =>
//             $q->where('stores_tables.id', $store?->id)
//         )
//         ->whereHas('grading', fn($q) =>
//             $q->where('level', '<', $this->grading->level)
//         )
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();
// }
// public function atasanStruktur()
// {
//     $store = $this->primaryStore()->first();
//     $department = $this->primaryDepartment()->first();

//     return Employee::whereHas('store', fn($q) =>
//             $q->where('stores_tables.id', $store?->id)
//         )
//         ->whereHas('department', fn($q) =>
//             $q->where('departments_tables.id', $department?->id)
//         )
//         ->whereHas('grading', fn($q) =>
//             $q->where('level', '<', $this->grading->level)
//         )
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();
// }
// public function atasanStruktur()
// {
//     $store = $this->primaryStore()->first();
//     $department = $this->primaryDepartment()->first();

//     // Cari atasan di store + department yang sama dulu
//     $atasan = Employee::whereHas('store', fn($q) =>
//             $q->where('stores_tables.id', $store?->id)
//         )
//         ->whereHas('department', fn($q) =>
//             $q->where('departments_tables.id', $department?->id)
//         )
//         ->whereHas('grading', fn($q) =>
//             $q->where('level', '<', $this->grading->level)
//         )
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();

//     // Kalau tidak ketemu, fallback cari berdasarkan department saja
//     if (!$atasan) {
//         $atasan = Employee::whereHas('department', fn($q) =>
//                 $q->where('departments_tables.id', $department?->id)
//             )
//             ->whereHas('grading', fn($q) =>
//                 $q->where('level', '<', $this->grading->level)
//             )
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }

//     // Kalau masih tidak ketemu, fallback cari berdasarkan level saja
//     if (!$atasan) {
//         $atasan = Employee::whereHas('grading', fn($q) =>
//                 $q->where('level', '<', $this->grading->level)
//             )
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }

//     return $atasan;
// }

}

// public function atasanStruktur()
// {
//     // Prioritas 1 — atasan_id manual yang sudah di-set HR
//     if ($this->atasan_id) {
//         return Employee::find($this->atasan_id);
//     }

//     $store = $this->primaryStore()->first();
//     $department = $this->primaryDepartment()->first();

//     // Prioritas 2 — store + department sama
//     $atasan = Employee::whereHas('store', fn($q) =>
//             $q->where('stores_tables.id', $store?->id)
//         )
//         ->whereHas('department', fn($q) =>
//             $q->where('departments_tables.id', $department?->id)
//         )
//         ->whereHas('grading', fn($q) =>
//             $q->where('level', '<', $this->grading->level)
//         )
//         ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//         ->orderByDesc('grading.level')
//         ->select('employees_tables.*')
//         ->first();

//     // Prioritas 3 — department saja
//     if (!$atasan) {
//         $atasan = Employee::whereHas('department', fn($q) =>
//                 $q->where('departments_tables.id', $department?->id)
//             )
//             ->whereHas('grading', fn($q) =>
//                 $q->where('level', '<', $this->grading->level)
//             )
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }
//     // Prioritas 4 — level saja
//     if (!$atasan) {
//         $atasan = Employee::whereHas('grading', fn($q) =>
//                 $q->where('level', '<', $this->grading->level)
//             )
//             ->join('grading', 'grading.id', '=', 'employees_tables.grading_id')
//             ->orderByDesc('grading.level')
//             ->select('employees_tables.*')
//             ->first();
//     }

//     return $atasan;
// }