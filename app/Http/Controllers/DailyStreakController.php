<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DailyStreak;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DailyStreakController extends Controller
{
    /**
     * Get current streak for a user
     */
    public function current(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId = $request->user_id;

        $lastStreak = DailyStreak::where('user_id', $userId)
            ->latest('date')
            ->first();

        if (!$lastStreak) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'current_streak' => 0,
                    'last_streak_date' => null,
                    'streak_broken' => false,
                ]
            ]);
        }

        $lastDate = Carbon::parse($lastStreak->date);
        $today = Carbon::today();
        $diff = $today->diffInDays($lastDate);

        $streakBroken = $diff > 1;

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_streak' => $streakBroken ? 0 : $lastStreak->streak_count,
                'last_streak_date' => $lastStreak->date->toDateString(),
                'streak_broken' => $streakBroken,
            ]
        ]);
    }

    /**
     * Submit daily streak for a user
     */
    public function submit(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId = $request->user_id;
        $today = Carbon::today()->toDateString();

        $existing = DailyStreak::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'error',
                'message' => 'Daily streak already submitted for today'
            ]);
        }

        // Get last streak to calculate new count
        $lastStreak = DailyStreak::where('user_id', $userId)
            ->latest('date')
            ->first();

        $newCount = 1;
        if ($lastStreak && Carbon::parse($lastStreak->date)->addDay()->isToday()) {
            $newCount = $lastStreak->streak_count + 1;
        }

        $streak = DailyStreak::create([
            'user_id' => $userId,
            'date' => $today,
            'streak_count' => $newCount,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Daily streak submitted successfully',
            'data' => [
                'new_streak_count' => $streak->streak_count,
                'date' => $streak->date->toDateString(),
            ]
        ]);
    }
}
