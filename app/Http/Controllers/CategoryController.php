<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService,
    ) {}

    /**
     * Daftar semua kategori project (untuk dropdown/checkbox form create project).
     * GET /api/categories  (publik, tanpa auth)
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => 'Categories retrieved successfully',
            'data'    => $this->categoryService->getAllCategories(),
        ]);
    }
}
