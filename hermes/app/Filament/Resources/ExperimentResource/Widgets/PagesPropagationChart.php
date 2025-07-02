<?php

namespace App\Filament\Resources\ExperimentResource\Widgets;

use App\Models\Experiment;
use Filament\Widgets\ChartWidget;

class PagesPropagationChart extends ChartWidget
{
    protected static ?string $heading = 'Pages Propagation (legacy)';

    public ?Experiment $record = null;

    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        $experiment = $this->record;

        $pages = collect(range(1, $experiment->pages_amount))->map(fn ($page) => "{$page}");
        $amounts = $pages->map(function ($page) use ($experiment) {
            return $experiment->nodes
                ->filter(fn ($node) => $node->pages_stored && in_array($page, collect($node->pages_stored)->pluck('name')->toArray()))
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Nodes storing page',
                    'data' => $amounts,
                ],
            ],
            'labels' => $pages->map(fn ($page) => "Page {$page}"),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
