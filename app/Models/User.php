<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
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
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'users';
    protected $fillable = [
        'id',
        'terms_id',
        'employee_id',
        'username',
        'password',
        'active_role_hrx',
        'all_roles_hrx',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
        'two_factor_required',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    // app/Models/User.php
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s', // Format default MySQL
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'all_roles_hrx' => 'array',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_required'     => 'boolean',

    ];

    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_confirmed_at)
            && !is_null($this->two_factor_secret);
    }

    // Apakah user ini wajib setup 2FA (di-set oleh admin)
    public function requiresTwoFactor(): bool
    {
        return (bool) $this->two_factor_required;
    }

    // Generate recovery codes baru (8 kode, masing-masing 10 karakter)
    public function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))->map(
            fn() => strtoupper(substr(bin2hex(random_bytes(5)), 0, 10))
        )->toArray();
    }

    // Simpan recovery codes ke DB (di-hash satu per satu)
    public function storeRecoveryCodes(array $codes): void
    {
        $hashed = array_map(fn($code) => bcrypt($code), $codes);
        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($hashed)),
        ])->save();
    }

    // Ambil recovery codes (decrypt + decode)
    public function getRecoveryCodes(): array
    {
        if (!$this->two_factor_recovery_codes) return [];
        return json_decode(decrypt($this->two_factor_recovery_codes), true) ?? [];
    }

    // Validasi & hapus recovery code yang dipakai
    public function useRecoveryCode(string $inputCode): bool
    {
        $codes = $this->getRecoveryCodes();
        foreach ($codes as $index => $hashedCode) {
            if (Hash::check(strtoupper(trim($inputCode)), $hashedCode)) {
                // Hapus code yang sudah dipakai
                unset($codes[$index]);
                $this->forceFill([
                    'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
                ])->save();
                return true;
            }
        }
        return false;
    }
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public function findForAuth($username)
    {
        return $this->where('username', $username)->first();
    }

    public function Terms()
    {
        return $this->belongsTo(Terms::class, 'terms_id');
    }
    public function Employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }



    public function sessions()
    {
        return $this->hasMany(UserSession::class, 'user_id', 'id');
    }
}
