<?php

namespace App\Jobs;

use App\Data\SimulationSnapshotData;
use App\Models\Node;
use Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class NodeUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $failOnTimeout = true;

    public int $timeout = 10;

    public function __construct(
        protected Node $node,
        protected SimulationSnapshotData $data,
        protected Carbon $dispatchedAt
    ) {}

    public function handle(): void
    {
        // Ensure no two jobs for the same node run concurrently
        Cache::lock('node_update_'.$this->node->id, 20)->get(function () {
            $this->processUpdate();
        });
    }

    protected function processUpdate(): void
    {
        $node = $this->node;
        $data = $this->data;
        $dispatchedAt = $this->dispatchedAt;

        $node->load(['dataPoints', 'timelessDataPoints', 'experiment']);
        $experiment = $node->experiment;

        $node->pages_stored = $data->pages_stored ?? $node->pages_stored;
        $node->status = $data->node_status ?? $node->status;
        $node->evil_noforward = $data->evil_no_forward ?? $node->evil_noforward;

        $dataPoints = collect($data->timed_metrics)
            ->map(function ($metric) use (&$node, $dispatchedAt) {
                return $node->dataPoints()->make([
                    'name' => $metric[0],
                    'value' => $metric[1],
                    'time' => $dispatchedAt->floorSeconds(10),
                ]);
            })
            ->all();
        $node->dataPoints()->saveMany($dataPoints);

        if ($node->last_update_timestamp === null || $dispatchedAt->isAfter($node->last_update_timestamp)) {
            $timelessDataPoints = collect($data->timeless_metrics)
                ->map(function ($metric) use (&$node) {
                    $tdp = $node->timelessDataPoints()->where('name', $metric[0])->first();
                    if ($tdp) {
                        $tdp->value = $metric[1];

                        return $tdp;
                    }

                    return $node->timelessDataPoints()->make([
                        'name' => $metric[0],
                        'value' => $metric[1],
                    ]);
                })
                ->all();
            $node->timelessDataPoints()->saveMany($timelessDataPoints);
        }

        $node->last_update_timestamp = $dispatchedAt;

        $node->save();
        $experiment->save();
    }
}
