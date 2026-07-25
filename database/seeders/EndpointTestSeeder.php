<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Project;
use App\Models\Category;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Interaction;
use App\Models\Kyc;
use Faker\Factory as Faker;

/**
 * Seeder untuk pengujian semua endpoint API.
 *
 * Akun test:
 *   - user biasa  : test@upfund.com / password
 *   - admin/creator: creator@upfund.com / password
 *
 * Jalankan: php artisan db:seed --class=EndpointTestSeeder
 */
class EndpointTestSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // ----------------------------------------------------------------
        // 1. USERS
        // ----------------------------------------------------------------
        $testUser = User::firstOrCreate(
            ['email' => 'test@upfund.com'],
            [
                'username'    => 'testuser',
                'name'        => 'Test User',
                'password'    => Hash::make('password'),
                'role'        => 'investor',
                'is_verified' => true,
            ]
        );

        $creator = User::firstOrCreate(
            ['email' => 'creator@upfund.com'],
            [
                'username'    => 'testcreator',
                'name'        => 'Test Creator',
                'password'    => Hash::make('password'),
                'role'        => 'creator',
                'is_verified' => true,
            ]
        );

        // 3 user tambahan untuk variasi data
        $extraUsers = [];
        for ($i = 1; $i <= 3; $i++) {
            $extraUsers[] = User::firstOrCreate(
                ['email' => "dummy{$i}@upfund.com"],
                [
                    'username'    => "dummyuser{$i}",
                    'name'        => $faker->name(),
                    'password'    => Hash::make('password'),
                    'role'        => 'user',
                    'is_verified' => true,
                ]
            );
        }

        // ----------------------------------------------------------------
        // 2. KYC — untuk endpoint GET /kyc/status & POST /admin/kyc/{id}/verify
        // ----------------------------------------------------------------
        Kyc::firstOrCreate(
            ['user_id' => $testUser->id],
            [
                'company_document_url' => 'https://example.com/docs/company.pdf',
                'bank_document_url'    => 'https://example.com/docs/bank.pdf',
                'address'              => 'Jl. Test No. 1, Jakarta',
                'status'               => 'pending',
                'rejection_reason'     => null,
            ]
        );

        // ----------------------------------------------------------------
        // 3. PROJECT CATEGORIES (untuk filter GET /projects?category=...)
        // ----------------------------------------------------------------
        $categoryNames = ['Teknologi', 'Kesehatan', 'Pendidikan', 'Agrikultur', 'Energi Terbarukan'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[] = Category::firstOrCreate(['title' => $name]);
        }

        // ----------------------------------------------------------------
        // 4. PROJECTS — untuk GET /projects, GET /projects/{id}, landing-page
        // ----------------------------------------------------------------
        $projects = [];
        for ($i = 0; $i < 12; $i++) {
            $project = Project::create([
                'user_id'         => $i % 3 === 0 ? $creator->id : $extraUsers[$i % 3]->id,
                'title'           => $faker->words(3, true),
                'description'     => $faker->paragraph(3),
                'date'            => now()->subDays(rand(1, 60)),
                'video_url'       => $i % 2 === 0 ? 'https://www.youtube.com/watch?v=dQw4w9WgXcQ' : null,
                'valuation'       => $faker->numberBetween(100_000_000, 2_000_000_000),
                'collected_funds' => $faker->numberBetween(10_000_000, 500_000_000),
                'investment_url'  => $faker->url(),
            ]);

            $project->categories()->attach(
                collect($categories)->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Project images
            foreach (range(1, rand(1, 3)) as $j) {
                DB::table('project_images')->insert([
                    'project_id' => $project->id,
                    'image_url'  => "https://picsum.photos/seed/{$project->id}{$j}/800/600",
                ]);
            }

            // Status timeline
            DB::table('investment_timelines')->insert([
                'project_id' => $project->id,
                'title'      => 'Seed Round',
                'timeline'   => collect(['created', 'verified', 'listed', 'closed'])->random(),
                'date'       => now()->subDays(rand(1, 30)),
            ]);

            // Milestones
            DB::table('milestones')->insert([
                'project_id'  => $project->id,
                'title'       => 'MVP Launch',
                'description' => $faker->sentence(),
                'from'        => now()->subDays(30),
                'to'          => now()->addDays(60),
                'budget'      => $faker->numberBetween(10_000_000, 100_000_000),
            ]);

            // Shareholders
            DB::table('shareholders')->insert([
                'project_id' => $project->id,
                'name'       => $faker->company(),
                'share'      => rand(10, 40),
            ]);

            // Public events
            DB::table('public_events')->insert([
                'project_id'  => $project->id,
                'title'       => 'Demo Day',
                'description' => $faker->sentence(),
                'url'         => $faker->url(),
                'location'    => $faker->city(),
                'notes'       => null,
                'date'        => now()->addDays(rand(1, 30)),
            ]);

            // Comments on project (polymorphic)
            foreach (range(1, rand(2, 4)) as $k) {
                DB::table('comments')->insert([
                    'user_id'     => $extraUsers[array_rand($extraUsers)]->id,
                    'target_type' => 'project',
                    'target_id'   => $project->id,
                    'comment'     => $faker->sentence(),
                    'date'        => now()->subMinutes(rand(1, 1440)),
                ]);
            }

            // Interactions on project
            DB::table('interaction')->insert([
                'user_id'     => $testUser->id,
                'target_type' => 'project',
                'target_id'   => $project->id,
                'like'        => true,
                'share'       => rand(0, 5),
                'not_interested' => false,
                'date'        => now(),
            ]);

            $projects[] = $project;
        }

        // ----------------------------------------------------------------
        // 5. POSTS — untuk GET /posts, GET /posts/{id}, POST /posts
        // ----------------------------------------------------------------
        $postCategories = ['software', 'iot', 'green tech', 'robotics', 'hardware', 'agriculture'];

        for ($i = 0; $i < 10; $i++) {
            $linkedProject = $i % 2 === 0 ? $projects[$i % count($projects)] : null;

            $post = Post::create([
                'image_url'   => "https://picsum.photos/seed/post{$i}/800/600",
                'description' => $faker->paragraph(2),
                'project_url' => $linkedProject?->id,
                'date'        => now()->subHours(rand(1, 72)),
            ]);

            // Post categories (HasMany via post_id)
            $selectedCats = collect($postCategories)->random(rand(1, 2));
            foreach ($selectedCats as $catTitle) {
                DB::table('categories')->insert([
                    'title'   => $catTitle,
                    'post_id' => $post->id,
                ]);
            }

            // Comments on post
            foreach (range(1, rand(1, 3)) as $k) {
                DB::table('comments')->insert([
                    'user_id'     => $extraUsers[array_rand($extraUsers)]->id,
                    'target_type' => 'post',
                    'target_id'   => $post->id,
                    'comment'     => $faker->sentence(),
                    'date'        => now()->subMinutes(rand(1, 720)),
                ]);
            }

            // Interactions on post
            DB::table('interaction')->insert([
                'user_id'        => $testUser->id,
                'target_type'    => 'post',
                'target_id'      => $post->id,
                'like'           => (bool) rand(0, 1),
                'share'          => rand(0, 10),
                'not_interested' => false,
                'date'           => now(),
            ]);
        }

        $this->command->info('EndpointTestSeeder selesai.');
        $this->command->info('Login: test@upfund.com / password');
        $this->command->info('Login: creator@upfund.com / password');
    }
}
