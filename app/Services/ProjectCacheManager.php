<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Mengelola key-registry cache untuk data project.
 *
 * Cache key /projects bersifat dinamis (projects_category_{cat}_page_{n}), jadi
 * kita tidak bisa menebak semua key saat invalidasi. Driver cache default project
 * ini adalah `database` yang TIDAK mendukung cache tags. Solusinya: registry —
 * setiap key project yang di-cache didaftarkan ke satu set (PROJECT_INDEX_KEY);
 * saat data project berubah, kita loop registry itu dan forget semuanya.
 *
 * Lihat konvensi "cache invalidation (key-registry)" di CLAUDE.md.
 */
class ProjectCacheManager
{
    /** Key yang menampung daftar semua cache key project dinamis. */
    public const PROJECT_INDEX_KEY = 'projects_cache_keys';

    /** Key statis yang juga menampilkan data project (landing page). */
    public const LANDING_PAGE_KEY = 'landing_page_data';

    /** TTL untuk registry — cukup panjang agar tak kedaluwarsa sebelum data cache. */
    private const INDEX_TTL = 86400; // 24 jam

    /**
     * Daftarkan sebuah cache key ke registry (idempoten).
     * Dipanggil oleh service pembaca tiap kali membuat entry cache project.
     */
    public function register(string $key): void
    {
        $keys = Cache::get(self::PROJECT_INDEX_KEY, []);

        if (! in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::put(self::PROJECT_INDEX_KEY, $keys, self::INDEX_TTL);
        }
    }

    /**
     * Invalidate SEMUA cache terkait project: landing page + seluruh key dinamis
     * yang terdaftar di registry, lalu bersihkan registry itu sendiri.
     * Dipanggil setelah mutasi project (create/update/delete) berhasil di-commit.
     */
    public function flush(): void
    {
        Cache::forget(self::LANDING_PAGE_KEY);

        foreach (Cache::get(self::PROJECT_INDEX_KEY, []) as $key) {
            Cache::forget($key);
        }

        Cache::forget(self::PROJECT_INDEX_KEY);
    }
}
