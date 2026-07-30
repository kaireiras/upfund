<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectDetailResource;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __construct(
        private ProjectService $projectService,
    ) {}

    /**
     * Buat project baru.
     * POST /api/projects  (auth:sanctum)
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create(
            $request->validated(),
            $request->user()->id,
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Project created successfully',
            'data'    => new ProjectDetailResource($project),
        ], 201);
    }
}
