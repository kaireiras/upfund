<?php

namespace App\Services;

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Cache;

class CategoryService
{
    /** Key cache untuk daftar kategori project. */
    public const CACHE_KEY = 'categories_all';

    /**
     * TTL 3600 dtk (1 jam) — lebih lama dari default 600 dtk karena daftar
     * kategori nyaris statis (hanya berubah lewat seeding / admin, sangat jarang),
     * sehingga aman di-cache lebih agresif untuk mengurangi beban DB.
     */
    private const TTL = 3600;

    /**
     * Ambil semua kategori project ({id, title}), diurutkan alfabetis by title.
     *
     * Hanya kategori project (post_id null) yang dikembalikan — baris categories
     * ber-post_id adalah tag kategori Post (konsep berbeda), tidak relevan untuk
     * form pembuatan project.
     *
     * Meng-cache HASIL AKHIR (array scalar via ->resolve()) sesuai konvensi
     * CLAUDE.md — bukan objek Eloquent/Resource.
     */
    public function getAllCategories(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL, function () {
            $categories = Category::whereNull('post_id')
                ->orderBy('title')
                ->get();

            return CategoryResource::collection($categories)->resolve();
        });
    }

    /**
     * Invalidate cache kategori. Panggil setelah kategori di-create/update/delete
     * (mis. dari admin) agar daftar tidak basi.
     */
    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
