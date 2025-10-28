<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChallengeService;
use Illuminate\Validation\ValidationException;

class ChallengeController extends Controller
{
    protected $service;

    public function __construct(ChallengeService $service)
    {
        $this->service = $service;
    }

    // Complete a challenge
    public function complete(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'challenge_id' => 'required|integer',
            'challenge_type' => 'required|string|in:quiz,who_am_i',
            'score' => 'required|numeric|min:0',
            'time_taken' => 'required|integer|min:0',
        ]);

        return response()->json($this->service->completeChallenge($validated));
    }

    //Get user score
    public function score(Request $request, $userId)
    {
        $challengeType = $request->query('type');
        return response()->json($this->service->getUserScore($userId, $challengeType));
    }

    //Get challenge results for a user
    public function results($userId, $challengeId, $type)
    {
        return response()->json($this->service->getChallengeResults($userId, $challengeId, $type));
    }

    //Get a random unanswered challenge
    public function randomUnanswered($userId, $type)
    {
        return response()->json($this->service->getRandomUnansweredChallenge($userId, $type));
    }

    //Search by name
    public function search(Request $request)
    {
        $term = $request->query('term');
        return response()->json($this->service->searchChallengeByName($term));
    }
}
