<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Carbon;

    class ProjectDetailTestSeeder extends Seeder
{
    /**
     * Project (yang SUDAH ADA) yang akan diisi data lengkap untuk testing manual.
     * Keduanya sudah punya comments dari seeder sebelumnya.
     */
    private array $targetProjectIds = [14, 15];

    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $userIds = User::orderBy('id')->pluck('id')->all();

        foreach ($this->targetProjectIds as $projectId) {
            $this->seedTimelines($projectId);
            $this->seedMilestones($projectId, $faker);
            $this->seedShareholders($projectId, $faker);
            $this->seedPublicEvents($projectId, $faker);
            $this->seedPosts($projectId, $faker);
            $this->seedInteractions($projectId, $faker, $userIds);
            $this->seedInvestmentHistories($projectId, $faker, $userIds);

            // Bersihkan cache detail agar data baru langsung terlihat
            Cache::forget("project_detail_{$projectId}");
        }

        $this->command?->info('Seeded detail data untuk project_id: ' . implode(', ', $this->targetProjectIds));
    }

    /**
     * 4 baris: satu untuk tiap fase, date berurutan.
     */
    private function seedTimelines(int $projectId): void
    {
        $phases = [
            ['created',  'Project dibuat',        Carbon::now()->subDays(40)],
            ['verified', 'Project terverifikasi', Carbon::now()->subDays(30)],
            ['listed',   'Project listing publik', Carbon::now()->subDays(20)],
            ['closed',   'Penggalangan ditutup',  Carbon::now()->subDays(5)],
        ];

        foreach ($phases as [$timeline, $title, $date]) {
            DB::table('investment_timelines')->insert([
                'project_id' => $projectId,
                'title'      => $title,
                'timeline'   => $timeline,
                'date'       => $date,
            ]);
        }
    }

    /**
     * 3 baris: from/to kronologis, budget bervariasi.
     */
    private function seedMilestones(int $projectId, $faker): void
    {
        $milestones = [
            ['Riset & Perencanaan', Carbon::now()->subDays(40), Carbon::now()->subDays(30), 25_000_000],
            ['Pengembangan Produk', Carbon::now()->subDays(29), Carbon::now()->subDays(10), 75_000_000],
            ['Peluncuran & Skalasi', Carbon::now()->subDays(9), Carbon::now()->addDays(20), 150_000_000],
        ];

        foreach ($milestones as [$title, $from, $to, $budget]) {
            DB::table('milestones')->insert([
                'project_id'  => $projectId,
                'title'       => $title,
                'description' => $faker->paragraph(2),
                'from'        => $from,
                'to'          => $to,
                'budget'      => $budget,
            ]);
        }
    }

    /**
     * 3-4 baris: name random, share 20-40% masing-masing.
     */
    private function seedShareholders(int $projectId, $faker): void
    {
        $count = rand(3, 4);
        for ($i = 0; $i < $count; $i++) {
            DB::table('shareholders')->insert([
                'project_id' => $projectId,
                'name'       => $faker->name(),
                'share'      => $faker->randomFloat(2, 20, 40),
            ]);
        }
    }

    /**
     * 2 baris public events.
     */
    private function seedPublicEvents(int $projectId, $faker): void
    {
        for ($i = 0; $i < 2; $i++) {
            DB::table('public_events')->insert([
                'project_id'  => $projectId,
                'title'       => 'Event: ' . rtrim($faker->words(3, true), '.'),
                'description' => $faker->paragraph(2),
                'url'         => $faker->url(),
                'location'    => $faker->city(),
                'notes'       => $faker->sentence(8),
                'date'        => Carbon::now()->addDays(($i + 1) * 7),
            ]);
        }
    }

    /**
     * 8 baris posts (>5 supaya preview vs full list beda).
     * FK-nya project_url (string), bukan project_id.
     */
    private function seedPosts(int $projectId, $faker): void
    {
        for ($i = 0; $i < 8; $i++) {
            DB::table('posts')->insert([
                'image_url'   => $faker->imageUrl(800, 600, 'business', true, 'Upfund'),
                'description' => $faker->paragraph(2),
                'date'        => Carbon::now()->subDays(rand(1, 20))->subHours($i),
                'project_url' => (string) $projectId,
            ]);
        }
    }

    /**
     * Minimal 5 interaction: like true/false bervariasi, share random 1-10.
     */
    private function seedInteractions(int $projectId, $faker, array $userIds): void
    {
        for ($i = 0; $i < 6; $i++) {
            DB::table('interaction')->insert([
                'target_id'      => $projectId,
                'target_type'    => 'project',
                'like'           => $faker->boolean(60),
                'share'          => rand(1, 10),
                'not_interested' => $faker->boolean(15),
                'user_id'        => $userIds[array_rand($userIds)],
                'date'           => Carbon::now()->subDays(rand(1, 15)),
            ]);
        }
    }

    /**
     * 3-4 investment_histories dengan user_id sengaja ada yang duplikat,
     * untuk memastikan investors_count (count distinct user_id) benar.
     * Butuh transaction dulu karena transaction_id NOT NULL (FK).
     */
    private function seedInvestmentHistories(int $projectId, $faker, array $userIds): void
    {
        // user_id sengaja mengandung duplikat: mis. [A, A, B, C] -> distinct = 3
        $picked = [$userIds[0], $userIds[0], $userIds[1], $userIds[2]];

        foreach ($picked as $uid) {
            $transactionId = DB::table('transactions')->insertGetId([
                'amount'     => $faker->numberBetween(1_000_000, 50_000_000),
                'user_id'    => $uid,
                'project_id' => $projectId,
                'date'       => Carbon::now()->subDays(rand(1, 15)),
                'status'     => 'paid',
            ]);

            DB::table('investment_histories')->insert([
                'project_id'     => $projectId,
                'transaction_id' => $transactionId,
                'user_id'        => $uid,
            ]);
        }
    }
}
