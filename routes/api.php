<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ProjectCategoriesController;
use App\Http\Controllers\ProjectDetailController;
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
// (Opsional) Jika kamu menggunakan versioning API, bisa dibungkus seperti ini:
// Route::prefix('v1')->group(function () {
//     Route::get('/landing-page', LandingPageController::class);
// });
