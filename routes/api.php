<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ProjectCategoriesController;
use App\Http\Controllers\ProjectDetailController;
use App\Http\Controllers\AdminKycController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\InvestmentHistoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Endpoint untuk Landing Page (BFF Pattern)
Route::get('/landing-page', LandingPageController::class);
Route::get('/projects', [ProjectCategoriesController::class, 'index']);
Route::get('/projects/{id}', [ProjectDetailController::class, 'show']);
Route::get('/projects/{id}/comments', [ProjectDetailController::class, 'comments']);
Route::get('/projects/{id}/posts', [ProjectDetailController::class, 'posts']);
Route::get('/projects/{id}/investors', [InvestmentHistoryController::class, 'projectInvestors']);
// Autentikasi
Route::post('/login', [AuthController::class, 'login']);

// Endpoint Publik Posts & Comments
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::get('/comments', [CommentController::class, 'index']);
Route::get('/posts', [PostController::class, 'index']);
Route::post('/midtrans/notification', [TransactionController::class, 'notificationHandler']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/interactions/like', [PostController::class, 'toggleLike']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    Route::post('/posts', [PostController::class, 'store']);

    Route::post('/kyc', [KycController::class, 'store']);
    Route::get('/kyc/status', [KycController::class, 'status']);
    Route::post('/admin/kyc/{id}/verify', [AdminKycController::class, 'verify']);
    Route::get('/profile', [UserProfileController::class, 'show']);
    Route::put('/profile', [UserProfileController::class, 'update']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/user/investment-histories', [InvestmentHistoryController::class, 'index']);
});
