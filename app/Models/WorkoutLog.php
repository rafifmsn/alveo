<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'saved_route_id',
        'location_name',
        'activity_type',
        'exertion_zone',
        'duration_minutes',
        'ambient_pm2_5',
        'ambient_o3',
        'ventilation_rate_lpm',
        'air_volume_m3',
        'inhaled_dose_ug',
        'exertion_risk_score',
        'trained_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'exertion_zone' => 'integer',
            'duration_minutes' => 'integer',
            'ambient_pm2_5' => 'float',
            'ambient_o3' => 'float',
            'ventilation_rate_lpm' => 'float',
            'air_volume_m3' => 'float',
            'inhaled_dose_ug' => 'float',
            'exertion_risk_score' => 'float',
            'trained_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedRoute(): BelongsTo
    {
        return $this->belongsTo(SavedRoute::class);
    }
}
