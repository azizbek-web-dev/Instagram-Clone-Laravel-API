<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function users(Request $request)
    {
        $q = $request->query('q', '');
        if (strlen($q) < 2) {
            return response()->json(['users' => []]);
        }

        $users = User::where('name', 'like', "%{$q}%")
            ->orWhere('username', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->limit(20)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username ?? $u->name,
                'avatar' => $u->avatar ? asset('storage/' . $u->avatar) : null,
            ]);

        return response()->json(['users' => $users]);
    }

    public function posts(Request $request)
    {
        $q = $request->query('q', '');
        if (strlen($q) < 2) {
            return response()->json(['posts' => []]);
        }

        $posts = Post::where('caption', 'like', "%{$q}%")
            ->orWhere('location', 'like', "%{$q}%")
            ->with('user')
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'image' => $p->image ? asset('storage/' . $p->image) : null,
                'caption' => $p->caption,
                'user' => $p->user ? [
                    'id' => $p->user->id,
                    'username' => $p->user->username ?? $p->user->name,
                ] : null,
            ]);

        return response()->json(['posts' => $posts]);
    }
}

