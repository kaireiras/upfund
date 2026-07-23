<?php
use App\Http\Controllers\AdminKycController;
use App\Http\Controllers\KycController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserProfileController;

// Endpoint Publik untuk melihat data post
Route::post('/login', [AuthController::class, 'login']);
Route::get('/posts/{id}', [PostController::class, 'show']); // Jika feed mau dibuat publik
Route::get('/comments', [CommentController::class, 'index']);
Route::get('/posts',[PostController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/interactions/like', [PostController::class, 'toggleLike']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::post('/posts',[PostController::class, 'store']);

    Route::post('/kyc', [KycController::class, 'store']);
    Route::get('/kyc/status', [KycController::class, 'status']);
    Route::post('/admin/kyc/{id}/verify', [AdminKycController::class, 'verify']);
    Route::get('/profile', [UserProfileController::class, 'show']);
    Route::put('/profile', [UserProfileController::class, 'update']);
});


