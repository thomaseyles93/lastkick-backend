<?php

namespace App\Http\Controllers;

use App\Models\ChallengeCategory;
use Illuminate\Http\Request;
use App\Models\Challenge;
use App\Services\ChallengeService;
use Illuminate\Support\Facades\Log;

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
        $userId   = $request->query('user_id');

        if ($parentId === null) {
            // Return categories
            $challenges = ChallengeCategory::all();
        } else {
            // Use get(), not all(), and eager-load relations
            $challenges = Challenge::with(['category', 'completions'])
                ->where('challenge_id', $parentId)
                ->get();

            // Add category_title and answer_count, hide relations
            $challenges = $challenges->map(function ($challenge) {
                if (!$challenge) return null;

                $challenge->category_title = optional($challenge->category)->title;
                $challenge->answer_count   = $challenge->answers ?? 0;
                $challenge->makeHidden(['category', 'completions']);

                return $challenge;
            });
        }

        if ($userId && $challenges->count()) {
            $challenges = $challenges->map(function ($challenge) use ($userId) {
                if (!$challenge) return null;

                // completions is a collection (because we eager-loaded it)
                $completion = $challenge->completions
                    ? $challenge->completions->where('user_id', $userId)->first()
                    : null;

                $challenge->user_completed  = (bool) $completion;
                $challenge->user_score      = $completion->score ?? 0;
                $challenge->user_time_taken = $completion->time_taken ?? 0;

                $challenge->makeHidden(['category', 'completions']);

                return $challenge;
            });

            $challenges = $challenges->sortBy('user_completed')->values();
        }

        return response()->json([
            'status' => 'success',
            'data'   => $challenges,
        ]);
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
