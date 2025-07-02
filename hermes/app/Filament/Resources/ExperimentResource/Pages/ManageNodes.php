<?php

namespace App\Filament\Resources\ExperimentResource\Pages;

use App\Filament\Actions\ToLatestExperiment;
use App\Filament\Resources\ExperimentResource;
use App\Filament\Traits\HasResourceSubheading;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ManageNodes extends ManageRelatedRecords
{
    use HasResourceSubheading;

    protected static string $resource = ExperimentResource::class;

    protected static string $relationship = 'nodes';

    protected static ?string $navigationIcon = 'heroicon-o-cube-transparent';

    protected static ?string $title = 'Nodes';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]));
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ToLatestExperiment::make(),
        ];
    }
}
