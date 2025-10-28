<?php

namespace App\Http\Controllers;

use App\Models\ChallengeCategory;
use Illuminate\Http\Request;
use App\Models\Challenge;
use App\Services\ChallengeService;

class ChallengeListController extends Controller
{
    protected $service;

    public function __construct(ChallengeService $service)
    {
        $this->service = $service;
    }

    public function getChallenges(Request $request)
    {
        $parentId = $request->query('parent_id');
        $userId = $request->query('user_id');

        if ($parentId === null) {
            $challenges = ChallengeCategory::all();
        } else {
            $query = Challenge::with('category', 'answers')->where('challenge_id', $parentId);
            $challenges = $query->get();
        }


        if ($userId) {
            $challenges->transform(function ($challenge) use ($userId) {
                $completion = $challenge->completions()->where('user_id', $userId)->first();
                $challenge->user_completed = $completion ? true : false;
                $challenge->user_score = $completion->score ?? 0;
                $challenge->user_time_taken = $completion->time_taken ?? 0;
                return $challenge;
            });

            // Sort incomplete first
            $challenges = $challenges->sortBy('user_completed');
        }

        return response()->json(['status' => 'success', 'data' => $challenges]);
    }

    public function getChallengeById(Request $request, $id)
    {
        $userId = $request->query('user_id');
        $challenge = Challenge::with('category', 'answers')->find($id);

        if (!$challenge) {
            return response()->json(['status' => 'error', 'message' => 'Challenge not found'], 404);
        }

        if ($userId) {
            $completion = $challenge->completions()->where('user_id', $userId)->first();
            $userData = $completion ? ['completed' => true, 'score' => $completion->score] : ['completed' => false];
        } else {
            $userData = ['completed' => false];
        }

        return response()->json([
            'status' => 'success',
            'data' => $challenge,
            'userScore' => $userData
        ]);
    }

    public function addChallenge(Request $request)
    {
        $data = $request->validate([
            'parent_id' => 'required|exists:challenge_categories,id',
            'title' => 'required|string|max:255',
            'challenge_question' => 'required|string',
            'answers' => 'required|array|min:1',
            'time' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);

        $challenge = $this->service->createChallenge($data);

        return response()->json(['status' => 'success', 'data' => $challenge]);
    }
}
