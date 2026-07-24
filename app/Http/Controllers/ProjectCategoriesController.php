<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectIndexRequest;
use App\Services\ProjectCategoriesService;

class ProjectCategoriesController extends Controller
{
    protected $projectCategoriesService;

    public function __construct(ProjectCategoriesService $projectService)
    {
        $this->projectCategoriesService = $projectService;
    }

    public function index(ProjectIndexRequest $request)
    {
        $category = $request->query('category');
        $page = (int) $request->query('page', 1);

        $result = $this->projectCategoriesService->getProjectsByCategory($category, 9, $page);

        $statusCode = $result['status'] === 'success' ? 200 : 500;

        return response()->json($result, $statusCode);
    }
}
