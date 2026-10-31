<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'sensitivity_profile',
        'unit_system',
        'default_activity',
        'default_duration',
        'dark_mode',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'default_duration' => 'integer',
            'dark_mode' => 'boolean',
        ];
    }

    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function pm25Threshold(): float
    {
        return match ($this->sensitivity_profile) {
            'sensitive' => 25.0,
            'tolerant' => 50.0,
            default => 35.0,
        };
    }

    public function savedRoutes(): HasMany
    {
        return $this->hasMany(SavedRoute::class);
    }

    public function workoutLogs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class);
    }

    public function clubBroadcasts(): HasMany
    {
        return $this->hasMany(ClubBroadcast::class);
    }

    public function followedBroadcasts(): BelongsToMany
    {
        return $this->belongsToMany(ClubBroadcast::class, 'club_broadcast_follows')
            ->withTimestamps();
    }
}
