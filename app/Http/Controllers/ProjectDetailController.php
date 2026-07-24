<?php

namespace App\Http\Controllers;

use App\Services\ProjectDetailService;

class ProjectDetailController extends Controller
{
    public function __construct(protected ProjectDetailService $service) {}

    public function show(int $id)
    {
        $result = $this->service->getDetail($id);
        return response()->json($result, $result['status'] === 'success' ? 200 : ($result['message'] === 'Project not found' ? 404 : 500));
    }

    public function comments(int $id)
    {
        $page = (int) request()->query('page', 1);
        $result = $this->service->getComments($id, 15, $page);
        return response()->json($result, $result['status'] === 'success' ? 200 : ($result['message'] === 'Project not found' ? 404 : 500));
    }

    public function posts(int $id)
    {
        $page = (int) request()->query('page', 1);
        $result = $this->service->getPosts($id, 15, $page);
        return response()->json($result, $result['status'] === 'success' ? 200 : ($result['message'] === 'Project not found' ? 404 : 500));
    }
}
