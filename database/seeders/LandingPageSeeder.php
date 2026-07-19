<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Project;
use Faker\Factory as Faker;

class LandingPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Siapkan Faker (Locale Indonesia)
        $faker = Faker::create('id_ID');

        // 1. Buat 5 User Dummy (Sudah diperbaiki: username dan role ditambahkan)
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $users[] = User::create([
                'username'    => $faker->unique()->userName(),
                'name'        => $faker->name(),
                'email'       => $faker->unique()->safeEmail(),
                'password'    => Hash::make('password'), // default password: password
                'role'        => $faker->randomElement(['investor', 'creator', 'user']),
                'is_verified' => $faker->boolean(80), // 80% kemungkinan user sudah terverifikasi
            ]);
        }

        // 2. Buat Kategori Dummy
        $categoryNames = [
            'Teknologi', 'Kesehatan', 'Pendidikan',
            'Agrikultur', 'Energi Terbarukan', 'F&B', 'Kreatif'
        ];
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[] = Category::create([
                'title' => $name,
            ]);
        }

        // 3. Buat 15 Project Dummy untuk variasi data
        foreach (range(1, 15) as $index) {
            // (Sudah diperbaiki: title maksimal 100 char, valuation dan collected_funds diisi)
            $project = Project::create([
                'user_id'         => $users[array_rand($users)]->id,
                'title'           => rtrim($faker->words(3, true), '.'), // 3 kata acak
                'description'     => $faker->paragraph(3),
                'date'            => now()->subDays(rand(1, 30)),
                'video_url'       => $faker->boolean(50) ? 'https://www.youtube.com/watch?v=' . $faker->lexify('???????????') : null,
                'valuation'       => $faker->numberBetween(100000000, 2000000000), // Maksimal 2 Miliar
                'collected_funds' => $faker->numberBetween(10000000, 500000000), // 10 Juta - 500 Juta
                'investment_url'  => $faker->boolean(50) ? $faker->url() : null,
            ]);

            // --- RELASI MANY-TO-MANY (project_categories) ---
            // Mengambil 1 sampai 3 ID kategori secara acak
            $randomCategories = $faker->randomElements($categories, rand(1, 3));
            $categoryIds = array_map(fn($cat) => $cat->id, $randomCategories);
            // Asumsi di model Project ada fungsi: public function categories() { return $this->belongsToMany(Category::class, 'project_categories'); }
            $project->categories()->attach($categoryIds);

            // --- DATA DUMMY PROJECT IMAGES ---
            foreach (range(1, rand(1, 3)) as $imgIndex) {
                DB::table('project_images')->insert([
                    'project_id' => $project->id,
                    'image_url'  => $faker->imageUrl(800, 600, 'business', true, 'Upfund'),
                ]);
            }

            // --- DATA DUMMY COMMENTS (Polymorphic) ---
// --- DATA DUMMY COMMENTS (Sesuai Skema Kustom) ---
            foreach (range(1, rand(2, 5)) as $cmdIndex) {
                DB::table('comments')->insert([
                    'user_id'     => $users[array_rand($users)]->id,
                    'target_type' => 'project', // Sesuai dengan enum ['project', 'post']
                    'target_id'   => $project->id,
                    'comment'     => $faker->sentence(6), // Menggunakan 'comment' bukan 'content'
                    // Kolom 'date' akan otomatis terisi current_timestamp
                    // Kolom 'project_url' nullable, jadi bisa dibiarkan kosong
                ]);
            }
        }
    }
}
