<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Team;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\UserChallengeCompletion;

class UserController extends Controller
{
    // Check username or email availability
    public function checkAvailability(Request $request)
    {
        $username = $request->query('username');
        $email = $request->query('email');

        $response = [
            'username_exists' => $username ? User::where('username', $username)->exists() : false,
            'email_exists' => $email ? User::where('email', $email)->exists() : false,
        ];

        return response()->json(['status' => 'success', 'data' => $response]);
    }

    // Update user details
    public function update(Request $request, $userId)
    {
        $user = User::find($userId);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'username' => ['sometimes', 'string', Rule::unique('users')->ignore($userId)],
            'first_name' => 'sometimes|string',
            'last_name' => 'sometimes|string',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($userId)],
            'telephone' => 'sometimes|string|nullable',
            'support_team_id' => 'sometimes|integer|exists:teams,id|nullable',
            'country' => ['sometimes', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'photo' => 'sometimes|string|nullable',
            'password' => 'sometimes|string|min:6|nullable',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully',
            'data' => $user->only([
                'id', 'username', 'first_name', 'last_name', 'photo',
                'email', 'telephone', 'support_team_id', 'country'
            ])
        ]);
    }

    // Get user profile (details + stats + achievements)
    public function profile($userId)
    {
        $user = User::with(['achievements', 'completions'])->find($userId);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $stats = [
            'total_score' => (int) $user->completions->sum('score'),
            'challenges_completed' => (int) $user->completions->count(),
            'total_time_taken' => (int) $user->completions->sum('time_taken'),
        ];

        $achievements = $user->achievements->map(function ($achievement) {
            return [
                'id' => $achievement->id,
                'title' => $achievement->title,
                'img' => $achievement->img,
                'img_url' => url('/uploads/achievements/' . $achievement->img),
                'date_achieved' => $achievement->pivot->date_achieved ?? null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user->only([
                    'id', 'username', 'first_name', 'last_name',
                    'photo', 'email', 'telephone', 'support_team_id', 'country'
                ]),
                'stats' => $stats,
                'achievements' => $achievements,
            ],
        ]);
    }

    // Optional — list users
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get([
            'id', 'username', 'first_name', 'last_name', 'email', 'country', 'photo'
        ]);

        return response()->json(['status' => 'success', 'data' => $users]);
    }
}
