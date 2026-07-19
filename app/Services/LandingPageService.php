<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class LandingPageService
{
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
        return Project::with(['user', 'projectImages']) // Eager load relasi yang dibutuhkan FE
            ->orderBy('collected_funds', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Mengambil kategori beserta 4 project terbaru di masing-masing kategori.
     */
    private function getProjectsByCategory()
    {
        // Mengambil kategori aktif, lalu memetakan 4 project untuk setiap kategori
        return Category::take(4)->get()->map(function ($category) {
            return [
                'id' => $category->id,
                'title' => $category->title,
                'projects' => Project::with('projectImages')
                    ->whereHas('categories', function ($query) use ($category) {
                        $query->where('categories.id', $category->id);
                    })
                    ->latest('date')
                    ->take(4)
                    ->get()
            ];
        });
    }

    /**
     * Hot Discussed Projects berdasarkan jumlah komentar terbanyak.
     */
    private function getHotDiscussedProjects()
    {
        return Project::with(['user', 'projectImages'])
            ->withCount('comments') // Eloquent otomatis menghitung relasi hasMany ke tabel comments
            ->orderBy('comments_count', 'desc')
            ->take(5)
            ->get();
    }
}
