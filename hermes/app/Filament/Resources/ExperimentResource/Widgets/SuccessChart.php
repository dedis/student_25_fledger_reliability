<?php

namespace App\Filament\Resources\ExperimentResource\Widgets;

use App\Models\Experiment;
use App\Models\Node;
use App\Models\TimelessDataPoint;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class SuccessChart extends ChartWidget
{
    public ?Experiment $record = null;

    protected int | string | array $columnSpan = 2;

    public function getHeading(): string|Htmlable|null
    {
        return 'Success';
    }

    protected function getData(): array
    {
        $experiment = $this->record;

        $nodes = $experiment->nodes()->orderBy('name')->get();

        $values = $nodes->map(fn ($node) => $node->status == Node::STATUS_SUCCESS ? 1 : 0);

        return [
            'datasets' => [
                [
                    'label' => $this->getHeading(),
                    'data' => $values,
                ],
            ],
            'labels' => $nodes->pluck('name')
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
