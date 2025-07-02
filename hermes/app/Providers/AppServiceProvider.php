<?php

namespace App\Providers;

use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.force_https')) {
            URL::forceScheme('https');
        }
        Table::configureUsing(function (Table $table): void {
            $table->filtersLayout(FiltersLayout::AboveContent);
        });
        Gate::define('viewPulse', function ($user) {
            return $user->can('access pulse');
        });
    }
}
