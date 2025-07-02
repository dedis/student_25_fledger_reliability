<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Node extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_TIMEOUT = 'timeout';

    protected $fillable = [
        'name',
        'pages_stored',
        'target_pages',
        'last_update_timestamp',
        'evil_noforward',
    ];

    public function experiment(): BelongsTo
    {
        return $this->belongsTo(Experiment::class);
    }

    public function dataPoints(): HasMany
    {
        return $this->hasMany(DataPoint::class);
    }

    public function timelessDataPoints(): HasMany
    {
        return $this->hasMany(TimelessDataPoint::class);
    }

    protected function casts(): array
    {
        return [
            'pages_stored' => 'array',
            'target_pages' => 'array',
            'last_update_timestamp' => 'datetime',
            'evil_noforward' => 'boolean',
        ];
    }
}
