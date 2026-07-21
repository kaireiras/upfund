<?php
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthController;

// Endpoint Publik untuk melihat data post
Route::post('/login', [AuthController::class, 'login']);
Route::get('/posts/{id}', [PostController::class, 'show']); // Jika feed mau dibuat publik
Route::get('/comments', [CommentController::class, 'index']);
Route::get('/posts',[PostController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/interactions/like', [PostController::class, 'toggleLike']);
    Route::post('/comments', [CommentController::class, 'store']);
    Route::post('/posts',[PostController::class, 'store']);
});


