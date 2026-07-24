<?php

namespace App\Services;

use App\Models\Project;
use App\Http\Resources\ProjectDetailResource;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class ProjectDetailService
{
    public function getDetail(int $id): array
    {
        try {
            $cacheKey = "project_detail_{$id}";

            return Cache::remember($cacheKey, 600, function () use ($id) {
                $project = Project::with([
                    'user',
                    'categories',
                    'images',
                    'timelines',
                    'milestones',
                    'shareholders',
                    'publicEvents',
                    'postsPreview',
                    'commentsPreview.user',
                ])
                ->withCount([
                    'interactions as likes_count'     => fn ($q) => $q->where('like', true),
                    'investmentHistories as investors_count' => fn ($q) => $q->select(DB::raw('count(distinct user_id)')),
                    'posts as posts_count',
                    'comments as comments_count',
                ])
                ->withSum(['interactions as shares_count'], 'share')
                ->findOrFail($id);

                return [
                    'status'  => 'success',
                    'message' => 'Project retrieved successfully',
                    'data'    => (new ProjectDetailResource($project))->resolve(), // resolve() wajib di dalam closure — lihat CLAUDE.md
                ];
            });
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ['status' => 'error', 'message' => 'Project not found'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Failed to retrieve project', 'error' => $e->getMessage()];
        }
    }

    public function getComments(int $id, int $perPage = 15, int $page = 1): array
    {
        try {
            // Tidak di-cache karena komentar sering berubah
            $project = Project::findOrFail($id);

            $comments = $project->comments()
                ->with('user')
                ->latest('date')
                ->paginate($perPage, ['*'], 'page', $page);

            return [
                'status'  => 'success',
                'message' => 'Comments retrieved successfully',
                'data'    => CommentResource::collection($comments->getCollection())->resolve(),
                'meta'    => [
                    'current_page'   => $comments->currentPage(),
                    'per_page'       => $comments->perPage(),
                    'total'          => $comments->total(),
                    'last_page'      => $comments->lastPage(),
                    'has_more_pages' => $comments->hasMorePages(),
                ],
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ['status' => 'error', 'message' => 'Project not found'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Failed to retrieve comments', 'error' => $e->getMessage()];
        }
    }

    public function getPosts(int $id, int $perPage = 15, int $page = 1): array
    {
        try {
            $project = Project::findOrFail($id);

            $posts = $project->posts()
                ->latest('date')
                ->paginate($perPage, ['*'], 'page', $page);

            return [
                'status'  => 'success',
                'message' => 'Posts retrieved successfully',
                'data'    => PostResource::collection($posts->getCollection())->resolve(),
                'meta'    => [
                    'current_page'   => $posts->currentPage(),
                    'per_page'       => $posts->perPage(),
                    'total'          => $posts->total(),
                    'last_page'      => $posts->lastPage(),
                    'has_more_pages' => $posts->hasMorePages(),
                ],
            ];
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return ['status' => 'error', 'message' => 'Project not found'];
        } catch (Throwable $e) {
            return ['status' => 'error', 'message' => 'Failed to retrieve posts', 'error' => $e->getMessage()];
        }
    }
}
