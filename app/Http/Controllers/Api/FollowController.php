<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function follow(Request $request, User $user)
    {
        $follower = $request->user();
        if ($follower->id === $user->id) {
            return response()->json(['message' => 'Cannot follow yourself'], 403);
        }

        Follow::firstOrCreate(['follower_id' => $follower->id, 'following_id' => $user->id]);

        return response()->json([
            'message' => 'Following',
            'followers_count' => $user->followers()->count(),
        ]);
    }

    public function unfollow(Request $request, User $user)
    {
        Follow::where('follower_id', $request->user()->id)
            ->where('following_id', $user->id)
            ->delete();

        return response()->json([
            'message' => 'Unfollowed',
            'followers_count' => $user->followers()->count(),
        ]);
    }

    public function followers(Request $request, User $user)
    {
        $followers = $user->followers()->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'username' => $u->username ?? $u->name,
            'avatar' => $u->avatar ? asset('storage/' . $u->avatar) : null,
        ]);

        return response()->json(['followers' => $followers]);
    }

    public function following(Request $request, User $user)
    {
        $following = $user->following()->get()->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'username' => $u->username ?? $u->name,
            'avatar' => $u->avatar ? asset('storage/' . $u->avatar) : null,
        ]);

        return response()->json(['following' => $following]);
    }
}

