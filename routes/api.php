<?php

use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes – AJAX calls from the frontend
|--------------------------------------------------------------------------
|
| These routes are stateless / session-less by default (Sanctum SPA auth
| or public). Throttle limits are applied per route group.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public – no auth required
    |--------------------------------------------------------------------------
    */

    // Live / instant search (autocomplete)
    Route::get('/search', [SearchController::class, 'index'])
        ->middleware('throttle:60,1')
        ->name('search');

    // Newsletter subscription
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
        ->middleware('throttle:10,1')
        ->name('newsletter.subscribe');

    /*
    |--------------------------------------------------------------------------
    | Comment submission (auth optional – guests allowed)
    |--------------------------------------------------------------------------
    */

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('comments.store');
});
