<?php

namespace App\Filament\Actions;

use App\Filament\Resources\ExperimentResource;
use Filament\Actions\Action;

class ToLatestExperiment
{
    public static function make(): Action
    {
        return Action::make('To latest experiment')
            ->outlined()
            ->action(function ($record) {
                $url = ExperimentResource::getUrl('metrics', [
                    'record' => $record->latestExperiment(),
                ]);
                redirect($url);
            })
            ->icon('heroicon-o-arrow-right');
    }
}
