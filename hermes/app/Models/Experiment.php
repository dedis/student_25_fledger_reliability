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
        $this->load('nodes');

        $fetchSuccessRate = $this->fetchSuccessRate() * 100;
        $fetchesTotal = $this->nodes
            ->map(fn (Node $node) => $node->timelessDataPoints->where('name', 'fetch_requests_total')->sum('value'))
            ->sum();
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
        $targetsStored = count($this->storedTargetPages());

        return collect([
            $state,
            $duration,
            "{$fetchSuccessRate}% f-success",
            "{$fetchesTotal} fetches",
            "{$this->nodes->count()} nodes",
            "{$evilPercentage}% evil",
            "{$this->filler_amount} fillers",
            "{$targetsStored}/{$this->target_amount} targets",
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

    public function storedTargetPages(): array
    {
        $this->load(['nodes']);

        return $this->nodes
            ->where('evil_noforward', false)
            ->flatMap(function (Node $node) {
                return collect($node->stored_targets);
            })
            ->unique()
            ->toArray();
    }

    public function lostTargetPages(): array
    {
        $this->load(['nodes']);
        $targetIds = collect($this->target_pages)->pluck('id');

        $propagatedTargetIds = $this->storedTargetPages();
        $lostTargetIds = collect($targetIds)->diff($propagatedTargetIds)->values();

        return $lostTargetIds->toArray();
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }
}
