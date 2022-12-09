<?php

use App\Http\Controllers\Github\CollaboratorController;
use App\Http\Controllers\Github\CommitController;
use App\Http\Controllers\Github\ContributorController;
use App\Http\Controllers\Github\RepoController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('throttle:500,1')->group(function () {
    Route::prefix('github')->group(function () {
        Route::apiResource('commits', CommitController::class)
            ->only(['index', 'show']);
        Route::get('commitstats', [CommitController::class, 'stat']);
        Route::apiResource('repos', RepoController::class)
            ->only(['index', 'show']);
        Route::apiResource('contributors', ContributorController::class)
            ->only(['index', 'show']);
        Route::apiResource('collaborators', CollaboratorController::class)
            ->only(['index', 'show']);
    });
});
