<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ArticleController as V1ArticleController;
use App\Http\Controllers\Api\V1\AdminArticleController;
use App\Http\Controllers\Api\V1\WriterArticleController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V2\ArticleController as V2ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\AttachmentController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('throttle:api_limiter')->group(function () {


    Route::prefix('v1')->group(function () {

        Route::post('/articles/{article}/comments', [CommentController::class, 'store']);

        Route::post('articles/{article}/attachments', [AttachmentController::class, 'store']);

        Route::get('/articles', [V1ArticleController::class, 'index']);
        Route::get('/articles/{article}', [V1ArticleController::class, 'show']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::middleware('auth:sanctum')->group(function () {

            Route::post('/articles', [V1ArticleController::class, 'store']);
            Route::put('/articles/{article}', [V1ArticleController::class, 'update']);
            Route::delete('/articles/{article}', [V1ArticleController::class, 'destroy']);

            Route::post('/articles/{article}/publish', [WriterArticleController::class, 'publish']);
            Route::post('/articles/{article}/review', [AdminArticleController::class, 'review']);
        });
    });
    Route::prefix('v2')->group(function () {

        Route::get('/articles', [V2ArticleController::class, 'index']);
        Route::get('/articles/{article}', [V2ArticleController::class, 'show']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/articles', [V2ArticleController::class, 'store']);
            Route::put('/articles/{article}', [V2ArticleController::class, 'update']);
            Route::delete('/articles/{article}', [V2ArticleController::class, 'destroy']);
        });
    });
});
