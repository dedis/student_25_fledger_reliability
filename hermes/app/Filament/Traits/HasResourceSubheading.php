<?php

namespace App\Filament\Traits;

trait HasResourceSubheading
{
    public function getSubheading(): ?string
    {
        $resource = static::getResource();

        return $resource::getSubheading($this->record);
    }
}
