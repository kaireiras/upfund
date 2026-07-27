<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Seeder kanonik untuk 12 kategori project Upfund.
 *
 * Ini adalah SATU-SATUNYA sumber daftar kategori project. Seeder lain
 * (CategoryAndProjectSeeder, LandingPageSeeder, EndpointTestSeeder) TIDAK lagi
 * menanam daftar kategorinya sendiri — mereka mengambil kategori yang sudah ada
 * di DB (via Category::whereNull('post_id')). DatabaseSeeder memanggil seeder
 * ini paling awal.
 *
 * Memakai firstOrCreate() berdasarkan title agar aman dijalankan berulang
 * (idempoten, tanpa duplikat).
 */
class CategorySeeder extends Seeder
{
    /** Daftar kanonik kategori project (Bahasa Indonesia). */
    public const CATEGORIES = [
        'Teknologi',
        'F&B (Food & Beverage)',
        'Fashion & Retail',
        'Kesehatan',
        'Pendidikan',
        'Properti',
        'Energi & Lingkungan',
        'Pertanian',
        'Transportasi & Logistik',
        'Keuangan',
        'Pariwisata & Hospitality',
        'Hiburan & Media',
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $title) {
            // Kategori project = baris categories tanpa post_id (post_id khusus
            // tag kategori Post). firstOrCreate by title menjaga idempotensi.
            Category::firstOrCreate(['title' => $title]);
        }
    }
}
