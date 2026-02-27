<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\SavedPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function feed(Request $request)
    {
        $user = $request->user();
        $posts = Post::with(['user', 'likes', 'comments'])
            ->withCount(['likes', 'comments'])
            ->latest()
            ->paginate(20);

        $postIds = $posts->pluck('id');
        $likedIds = Like::where('user_id', $user->id)->whereIn('post_id', $postIds)->pluck('post_id')->toArray();
        $savedIds = SavedPost::where('user_id', $user->id)->whereIn('post_id', $postIds)->pluck('post_id')->toArray();

        $items = $posts->map(function ($post) use ($likedIds, $savedIds) {
            return $this->formatPost($post, in_array($post->id, $likedIds), in_array($post->id, $savedIds));
        });

        return response()->json(['posts' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'caption' => 'nullable|string|max:2200',
            'location' => 'nullable|string|max:255',
        ]);

        $path = $request->file('image')->store('posts', 'public');
        $post = $request->user()->posts()->create([
            'image' => $path,
            'caption' => $validated['caption'] ?? null,
            'location' => $validated['location'] ?? null,
        ]);

        return response()->json([
            'message' => 'Post created',
            'post' => $this->formatPost($post->load('user')->loadCount(['likes', 'comments']), false, false),
        ], 201);
    }

    public function show(Request $request, Post $post)
    {
        $user = $request->user();
        $liked = Like::where('user_id', $user->id)->where('post_id', $post->id)->exists();
        $saved = SavedPost::where('user_id', $user->id)->where('post_id', $post->id)->exists();

        $post->load(['user', 'comments.user'])->loadCount(['likes', 'comments']);

        return response()->json([
            'post' => $this->formatPost($post, $liked, $saved),
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }

    public function like(Request $request, Post $post)
    {
        $user = $request->user();
        $like = Like::firstOrCreate(['user_id' => $user->id, 'post_id' => $post->id]);
        $count = $post->likes()->count();

        return response()->json([
            'message' => 'Post liked',
            'likes_count' => $count,
        ]);
    }

    public function unlike(Request $request, Post $post)
    {
        Like::where('user_id', $request->user()->id)->where('post_id', $post->id)->delete();
        $count = $post->likes()->count();

        return response()->json([
            'message' => 'Post unliked',
            'likes_count' => $count,
        ]);
    }

    public function save(Request $request, Post $post)
    {
        SavedPost::firstOrCreate(['user_id' => $request->user()->id, 'post_id' => $post->id]);
        return response()->json(['message' => 'Post saved']);
    }

    public function unsave(Request $request, Post $post)
    {
        SavedPost::where('user_id', $request->user()->id)->where('post_id', $post->id)->delete();
        return response()->json(['message' => 'Post unsaved']);
    }

    public function storeComment(Request $request, Post $post)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $comment->load('user');

        return response()->json([
            'message' => 'Comment added',
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'username' => $comment->user->username,
                    'avatar' => $comment->user->avatar ? asset('storage/' . $comment->user->avatar) : null,
                ],
                'created_at' => $comment->created_at->toIso8601String(),
            ],
        ], 201);
    }

    public function getComments(Request $request, Post $post)
    {
        $comments = $post->comments()->with('user')->latest()->get();

        $items = $comments->map(function ($c) {
            return [
                'id' => $c->id,
                'body' => $c->body,
                'user' => [
                    'id' => $c->user->id,
                    'name' => $c->user->name,
                    'username' => $c->user->username,
                    'avatar' => $c->user->avatar ? asset('storage/' . $c->user->avatar) : null,
                ],
                'created_at' => $c->created_at->toIso8601String(),
            ];
        });

        return response()->json(['comments' => $items]);
    }

    private function formatPost($post, $liked = false, $saved = false)
    {
        return [
            'id' => $post->id,
            'image' => $post->image ? asset('storage/' . $post->image) : null,
            'caption' => $post->caption,
            'location' => $post->location,
            'likes_count' => $post->likes_count ?? $post->likes()->count(),
            'comments_count' => $post->comments_count ?? $post->comments()->count(),
            'is_liked' => $liked,
            'is_saved' => $saved,
            'created_at' => $post->created_at->toIso8601String(),
            'user' => $post->user ? [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'username' => $post->user->username,
                'avatar' => $post->user->avatar ? asset('storage/' . $post->user->avatar) : null,
            ] : null,
        ];
    }
}

