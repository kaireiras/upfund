<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Interaction;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class PostController extends Controller
{
    /**
     * Mengambil Semua Daftar Post (Feed)
     * GET /api/posts
     */
    public function index(Request $request)
    {
        $posts = Post::with(['categories', 'comments.user'])
            ->withCount([
                'interactions as likes_count' => function ($query) {
                    $query->where('like', true);
                },
                'comments as comments_count'
            ])
            ->orderBy('date', 'desc') // Postingan terbaru di atas
            ->paginate(10); // Menampilkan 10 post per halaman

        // Menghitung shares_count untuk setiap post dalam koleksi
        $posts->getCollection()->transform(function ($post) {
            $sharesCount = Interaction::where('target_type', 'post')
                ->where('target_id', $post->id)
                ->sum('share');

            $post->shares_count = $sharesCount;
            return $post;
        });

        return PostResource::collection($posts);
    }
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'description'       => 'required|string',
            'image_url'         => 'nullable|url|max:1024',
            'project_url'       => [
                'nullable', 'string', 'max:50',
                Rule::exists('projects', 'id')->where('user_id', $user->id)
            ],
            
            'category_titles'   => 'required|array|min:1',
            'category_titles.*' => 'string|in:software,iot,green tech,robotics,hardware,agriculture',
        ]);

        $post = Post::create([
            'description' => $request->description,
            'image_url'   => $request->image_url,
            'project_url' => $request->project_url,
            'user_id'     => $user->id,
            'date'        => now()
        ]);

        $categoriesData = collect($request->category_titles)->map(function ($title) {
            return ['title' => $title];
        })->toArray();

        // 4. Simpan massal ke tabel categories memanfaatkan relasi HasMany
        // Laravel otomatis akan mengisikan 'post_id' sesuai dengan post yang baru dibuat
        $post->categories()->createMany($categoriesData);

        return response()->json([
            'status' => 'success',
            'message' => 'Update published successfully!',
            'data' => new PostResource($post->load('categories'))
        ], 201);
    }
    /**
     * Mengambil Detail Post beserta Relasinya (Komentar & Like)
     */
    public function show($id)
    {
        $post = Post::with(['comments.user'])
            ->withCount([
                'interactions as likes_count' => function ($query) {
                    $query->where('like', true);
                },
                'comments as comments_count'
            ])
            ->findOrFail($id);

        $sharesCount = Interaction::where('target_type', 'post')
            ->where('target_id', $id)
            ->sum('share');

        $post->shares_count = $sharesCount;

        return new PostResource($post);
    }

    /**
     * Toggle Like / Unlike Post atau Project
     */
    public function toggleLike(Request $request)
    {
        $request->validate([
            'target_id'   => 'required|integer',
            'target_type' => 'required|in:post,project'
        ]);

        $userId = $request->user()->id;

        $interaction = Interaction::where('target_type', $request->target_type)
            ->where('target_id', $request->target_id)
            ->where('user_id', $userId)
            ->first();

        if ($interaction) {
            $interaction->update(['like' => !$interaction->like]);
        } else {
            $interaction = Interaction::create([
                'target_id'   => $request->target_id,
                'target_type' => $request->target_type,
                'user_id'     => $userId,
                'like'        => true,
                'date'        => now()
            ]);
        }

        return response()->json([
            'message'  => $interaction->like ? 'Liked' : 'Unliked',
            'is_liked' => $interaction->like
        ]);
    }
}