<?php

namespace App\Filament\Resources\ExperimentResource\Widgets;

use App\Models\Experiment;
use App\Models\Node;
use App\Models\TimelessDataPoint;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class TimelessSeriesChart extends ChartWidget
{
    public ?Experiment $record = null;

    public ?string $timelessSeriesName = null;

    protected int | string | array $columnSpan = 2;

    public function getHeading(): string|Htmlable|null
    {
        if ($this->timelessSeriesName === null) {
            return 'Bar Chart';
        }

        return str($this->timelessSeriesName)->replace('_', ' ')->title();
    }

    protected function getData(): array
    {
        $experiment = $this->record;
        $experiment->load('nodes.timelessDataPoints');

        $nodes = $experiment->nodes()->orderBy('name')->get();

        $values = $nodes->map(function ($node) {
            return TimelessDataPoint::where('node_id', $node->id)
                ->where('name', $this->timelessSeriesName)
                ->first()
                ?->value;
        });

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
