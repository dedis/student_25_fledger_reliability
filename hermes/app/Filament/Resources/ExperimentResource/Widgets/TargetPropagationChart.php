<?php

namespace App\Filament\Resources\ExperimentResource\Widgets;

use App\Models\Experiment;
use App\Models\Node;
use Filament\Widgets\ChartWidget;

class TargetPropagationChart extends ChartWidget
{
    protected static ?string $heading = 'Target Propagation';

    public ?Experiment $record = null;

    protected int|string|array $columnSpan = 2;

    protected function getData(): array
    {
        $experiment = $this->record;

        $pages = collect(range(0, $experiment->target_amount - 1))->map(fn ($page) => "target-{$page}");
        $benevolentNodesStoringPages = $pages->map(function ($page) use ($experiment) {
            return $experiment->nodes()
                ->where('evil_noforward', false)
                ->get()
                ->filter(fn ($node) => $this->isPageStoredInNode($node, $page))
                ->count();
        });

        $evilNodesStoringPages = $pages->map(function ($page) use ($experiment) {
            return $experiment->nodes()
                ->where('evil_noforward', true)
                ->get()
                ->filter(fn ($node) => $this->isPageStoredInNode($node, $page))
                ->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Benevolent nodes storing target page',
                    'data' => $benevolentNodesStoringPages->toArray(),
                ],
                [
                    'label' => 'Evil nodes storing target page',
                    'data' => $evilNodesStoringPages->toArray(),
                    'backgroundColor' => '#953620',
                ],
            ],
            'labels' => $pages->map(fn ($page) => "{$page}"),
        ];
    }

    private function isPageStoredInNode(Node $node, string $page): bool
    {
        if ($node->pages_stored == null) {
            return false;
        }

        $pagesStored = collect($node->pages_stored)->pluck('name')->toArray();

        return in_array($page, $pagesStored);
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
