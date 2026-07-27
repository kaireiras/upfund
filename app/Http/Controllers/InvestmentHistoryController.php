<?php
namespace App\Http\Controllers;

use App\Http\Resources\InvestmentHistoryResource;
use App\Models\InvestmentHistory;
use Illuminate\Http\Request;

class InvestmentHistoryController extends Controller
{
    /**
     * GET /api/user/investment-histories
     * Menampilkan daftar investasi milik user yang sedang login
     */
    public function index(Request $request)
    {
        $histories = InvestmentHistory::with(['project', 'transaction'])
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(10);

        return InvestmentHistoryResource::collection($histories);
    }

    /**
     * GET /api/projects/{projectId}/investors
     * Menampilkan daftar investor pada proyek tertentu
     */
    public function projectInvestors(int $projectId)
    {
        $investors = InvestmentHistory::with(['user', 'transaction'])
            ->where('project_id', $projectId)
            ->latest('id')
            ->paginate(15);

        return InvestmentHistoryResource::collection($investors);
    }
}