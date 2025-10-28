<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChallengeSubmissionService;

class ChallengeSubmissionController extends Controller
{
    protected $service;

    public function __construct(ChallengeSubmissionService $service)
    {
        $this->service = $service;
    }

    public function submitAnswer(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'challenge_id' => 'required|integer',
            'term' => 'required|string',
        ]);

        $result = $this->service->submitAnswer($data['user_id'], $data['challenge_id'], $data['term']);

        return response()->json($result);
    }

    public function getUserAnswers(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'challenge_id' => 'required|integer',
        ]);

        $result = $this->service->getUserAnswers($data['user_id'], $data['challenge_id']);

        return response()->json($result);
    }

    public function finishChallenge(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'challenge_id' => 'required|integer',
        ]);

        $result = $this->service->finishChallenge($data['user_id'], $data['challenge_id']);

        return response()->json($result);
    }
}
