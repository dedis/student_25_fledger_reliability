<?php

namespace App\Filament\Resources\ExperimentResource\Widgets;

use App\Models\Experiment;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class FetchSuccessRateAmongBenevolent extends ChartWidget
{
    public ?Experiment $record = null;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $experiment = $this->record;
        $experiment->load(['nodes', 'nodes.timelessDataPoints']);

        $amountBenevolent = $experiment->nodes
            ->where('evil_noforward', false)
            ->count();
        $totalExpected = $amountBenevolent * $experiment->targets_per_node;
        $totalFetched = $experiment->nodes
            ->where('evil_noforward', false)
            ->map(function ($node) {
                return $node->timelessDataPoints
                    ->where('name', 'target_successfully_fetched_total')
                    ->sum('value');
            })->sum();

        return [
            'datasets' => [
                [
                    'label' => $this->getHeading(),
                    'data' => [
                        $totalFetched,
                        $totalExpected - $totalFetched,
                    ],
                ],
            ],
            'labels' => [
                'Successfully Fetched',
                'Not Fetched',
            ],
        ];
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Fetch Success Rate Among Benevolent Nodes';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
