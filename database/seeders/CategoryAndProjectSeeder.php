<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CategoryAndProjectSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Pastikan ada minimal 1 User di database (Mengacu pada seeder yang sukses)
        if (DB::table('users')->count() === 0) {
            DB::table('users')->insert([
                'username'    => 'admin_upfund',
                'name'        => 'Admin Upfund',
                'email'       => 'admin@upfund.test',
                'password'    => Hash::make('password123'),
                'role'        => 'user',        // Diwajibkan oleh database
                'is_verified' => true,          // Diwajibkan oleh database
            ]);
        }

        // 2. Buat Kategori
        $categories = ['Teknologi', 'Fintech', 'Sosial', 'Lingkungan'];
        foreach ($categories as $cat) {
            // Gunakan updateOrInsert agar tidak duplicate jika seeder dijalankan berulang
            DB::table('categories')->updateOrInsert(
                ['title' => $cat],
                ['title' => $cat]
            );
        }

        // 3. Ambil ID kategori dan user
        $categoryIds = DB::table('categories')->pluck('id')->toArray();
        $userIds = DB::table('users')->pluck('id')->toArray();

        // 4. Buat Proyek (sesuaikan dengan migration projects)
        foreach (range(1, 10) as $i) {
            $projectId = DB::table('projects')->insertGetId([
                'user_id'         => $userIds[array_rand($userIds)],
                'title'           => rtrim($faker->words(3, true), '.'),
                'description'     => $faker->paragraph(),
                'valuation'       => rand(1000000, 5000000),
                'collected_funds' => rand(0, 1000000),
                'date'            => now()->subDays(rand(1, 30)), // Agar tanggal bervariasi
            ]);

            // Hubungkan ke kategori (pivot)
            DB::table('project_categories')->insert([
                'project_id'  => $projectId,
                'category_id' => $categoryIds[array_rand($categoryIds)],
            ]);

            // 5. Tambahkan Dummy Image (Agar testing relasi API berjalan sempurna)
            DB::table('project_images')->insert([
                'project_id' => $projectId,
                'image_url'  => $faker->imageUrl(800, 600, 'business', true, 'Upfund'),
            ]);
        }
    }
}











