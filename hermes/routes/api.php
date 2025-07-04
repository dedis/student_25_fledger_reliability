<?php

use App\Http\Controllers\ExperimentController;
use App\Http\Controllers\NodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    if (app()->environment('local')) {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    }

    Route::get('/experiments/{experiment}/end', [ExperimentController::class, 'end'])
        ->name('experiments.end');
    Route::post('/experiments/{experiment}/store-target-pages', [ExperimentController::class, 'storeTargetPages'])
        ->name('experiments.store-target-page');
    Route::get('/experiments/{experiment}/lost-target-pages', [ExperimentController::class, 'lostTargetPages'])
        ->name('experiments.lost-target-page');
    Route::get('/experiments/{experiment}/start-fetching', [ExperimentController::class, 'startFetching'])
        ->name('experiments.start-fetching');
    Route::resource('experiments', ExperimentController::class)
        ->only(['store', 'update']);

    Route::post('/nodes/{node}/set-target-pages', [NodeController::class, 'setTargetPages'])
        ->name('nodes.set-target-pages');
    Route::resource('experiments.nodes', NodeController::class)
        ->shallow()
        ->only(['store', 'update']);
});
