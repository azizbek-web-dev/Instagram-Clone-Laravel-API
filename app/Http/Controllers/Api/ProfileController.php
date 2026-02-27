<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'bio' => $user->bio,
                'website' => $user->website,
                'phone' => $user->phone,
                'gender' => $user->gender,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $input = $request->only(['name', 'username', 'email', 'bio', 'website', 'phone', 'gender']);

        $validated = validator($input, [
            'name' => 'sometimes|string|min:1|max:255',
            'username' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'bio' => 'nullable|string|max:500',
            'website' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:Male,Female,Other,male,female,other',
        ])->validate();

        $data = $validated;
        if (isset($data['gender']) && $data['gender']) {
            $data['gender'] = ucfirst(strtolower($data['gender']));
        }
        $data = array_filter($data, fn ($v) => $v !== null && $v !== '');

        $user->update($data);
        $updated = $user->fresh();

        return response()->json([
            'message' => 'Profile updated',
            'user' => [
                'id' => $updated->id,
                'name' => $updated->name,
                'username' => $updated->username,
                'email' => $updated->email,
                'avatar' => $updated->avatar ? asset('storage/' . $updated->avatar) : null,
                'bio' => $updated->bio,
                'website' => $updated->website,
                'phone' => $updated->phone,
                'gender' => $updated->gender,
            ],
        ]);
    }

    public function changeAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return response()->json([
            'message' => 'Avatar updated',
            'avatar' => asset('storage/' . $path),
        ]);
    }

    public function posts(Request $request)
    {
        $posts = $request->user()->posts()->latest()->get();
        $items = $posts->map(fn ($p) => [
            'id' => $p->id,
            'image' => $p->image ? asset('storage/' . $p->image) : null,
            'caption' => $p->caption,
            'location' => $p->location,
            'created_at' => $p->created_at->toIso8601String(),
        ]);
        return response()->json(['posts' => $items]);
    }
}
