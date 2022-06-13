<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('api')
    ->group(function() {
        Route::prefix('auth')
            ->controller(\App\Http\Controllers\v1\AuthController::class)
            ->group(function() {
                Route::post('/register', 'register');
                Route::post('/login', 'login');
                Route::post('/forgot-password', 'forgotPassword');
                Route::post('/reset-password', 'resetPassword');
            });

        Route::middleware('auth:sanctum')
            ->group(function() {
                Route::post('/auth/logout', [\App\Http\Controllers\v1\AuthController::class, 'logout']);
            });
    });

// Redirect section

Route::get('/auth/forgot-password', function() {
    return redirect();
})->name('password.reset');
