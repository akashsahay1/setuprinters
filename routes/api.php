<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScanApiController;
use App\Http\Middleware\VerifyScanApiToken;

Route::get('user/list', [UserController::class, 'index']);

Route::middleware(VerifyScanApiToken::class)->group(function () {
    Route::post('scan', [ScanApiController::class, 'store']);
});
