<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPasswordResetLink extends Model
{
    protected $table = 'user_password_reset_links';

    protected $fillable = ['user_id', 'token_hash', 'sent_to', 'requested_by', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function isUsable(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }
}
