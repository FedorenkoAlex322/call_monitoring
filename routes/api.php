<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CallController;
use App\Http\Controllers\Api\CdrController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/account', [AccountController::class, 'show']);
    Route::get('/account/balance', [AccountController::class, 'balance']);

    Route::get('/calls/active', [CallController::class, 'active']);

    Route::get('/cdrs', [CdrController::class, 'index']);
    Route::get('/cdrs/{cdr}', [CdrController::class, 'show']);
});
