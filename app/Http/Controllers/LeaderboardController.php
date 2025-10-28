<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /**
     * Get leaderboard
     */
    public function index(Request $request)
    {
        $challengeType = $request->query('challenge_type');
        $limit = min($request->query('limit', 100), 100);

        $query = User::select(
            'users.id',
            'users.first_name',
            'users.last_name',
            DB::raw('COUNT(user_challenges_completions.id) as challenges_completed'),
            DB::raw('COALESCE(SUM(user_challenges_completions.score), 0) as total_score'),
            DB::raw('COALESCE(SUM(user_challenges_completions.time_taken), 0) as total_time')
        )
            ->leftJoin('user_challenges_completions', function ($join) use ($challengeType) {
                $join->on('users.id', '=', 'user_challenges_completions.user_id');
                if ($challengeType) {
                    $join->where('user_challenges_completions.challenge_type', $challengeType);
                }
            })
            ->groupBy('users.id')
            ->having('total_score', '>', 0)
            ->orderByDesc('total_score')
            ->orderBy('total_time')
            ->limit($limit)
            ->get();

        // Add position/rank
        $leaderboard = $query->map(function ($user, $index) {
            return [
                'position' => $index + 1,
                'user_id' => (int)$user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'challenges_completed' => (int)$user->challenges_completed,
                'total_score' => (int)$user->total_score,
                'total_time' => (int)$user->total_time,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'challenge_type' => $challengeType ?? 'all',
                'leaderboard' => $leaderboard
            ]
        ]);
    }
}
