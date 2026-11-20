<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\AuditsChanges;

class User extends Authenticatable
{
    use HasFactory, Notifiable, AuditsChanges;

    protected $fillable = [
        'username',
        'email',
        'password',
        'action_password', // Added for Manager Override
        'firstname',
        'lastname',
        'suffix',
        'role',
        'is_active',
        'must_change_password',
        'theme',
    ];

    protected $hidden = [
        'password',
        'action_password', // Hide this from JSON responses
        'remember_token',
    ];

    protected $appends = [
        'has_action_password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function getHasActionPasswordAttribute(): bool
    {
        return !empty($this->action_password);
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->firstname} {$this->lastname} {$this->suffix}");
    }
}