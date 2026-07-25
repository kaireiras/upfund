<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Hapus atau comment kode User::factory() bawaan Laravel
        // \App\Models\User::factory(10)->create();

        // Panggil seeder yang sudah kita buat
        $this->call([
            CategoryAndProjectSeeder::class,
            LandingPageSeeder::class,
            EndpointTestSeeder::class,
        ]);
    }
}
