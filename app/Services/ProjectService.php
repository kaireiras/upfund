<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    public function __construct(
        private ProjectCacheManager $cache,
    ) {}

    /**
     * Buat project baru beserta relasi (cover image, categories, shareholders,
     * milestones) secara atomik, lalu invalidate cache project.
     *
     * @param  array  $data    Data tervalidasi dari StoreProjectRequest.
     * @param  int    $userId  Owner project (dari user yang login).
     * @return Project          Project baru dengan relasi ter-load, siap di-Resource.
     */
    public function create(array $data, int $userId): Project
    {
        $project = DB::transaction(function () use ($data, $userId) {
            // 1. Project utama. Pemetaan field FE -> kolom DB:
            //    video_pitch_url -> video_url, company_valuation -> valuation.
            //    collected_funds selalu 0 saat create; date = now().
            $project = Project::create([
                'user_id'         => $userId,
                'title'           => $data['title'],
                'description'     => $data['description'],
                'date'            => now(),
                'video_url'       => $data['video_pitch_url'],
                'valuation'       => $data['company_valuation'],
                'funding_target'  => $data['funding_target'],
                'collected_funds' => 0,
            ]);

            // 2. Cover image = satu baris project_images (konvensi: baris pertama = cover).
            $project->images()->create([
                'image_url' => $data['cover_image_url'],
            ]);

            // 3. Categories (many-to-many) -> pivot project_categories.
            $project->categories()->attach($data['categories']);

            // 4. Shareholders. owned_equity_percent -> kolom share (decimal 5,2).
            $shareholders = array_map(fn ($sh) => [
                'name'  => $sh['name'],
                'share' => $sh['owned_equity_percent'],
            ], $data['shareholders']);
            $project->shareholders()->createMany($shareholders);

            // 5. Milestones. Pemetaan: description(target_objectives) -> description,
            //    start_date -> from, end_date -> to, release_budget_percent -> budget.
            $milestones = array_map(fn ($ms) => [
                'title'       => $ms['title'],
                'description' => $ms['description'],
                'from'        => $ms['start_date'],
                'to'          => $ms['end_date'],
                'budget'      => $ms['release_budget_percent'],
            ], $data['milestones']);
            $project->milestones()->createMany($milestones);

            return $project;
        });

        // Invalidate cache project SETELAH commit sukses (landing_page_data + semua
        // key projects_category_*_page_* yang terdaftar di registry).
        $this->cache->flush();

        // Muat relasi untuk response detail (reuse ProjectDetailResource).
        return $project->load([
            'user',
            'categories',
            'images',
            'milestones',
            'shareholders',
        ]);
    }
}
