<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Actions\ToLatestExperiment;
use App\Filament\Resources\ExperimentResource;
use App\Filament\Traits\HasResourceSubheading;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewMetrics extends EditRecord
{
    use HasResourceSubheading;

    protected static string $resource = ExperimentResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $title = 'Metrics';

    public function getSubheading(): string|Htmlable|null
    {
        return $this->record->infoLine();
    }

    public function getFooterWidgetsColumns(): int|string|array
    {
        return 4;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('summary'),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ToLatestExperiment::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ExperimentResource\Widgets\SuccessVTimeoutChart::make(),
            ExperimentResource\Widgets\SuccessChart::make(),
            ExperimentResource\Widgets\FetchSuccessRateAmongBenevolent::make(),
            ExperimentResource\Widgets\FillersPropagationChart::make(),
            ExperimentResource\Widgets\TargetPropagationChart::make(),
            ExperimentResource\Widgets\TimelessSeriesChart::make([
                'timelessSeriesName' => 'target_successfully_fetched_total',
            ]),

            ExperimentResource\Widgets\PagesPropagationChart::make(),
            ExperimentResource\Widgets\TimelessSeriesChart::make([
                'timelessSeriesName' => 'target_page_stored_bool',
            ]),
        ];
    }
}
