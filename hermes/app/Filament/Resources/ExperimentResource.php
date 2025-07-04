<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExperimentResource\Pages;
use App\Models\Experiment;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExperimentResource extends Resource
{
    protected static ?string $model = Experiment::class;

    protected static ?string $slug = 'experiments';

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\EditExperiment::class,
            Pages\ViewMetrics::class,
            Pages\ViewTimelessSeries::class,
            Pages\ViewTimeSeries::class,
            Pages\ManageNodes::class,
        ]);
    }

    public static function getSubheading(Experiment $experiment): string
    {
        return $experiment->infoLine();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('id')
                    ->label('Experiment ID')
                    ->disabled(),
                TextInput::make('name')
                    ->disabled(),
                Toggle::make('bookmarked')
                    ->live()
                    ->afterStateUpdated(fn ($state, Experiment $experiment) => $experiment->update(['bookmarked' => $state])),
                TextInput::make('summary'),
                MarkdownEditor::make('description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id'),
                TextColumn::make('name')
                    ->description(fn (Experiment $experiment) => $experiment->infoLine()),
                TextInputColumn::make('summary')
                    ->grow(),
                ToggleColumn::make('bookmarked')
                    ->label('Bookmarked')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('bookmarked')
                    ->label('Bookmarked only')
                    ->toggle()
                    ->query(fn (Builder $query, $state) => $query->where('bookmarked', $state)),
                TrashedFilter::make(),
            ])
            ->headerActions([
                Action::make('delete_ongoing')
                    ->label('Delete all ongoing')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->outlined()
                    ->requiresConfirmation()
                    ->action(function () {
                        Experiment::query()
                            ->whereNull('ended_at')
                            ->delete();
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->url(fn ($record) => static::getUrl('metrics', ['record' => $record])),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExperiments::route('/'),
            'create' => Pages\CreateExperiment::route('/create'),
            'edit' => Pages\EditExperiment::route('/{record}/edit'),

            'metrics' => Pages\ViewMetrics::route('/{record}/metrics'),
            'nodes' => Pages\ManageNodes::route('/{record}/nodes'),
            'time-series' => Pages\ViewTimeSeries::route('/{record}/time-series'),
            'timeless-series' => Pages\ViewTimelessSeries::route('/{record}/timeless-series'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            //
        ];
    }
}
