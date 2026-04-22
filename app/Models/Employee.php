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
use Svg\Tag\Group;
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
    // protected static function booted()
    // {
    //     static::creating(function ($employee) {
    //         $employee->pin = self::generateSafePin();
    //     });
    // }
    // public static function generateSafePin()
    // {
    //     return DB::transaction(function () {
    //         // Lock baris untuk mencegah race condition
    //         $lastPin = DB::table('employees_tables')
    //             ->whereRaw('CHAR_LENGTH(pin) = 4 AND pin REGEXP "^[0-9]{4}$"')
    //             ->lockForUpdate()
    //             ->orderByDesc('pin')
    //             ->value('pin');
    //         $nextPin = str_pad(((int) $lastPin + 1), 5, '0', STR_PAD_LEFT);
    //         if ((int)$nextPin > 99999) {
    //             throw new \Exception("PIN sudah habis (lebih dari 9999)");
    //         }
    //         return $nextPin;
    //     });
    // }
      public static function getReligionOptions()
{
    return [
        'Catholic Christian' => 'Catholic Christian',
        'Christian' => 'Christian',
        'Islam' => 'Islam',
        'Hindu' => 'Hindu',
        'Confucian' => 'Confucian',
        'Buddha' => 'Buddha'];
}
      public static function getMarriageOptions()
{
    return [
        'Yes' => 'Yes',
        'No' => 'No'];
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
        'Diploma IV' => 'Diploma IV'];
}
      public static function getGenderOptions()
{
    return [
        'Male' => 'Male',
        'Female' => 'Female'];
}
      public static function getStatusEmployeeOptions()
{
    return [
        'PKWT' => 'PKWT',
        'DW' => 'DW',
        'On Job Training' => 'On Job Training'];
}
      public static function getStatusOptions()
{
    return [
        'Active' => 'Active',
        'Inactive' => 'Inactive',
        'Pending' => 'Pending',
        'On Leave' => 'On Leave',
        'Mutation' => 'Mutation',
        'Resign' => 'Resign'];
}
    public function department()
    {
        return $this->belongsTo(Departments::class, 'department_id', 'id');
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
        return $this->belongsTo(Banks::class, 'banks_id','id');
    }
    public function store()
    {
        return $this->belongsTo(Stores::class, 'store_id');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id','id');
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
    // public function getLengthOfServiceAttribute()
    // {
    //     return $this->created_at->diffInDays(now()) . ' days';
    // }
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







    public function manager()
    {
        return $this->belongsTo(Employee::class, 'level_id');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'level_id');
    }

    public function subordinatesRecursive()
    {
        return $this->subordinates()->with('subordinatesRecursive');
    }

    public function departmentMembers()
    {
        return Employee::where('department_id', $this->department_id)->get();
    }

    /**
     * Kembalikan array id semua bawahan (rekursif)
     */
    public function getAllSubordinateIds(): array
    {
        $ids = $this->subordinates()->pluck('id')->toArray();

        foreach ($this->subordinates as $sub) {
            $ids = array_merge($ids, $sub->getAllSubordinateIds());
        }

        return $ids;
    }

    /**
     * Ambil semua bawahan sebagai Eloquent Collection (rekursif)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllSubordinates(): EloquentCollection
    {
        // mulai dengan Eloquent Collection kosong
        $all = new EloquentCollection();

        // ambil direct subordinates (query ke DB)
        $direct = $this->subordinates()->get();

        foreach ($direct as $sub) {
            // tambahkan subordinate langsung
            $all->push($sub);

            // rekursif: ambil koleksi Eloquent dari bawahannya
            $subSubs = $sub->getAllSubordinates(); // ini sudah EloquentCollection

            // merge: EloquentCollection->merge mengembalikan Support collection,
            // jadi gunakan add() atau concat cara yang aman:
            foreach ($subSubs as $ss) {
                $all->push($ss);
            }
        }

        // unikkan berdasarkan id (jika ada duplikat)
        $all = $all->unique('id')->values();

        return $all;
    }
    public function departmentSubordinates(): Collection
    {
        return Employee::where('department_id', $this->department_id)
            ->where('id', '!=', $this->id)
            ->get();
    }
    public function getAllSubordinatesByDepartment(): Collection
    {
        // Ambil semua dalam 1 department
        $all = Employee::where('department_id', $this->department_id)->get();

        // Pisahkan mereka yang bukan dirinya sendiri
        return $all->where('id', '!=', $this->id);
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
                    'store_id' => fn($id) => optional(Stores::find($id))->name,
                    'position_id' => fn($id) => optional(Position::find($id))->name,
                    'banks_id' => fn($id) => optional(Banks::find($id))->name,
                    'structure_id' => fn($id) => optional(
                        optional(
                            optional(Structuresnew::with('submissionposition.positionRelation')->find($id))
                                ->submissionposition
                        )->positionRelation
                    )->name,
                    'department_id' => fn($id) => optional(Departments::find($id))->department_name,
                    'grading_id' => fn($id) => optional(Grading::find($id))->grading_name,
                    'group_id' => fn($id) => optional(Groups::find($id))->group_name,
                    'level_id' => fn($id) => optional(Employee::find($id))->employee_name,
                ];
                $fieldLabels = [
                    'employee_name' => 'Employee Name',
                    'foto' => 'Photo',
                    'position_id' => 'Position',
                    'company_id' => 'Company',
                    'store_id' => 'Location',
                    'bank_account_number' => 'Bank Account Number',
                    'banks_id' => 'Bank',
                    'department_id' => 'Department',
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
                    'structure_id' => 'Structures',
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
                        // Selain relasi, tampilkan nilai langsung
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
    
    public function rosters()
    {
    return $this->hasMany(Roster::class, 'employee_id', 'id');
    }

    public function schedules()
    {
    return $this->hasMany(Schedule::class, 'employee_id', 'id');
    }

    public function structuresnew()
    {
        return $this->belongsTo(Structuresnew::class, 'structure_id', 'id');
    }
    public function getSupervisorFromStructure()
    {
        $structure = $this->structuresnew;

        if (!$structure || !$structure->parent) {
            return null;
        }

        $parentStructure = $structure->parent;

        $supervisor = $parentStructure->employees()
            ->where('status', 'Active')
            ->where('is_manager', 1)
            ->first();

        return $supervisor;
    }

    public function getSecondarySupervisors(): Collection
    {
        $structure = $this->structuresnew;

        $result = collect();

        if (!$structure) {
            return $result;
        }

        // secondarySupervisors adalah relation ke Structuresnew (pivot structure_supervisors)
        $secStructures = $structure->secondarySupervisors ?? collect();

        foreach ($secStructures as $secStructure) {
            $manager = $secStructure->employees()
                ->where('status', 'Active')
                ->where('is_manager', 1)
                ->first();

            if ($manager && !$result->contains('id', $manager->id)) {
                $result->push($manager);
            }
        }

        return $result;
    }
    public function getAllSupervisors(): Collection
    {
        $supervisors = collect();

        $primary = $this->getSupervisorFromStructure();
        if ($primary) {
            $supervisors->push($primary);
        }

        $secondary = $this->getSecondarySupervisors();
        if ($secondary->isNotEmpty()) {
            $supervisors = $supervisors->merge($secondary);
        }

        // pastikan unique & reset index
        return $supervisors->unique('id')->values();
    }


}
