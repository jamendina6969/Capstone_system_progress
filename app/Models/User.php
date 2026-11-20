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
        'avatar_data',
        'avatar_mime',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'action_password', // Hide this from JSON responses
        'remember_token',
        'avatar_data',     // Keep JSON payloads lightweight
    ];

    protected $appends = [
        'has_action_password',
        'avatar_url',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_active'            => 'boolean',
            'must_change_password' => 'boolean',
            'preferences'          => 'array',
        ];
    }

    public function getHasActionPasswordAttribute(): bool
    {
        return !empty($this->action_password);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->lastname} {$this->suffix}");
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (empty($this->avatar_data)) {
            return null;
        }

        $ver = $this->updated_at ? $this->updated_at->timestamp : 1;
        return route('account.avatar', ['user' => $this->id, 'v' => $ver]);
    }

    public function getPreference(string $key, mixed $default = null): mixed
    {
        $prefs = is_array($this->preferences) ? $this->preferences : [];
        return array_key_exists($key, $prefs) ? $prefs[$key] : $default;
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }
}
