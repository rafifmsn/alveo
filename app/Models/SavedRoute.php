<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'region',
        'latitude',
        'longitude',
        'distance_km',
        'preferred_activity',
        'surface_type',
        'is_preset',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'distance_km' => 'float',
            'is_preset' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
