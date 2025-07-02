<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Resources\ExperimentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExperiment extends CreateRecord
{
    protected static string $resource = ExperimentResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
