<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Category;
use App\Http\Resources\ProjectResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LandingPageService
{
    /**
     * Terapkan agregat interaksi & investor (likes_count, shares_count,
     * investors_count) ke sebuah query/relasi Project dalam satu rangkaian.
     * Menerima Eloquent Builder maupun Relation (mis. saat dipakai di dalam
     * constraint eager-load) agar tetap 1 query gabungan, bukan N+1.
     */
    private function withProjectAggregates($query)
    {
        return $query
            ->withCount(['interactions as likes_count' => fn ($q) => $q->where('like', true)])
            ->withSum(['interactions as shares_count'], 'share')
            ->withCount(['investmentHistories as investors_count' => fn ($q) => $q->select(DB::raw('count(distinct user_id)'))]);
    }

    /**
     * Mengambil semua data agregat untuk Landing Page dengan mekanisme Cache.
     */
    public function getAggregatedData(): array
    {
        // Cache data selama 10 menit (600 detik) untuk mengamankan koneksi Supabase
        return Cache::remember('landing_page_data', 600, function () {
            return [
                'top_projects' => $this->getTopProjects(),
                'category_projects' => $this->getProjectsByCategory(),
                'hot_projects' => $this->getHotDiscussedProjects(),
            ];
        });
    }

    /**
     * Top Projects berdasarkan jumlah dana yang terkumpul terbanyak.
     */
    private function getTopProjects()
    {
        $projects = $this->withProjectAggregates(
            Project::with(['user', 'images']) // Eager load relasi yang dibutuhkan FE
        )
            ->orderBy('collected_funds', 'desc')
            ->take(5)
            ->get();

        return ProjectResource::collection($projects)->resolve();
    }

    /**
     * Mengambil kategori beserta 4 project terbaru di masing-masing kategori.
     */
    private function getProjectsByCategory()
    {
        // Eager load relasi projects (beserta images) untuk semua kategori sekaligus.
        // Menghindari query-in-loop: 3 query total (categories, projects via pivot, images)
        // alih-alih 1 + N query per kategori.
        return Category::with([
            'projects' => function ($query) {
                $this->withProjectAggregates($query->with('images')->latest('date'));
            },
        ])
            ->take(4)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'title' => $category->title,
                    // Ambil 4 project terbaru per kategori (ordering date desc dari eager load di atas).
                    'projects' => ProjectResource::collection($category->projects->take(4))->resolve(),
                ];
            });
    }

    /**
     * Hot Discussed Projects berdasarkan jumlah komentar terbanyak.
     */
    private function getHotDiscussedProjects()
    {
        $projects = $this->withProjectAggregates(
            Project::with(['user', 'images'])
        )
            ->withCount('comments') // Eloquent otomatis menghitung relasi hasMany ke tabel comments
            ->orderBy('comments_count', 'desc')
            ->take(5)
            ->get();

        return ProjectResource::collection($projects)->resolve();
    }
}
