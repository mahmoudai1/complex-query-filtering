<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;

Route::middleware('throttle:api')->prefix('/v1')->group(function () {
    Route::get('/jobs', [JobController::class, 'index']);
});
