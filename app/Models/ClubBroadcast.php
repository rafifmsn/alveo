<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClubBroadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'club_name',
        'captain_name',
        'corridor_name',
        'latitude',
        'longitude',
        'activity_type',
        'duration_minutes',
        'verdict',
        'window_start',
        'window_end',
        'session_date',
        'projected_pm2_5',
        'improvement_pct',
        'wind_summary',
        'captain_note',
        'followers_count',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'duration_minutes' => 'integer',
            'session_date' => 'date',
            'projected_pm2_5' => 'float',
            'improvement_pct' => 'float',
            'followers_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_broadcast_follows')
            ->withTimestamps();
    }

    public function telegramText(): string
    {
        $statusTag = $this->verdict === 'GO' ? '[CLEAR - GO]' : '[RESCHEDULED - DELAY]';
        $act = ucfirst($this->activity_type);

        return "{$statusTag} {$this->club_name} Dispatch\n"
            ."Corridor: {$this->corridor_name}\n"
            ."Session: {$act} ({$this->duration_minutes}m) on {$this->session_date->format('D, d M Y')}\n"
            ."Optimal Window: {$this->window_start} - {$this->window_end}\n"
            ."Projected PM2.5: {$this->projected_pm2_5} ug/m3 (-{$this->improvement_pct}% exposure)\n"
            ."Wind Vector: {$this->wind_summary}\n"
            ."Note ({$this->captain_name}): {$this->captain_note}";
    }
}
