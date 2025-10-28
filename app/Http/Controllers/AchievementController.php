<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Storage;

class AchievementController extends Controller
{
    public function index()
    {
        $achievements = Achievement::orderByDesc('date_added')->get();
        return response()->json(['status' => 'success', 'data' => $achievements]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'img' => 'required|string',
        ]);

        $imgData = $request->img;
        $uploadDir = public_path('uploads/achievements/');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (preg_match('/^data:image\/(\w+);base64,/', $imgData, $type)) {
            $data = substr($imgData, strpos($imgData, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif'])) {
                return response()->json(['status' => 'error', 'message' => 'Invalid image type'], 400);
            }

            $data = base64_decode($data);

            if ($data === false) {
                return response()->json(['status' => 'error', 'message' => 'Failed to decode image'], 400);
            }

            $filename = uniqid() . '.' . $type;
            file_put_contents($uploadDir . $filename, $data);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Invalid image format'], 400);
        }

        $achievement = Achievement::create([
            'title' => $request->title,
            'img' => $filename,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Achievement added successfully',
            'data' => $achievement
        ]);
    }

    public function userAchievements($userId)
    {
        $achievements = UserAchievement::where('user_id', $userId)
            ->join('challenge_achievements', 'challenge_achievements.id', '=', 'user_challenge_achievements.achievement_id')
            ->orderByDesc('user_challenge_achievements.date_achieved')
            ->get(['challenge_achievements.*', 'user_challenge_achievements.date_achieved']);

        $achievements->each(function ($a) {
            $a->img_url = url('/uploads/achievements/' . $a->img);
        });

        return response()->json(['status' => 'success', 'data' => $achievements]);
    }

    public function addUserAchievement(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'achievement_id' => 'required|integer',
        ]);

        $user = User::find($request->user_id);
        $achievement = Achievement::find($request->achievement_id);

        if (!$user || !$achievement) {
            return response()->json(['status' => 'error', 'message' => 'User or Achievement not found'], 404);
        }

        $exists = UserAchievement::where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->exists();

        if ($exists) {
            return response()->json(['status' => 'success', 'message' => 'Achievement already unlocked']);
        }

        UserAchievement::create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'date_achieved' => now(),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Achievement unlocked successfully']);
    }
}
