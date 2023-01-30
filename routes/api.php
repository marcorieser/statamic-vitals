<?php

use Illuminate\Support\Facades\Route;
use MarcoRieser\Vitals\Http\Controllers\VitalsController;

Route::prefix('api/statamic-vitals')->group(function () {
    Route::post('vitals', [VitalsController::class, '__invoke']);
});
