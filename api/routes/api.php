<?php

use App\Http\Controllers\Github\CollaboratorController;
use App\Http\Controllers\Github\CommitController;
use App\Http\Controllers\Github\ContributorController;
use App\Http\Controllers\Github\RepoController;
use App\Http\Controllers\Org\OrgUserController;
use App\Http\Controllers\HealthcheckController;
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

/*
Route::group(['prefix' => 'v1'], function () {
    Route::group(['prefix' => 'github'], function () {
        Route::apiResource('commits', CommitController::class)
            ->only(['index', 'show']);
        Route::apiResource('repos', RepoController::class)
            ->only(['index', 'show']);
        Route::apiResource('contributors', ContributorController::class)
            ->only(['index', 'show']);
        Route::apiResource('collaborators', CollaboratorController::class)
            ->only(['index', 'show']);
        Route::get('commitstats', [CommitController::class, 'stat']);
    });
    Route::group(['prefix' => 'org'], function () {
        Route::apiResource('users', OrgUserController::class)
            ->only(['index', 'show'])
            ->parameters(['users' => 'id']);
    });
})->middleware('throttle:500,1');
*/

Route::prefix('v1')->middleware('throttle:500,1')->group(function () {
    Route::prefix('github')->group(function () {
        Route::apiResource('commits', CommitController::class)
            ->only(['index']);
        Route::apiResource('repos', RepoController::class)
            ->only(['index']);
        Route::apiResource('contributors', ContributorController::class)
            ->only(['index']);
        Route::apiResource('collaborators', CollaboratorController::class)
            ->only(['index']);
        Route::get('commitstats', [CommitController::class, 'stat']);
    });
    Route::prefix('org')->group(function () {
        Route::apiResource('users', OrgUserController::class)
            ->only(['index', 'show'])
            ->parameters(['users' => 'id']);
    });
    Route::get('/healthcheck', [HealthcheckController::class, 'Healthcheck']);
});
