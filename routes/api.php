<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingPageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Endpoint untuk Landing Page (BFF Pattern)
Route::get('/landing-page', LandingPageController::class);

// (Opsional) Jika kamu menggunakan versioning API, bisa dibungkus seperti ini:
// Route::prefix('v1')->group(function () {
//     Route::get('/landing-page', LandingPageController::class);
// });
