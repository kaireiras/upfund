<?php

namespace App\Services;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProjectCategoriesService
{
    public function getProjectsByCategory(?string $categorySlug = null, int $perPage = 9, int $page = 1): array
    {
        try {
            // Cache key unik per kombinasi kategori + halaman
            $cacheKey = "projects_category_" . ($categorySlug ?? 'all') . "_page_{$page}";

            // Cache HASIL AKHIR (array), bukan objek paginator — objek paginator
            // gagal di-unserialize lintas-proses ("incomplete object").
            return Cache::remember($cacheKey, 600, function () use ($categorySlug, $perPage, $page) {
                $query = Project::with(['images', 'categories', 'user'])
                    ->withCount(['interactions as likes_count' => fn ($q) => $q->where('like', true)])
                    ->withSum(['interactions as shares_count'], 'share')
                    ->withCount(['investmentHistories as investors_count' => fn ($q) => $q->select(DB::raw('count(distinct user_id)'))]);

                if ($categorySlug) {
                    $query->whereHas('categories', function ($q) use ($categorySlug) {
                        $q->where('title', $categorySlug);
                    });
                }

                $projects = $query->latest('id')->paginate($perPage, ['*'], 'page', $page);

                return [
                    'status' => 'success',
                    'message' => 'Projects retrieved successfully',
                    'data' => ProjectResource::collection($projects->getCollection())->resolve(),
                    'meta' => [
                        'current_page' => $projects->currentPage(),
                        'per_page' => $projects->perPage(),
                        'total' => $projects->total(),
                        'last_page' => $projects->lastPage(),
                        'has_more_pages' => $projects->hasMorePages(),
                    ],
                ];
            });
        } catch (Throwable $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve projects',
                'error' => $e->getMessage(),
            ];
        }
    }
}
