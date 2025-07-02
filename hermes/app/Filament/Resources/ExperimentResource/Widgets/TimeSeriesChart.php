<?php

namespace App\Filament\Resources\ExperimentResource\Widgets;

use App\Models\Experiment;
use App\Models\Node;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Carbon;

class TimeSeriesChart extends ChartWidget
{
    public ?Experiment $record = null;

    public ?string $timeSeriesName = null;

    public function getHeading(): string|Htmlable|null
    {
        if ($this->timeSeriesName === null) {
            return 'Time Series';
        }

        return str($this->timeSeriesName)->replace('_', ' ')->title();
    }

    protected function getData(): array
    {
        $experiment = $this->record;
        $experiment->load('nodes.dataPoints');

        $start = Carbon::make($experiment->created_at)->floorSeconds(10);
        $end = Carbon::make($experiment?->ended_at ?? now())->ceilSeconds(10);

        if (abs($start->diffInMinutes($end)) >= 60) {
            $end = $start->copy()->addHour();
        }

        $nodes = $experiment->nodes()->inRandomOrder()->take(5)->get();

        $dataPoints = $nodes->flatMap(function (Node $node) use ($start) {
            $nodeDataPoints = $node->dataPoints()
                ->where('name', $this->timeSeriesName)
                ->get(['time', 'value'])
                ->map(function ($dataPoint) use ($start) {
                    $secondsSinceStart = (int) abs(Carbon::parse($dataPoint->time)->diffInSeconds($start));
                    return [
                        'secondsSinceStart' => str($secondsSinceStart)->toString(),
                        'value' => $dataPoint->value,
                    ];
                })
                ->pluck('value', 'secondsSinceStart');

            return [
                [
                    'name' => $node->name,
                    'dataPoints' => $nodeDataPoints
                ],
            ];
        });

        $x = range(0, $start->diffInSeconds($end), 10);
        // labels of the form 1m20s
        $labels = collect($x)->map(function ($secondsSinceStart) {
            $date = Carbon::createFromTimestamp($secondsSinceStart);
            $m = $date->minuteOfHour();
            $s = $date->secondOfMinute();
            return "{$m}m {$s}s";
        })->toArray();

        $datasets = $dataPoints->map(function ($node) use ($x) {
            $data = collect($x)->map(function ($secondsSinceStart) use ($node) {
                $value = $node['dataPoints']->get($secondsSinceStart, null);
                return $value !== null ? (float) $value : null; // Ensure the value is a float or null
            })->toArray();

            return [
                'label' => $node['name'],
                'data' => $data,
                'borderColor' => '#'.substr(md5($node['name']), 0, 6), // Generate a color based on the node name
                'spanGaps' => true,
            ];
        });

        return [
            'datasets' => $datasets,
            'labels' => $labels
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
