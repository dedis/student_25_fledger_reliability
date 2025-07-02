<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Actions\ToLatestExperiment;
use App\Filament\Resources\ExperimentResource;
use App\Filament\Traits\HasResourceSubheading;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditExperiment extends EditRecord
{
    use HasResourceSubheading;

    protected static string $resource = ExperimentResource::class;

    protected static ?string $title = 'Experiment';

    protected function getHeaderActions(): array
    {
        return [
            ToLatestExperiment::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
