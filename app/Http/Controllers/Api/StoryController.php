<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $stories = Story::where('expires_at', '>', now())
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');

        $items = [];
        foreach ($stories as $userId => $userStories) {
            $storyUser = $userStories->first()->user;
            $viewed = StoryView::where('user_id', $user->id)
                ->whereIn('story_id', $userStories->pluck('id'))
                ->exists();

            $items[] = [
                'user' => [
                    'id' => $storyUser->id,
                    'username' => $storyUser->username ?? $storyUser->name,
                    'avatar' => $storyUser->avatar ? asset('storage/' . $storyUser->avatar) : null,
                ],
                'stories' => $userStories->map(fn ($s) => [
                    'id' => $s->id,
                    'image' => asset('storage/' . $s->image),
                    'created_at' => $s->created_at->toIso8601String(),
                ])->values(),
                'viewed' => $viewed,
            ];
        }

        return response()->json(['stories' => $items]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $path = $request->file('image')->store('stories', 'public');
        $story = $request->user()->stories()->create([
            'image' => $path,
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json([
            'message' => 'Story created',
            'story' => [
                'id' => $story->id,
                'image' => asset('storage/' . $path),
                'expires_at' => $story->expires_at->toIso8601String(),
            ],
        ], 201);
    }

    public function show(Request $request, string $username)
    {
        if ($username === 'me') {
            $storyUser = $request->user();
        } else {
            $storyUser = \App\Models\User::where('username', $username)->first();
        }

        if (!$storyUser) {
        if (!$storyUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $stories = Story::where('user_id', $storyUser->id)
            ->where('expires_at', '>', now())
            ->orderBy('created_at')
            ->get();

        if ($stories->isEmpty()) {
            return response()->json(['message' => 'No stories'], 404);
        }

        $viewer = $request->user();
        foreach ($stories as $story) {
            StoryView::firstOrCreate(
                ['user_id' => $viewer->id, 'story_id' => $story->id]
            );
        }

        $items = $stories->map(fn ($s) => [
            'id' => $s->id,
            'image' => asset('storage/' . $s->image),
            'created_at' => $s->created_at->toIso8601String(),
        ]);

        return response()->json([
            'user' => [
                'id' => $storyUser->id,
                'username' => $storyUser->username ?? $storyUser->name,
                'avatar' => $storyUser->avatar ? asset('storage/' . $storyUser->avatar) : null,
            ],
            'stories' => $items,
        ]);
    }

    public function destroy(Request $request, Story $story)
    {
        if ($story->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($story->image) {
            Storage::disk('public')->delete($story->image);
        }
        $story->delete();

        return response()->json(['message' => 'Story deleted']);
    }
}

