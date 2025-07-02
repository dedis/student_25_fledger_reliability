<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Actions\ToLatestExperiment;
use App\Filament\Resources\ExperimentResource;
use App\Filament\Traits\HasResourceSubheading;
use App\Models\DataPoint;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;

class ViewTimeSeries extends ViewRecord
{
    use HasResourceSubheading;

    protected static string $resource = ExperimentResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $title = 'Time Series';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ToLatestExperiment::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        $timeSeriesNames = DataPoint::whereIn('node_id', $this->record->nodes->pluck('id'))
            ->get()
            ->pluck('name')
            ->unique();

        return $timeSeriesNames->map(function ($name) {
            return ExperimentResource\Widgets\TimeSeriesChart::make([
                'timeSeriesName' => $name,
            ]);
        })->toArray();
    }
}
