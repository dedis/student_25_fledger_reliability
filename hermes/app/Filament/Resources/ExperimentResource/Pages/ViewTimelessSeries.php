<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Actions\ToLatestExperiment;
use App\Filament\Resources\ExperimentResource;
use App\Filament\Traits\HasResourceSubheading;
use App\Models\TimelessDataPoint;
use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;

class ViewTimelessSeries extends ViewRecord
{
    use HasResourceSubheading;

    protected static string $resource = ExperimentResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $title = 'Timeless Series';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function getHeaderWidgetsColumns(): int|string|array
    {
        return 4;
    }

    protected function getHeaderActions(): array
    {
        return [
            ToLatestExperiment::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        $timelessSeriesName = TimelessDataPoint::whereIn('node_id', $this->record->nodes->pluck('id'))
            ->get()
            ->pluck('name')
            ->unique();

        return $timelessSeriesName->map(function ($name) {
            return ExperimentResource\Widgets\TimelessSeriesChart::make([
                'timelessSeriesName' => $name,
            ]);
        })->toArray();
    }
}
