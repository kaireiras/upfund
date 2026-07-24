<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ProjectCategoriesController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Endpoint untuk Landing Page (BFF Pattern)
Route::get('/landing-page', LandingPageController::class);
Route::get('/projects', [ProjectCategoriesController::class, 'index']);
// (Opsional) Jika kamu menggunakan versioning API, bisa dibungkus seperti ini:
// Route::prefix('v1')->group(function () {
//     Route::get('/landing-page', LandingPageController::class);
// });
