<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function store(StoreProjectRequest $request): JsonResponse
    {
        // 1. Ambil data tervalidasi & masukkan user_id pembuat (dari token auth)
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['collected_funds'] = 0; // Set default awal 0

        // 2. Buat Project baru
        $project = Project::create($data);

        // 3. Attach relasi kategori jika ada
        if ($request->has('category_ids')) {
            $project->categories()->sync($request->category_ids);
        }

        // 4. Return JsonResponse menggunakan ProjectResource
        return response()->json([
            'status'  => 'success',
            'message' => 'Project created successfully',
            'data'    => new ProjectResource($project->load(['user', 'categories']))
        ], 201);
    }
}

