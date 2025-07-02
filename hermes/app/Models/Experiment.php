<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Experiment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'pages_amount', // legacy (backwards compatibility)
        'bookmarked',
        'summary',
        'description',
        'ended_at',
        'target_page_id', // legacy (backwards compatibility)
        'filler_amount',
        'target_amount',
        'target_pages',
        'targets_per_node',
    ];

    protected $casts = [
        'bookmarked' => 'boolean',
        'target_pages' => 'array',
        'filler_amount' => 'integer',
        'target_amount' => 'integer',
        'targets_per_node' => 'integer',
        'ended_at' => 'datetime',
    ];

    public static function latestExperiment(): ?self
    {
        return self::orderBy('id', 'desc')->first();
    }

    public function infoLine(): string
    {
        $fetchSuccessRate = $this->fetchSuccessRate() * 100;
        $state = $this->ended_at
            ? 'Ended'
            : 'Ongoing';
        $duration = $this->ended_at
            ? $this->ended_at->shortAbsoluteDiffForHumans($this->created_at, 2)
            : $this->created_at->shortAbsoluteDiffForHumans(now(), 2);
        $evilCount = $this->nodes->where('evil_noforward', true)->count();
        $evilPercentage = $this->nodes->count() > 0
            ? round($evilCount / $this->nodes->count() * 100)
            : 0;

        return collect([
            $state,
            $duration,
            "{$fetchSuccessRate}% f-success",
            "{$this->nodes->count()} nodes",
            "{$evilPercentage}% evil",
            "{$this->filler_amount} fillers",
            "{$this->targets_per_node}/{$this->target_amount} targets",
        ])->join(' | ');
    }

    public function fetchSuccessRate(): float
    {
        $this->load('nodes', 'nodes.timelessDataPoints');
        $amountBenevolent = $this->nodes
            ->where('evil_noforward', false)
            ->count();
        $totalExpected = $amountBenevolent * $this->targets_per_node;
        $totalFetched = $this->nodes
            ->where('evil_noforward', false)
            ->map(function ($node) {
                return $node->timelessDataPoints
                    ->where('name', 'target_successfully_fetched_total')
                    ->sum('value');
            })->sum();

        $rate = $totalExpected > 0 ? $totalFetched / $totalExpected : 0;

        return round($rate, 2);
    }

    public function lostTargetPages(): array
    {
        $this->load(['nodes']);
        $targetIds = collect($this->target_pages)->pluck('id');

        $propagatedTargetIds = collect($targetIds)->filter(function ($targetId) {
            return $this->nodes
                ->where('evil_noforward', false)
                ->firstWhere(function ($node) use ($targetId) {
                    $storedIds = collect($node->pages_stored)->pluck('id');

                    return collect($storedIds)->contains($targetId);
                });
        });
        $lostTargetIds = collect($targetIds)->diff($propagatedTargetIds)->values();

        return $lostTargetIds->toArray();
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }
}
