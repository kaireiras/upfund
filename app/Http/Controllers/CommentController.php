<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Http\Resources\CommentResource;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Mengambil daftar komentar berdasarkan target (post/project)
     * GET /api/comments?target_id=1&target_type=post
     */
    public function index(Request $request)
    {
        $request->validate([
            'target_id'   => 'required|integer',
            'target_type' => 'required|in:post,project',
        ]);

        $comments = Comment::with('user')
            ->where('target_id', $request->target_id)
            ->where('target_type', $request->target_type)
            ->orderBy('date', 'desc')
            ->paginate(10); // Otomatis mendukung pagination untuk frontend

        return CommentResource::collection($comments);
    }

    /**
     * Membuat Komentar Baru (Dinamis untuk tipe target apapun)
     * POST /api/comments
     */
    public function store(Request $request)
    {
        // Validasi data yang masuk
        $request->validate([
            'target_id'   => 'required|integer',
            'target_type' => 'required|in:post,project',
            'comment'     => 'required|string|max:500',
            'project_url' => 'nullable|string|max:50'
        ]);

        // Proses create data
        $comment = Comment::create([
            'target_id'   => $request->target_id,
            'target_type' => $request->target_type,
            'comment'     => $request->comment,
            'user_id'     => $request->user()->id, 
            'project_url' => $request->project_url, 
            'date'        => now()
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment created successfully',
            'data'    => new CommentResource($comment->load('user')) // Menggunakan CommentResource
        ], 201);
    }

    /**
     * Menghapus Komentar
     * DELETE /api/comments/{id}
     */
    public function destroy(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        // Proteksi: Pastikan hanya pemilik komentar yang bisa menghapus
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized. You can only delete your own comment.'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment deleted successfully'
        ], 200);
    }
}