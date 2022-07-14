<?php

use Illuminate\Support\Facades\Route;
use MarcoRieser\Vitals\Http\Controllers\VitalsController;

Route::prefix('api/statamic-vitals')->group(function () {
    // TODO[mr]: remove get route on release (07.04.22 mr)
    Route::get('vitals', [VitalsController::class, '__invoke']);
    Route::post('vitals', [VitalsController::class, '__invoke']);
});
