<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LandingPageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log; // Tambahkan ini untuk mencatat error (opsional tapi disarankan)

class LandingPageController extends Controller
{
    public function __construct(
        private readonly LandingPageService $landingPageService
    ) {}

    /**
     * Endpoint: GET /api/landing-page
     */
    public function __invoke(): JsonResponse
    {
        try {
            // Ambil data dari Service
            $data = $this->landingPageService->getAggregatedData();

            // Return response dengan format manual (tanpa DTO)
            return response()->json([
                'status'  => 'success',
                'message' => 'Landing page data fetched successfully',
                'data'    => $data
            ], 200);

        } catch (\Exception $e) {
            // Catat error di file log Laravel (storage/logs/laravel.log) untuk debugging
            Log::error('Landing Page Fetch Error: ' . $e->getMessage());

            // Return error response dengan format yang konsisten
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to fetch landing page data',
                'error'   => $e->getMessage() // Di production, ini bisa disembunyikan agar lebih aman
            ], 500);
        }
    }
}
