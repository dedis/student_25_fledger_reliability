<?php

namespace App\Filament\Resources\ExperimentResource\Widgets;

use App\Models\Experiment;
use App\Models\Node;
use Filament\Widgets\ChartWidget;

class SuccessVTimeoutChart extends ChartWidget
{
    protected static ?string $heading = 'Success v Timeout';

    public ?Experiment $record = null;

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $experiment = $this->record;

        return [
            'datasets' => [
                [
                    'label' => $this->getHeading(),
                    'data' => [
                        $experiment->nodes()->where('status', Node::STATUS_SUCCESS)->count(),
                        $experiment->nodes()->where('status', Node::STATUS_TIMEOUT)->count(),
                    ],
                ],
            ],
            'labels' => [
                'Success',
                'Timeout',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
