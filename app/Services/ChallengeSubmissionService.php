<?php
namespace App\Services;

use App\Models\ChallengeAnswer;
use App\Models\UserChallengeAnswer;
use App\Models\UserChallengeCompletion;
use Illuminate\Support\Facades\DB;
use Exception;

class ChallengeSubmissionService
{
    public function submitAnswer(int $userId, int $challengeId, string $term)
    {
        return DB::transaction(function () use ($userId, $challengeId, $term) {
            $answers = ChallengeAnswer::where('challenge_id', $challengeId)
                ->orderBy('position')
                ->get();

            if ($answers->isEmpty()) {
                throw new Exception('No answers found for this challenge');
            }

            $totalAnswers = $answers->count();
            $returnAnswers = [];
            $challengeComplete = false;

            foreach ($answers as $answer) {
                $helperService = app(HelperService::class);

                if ($helperService->isAnswerMatch($term, $answer->answer)) {
                    $alreadyAnswered = UserChallengeAnswer::where([
                        'user_id' => $userId,
                        'challenge_id' => $challengeId,
                        'position' => $answer->position
                    ])->exists();

                    if (!$alreadyAnswered) {
                        UserChallengeAnswer::create([
                            'user_id' => $userId,
                            'challenge_id' => $challengeId,
                            'position' => $answer->position
                        ]);
                    }

                    $answeredCount = UserChallengeAnswer::where([
                        'user_id' => $userId,
                        'challenge_id' => $challengeId
                    ])->count();

                    $challengeComplete = ($answeredCount === $totalAnswers);

                    $returnAnswers[] = [
                        'is_correct' => true,
                        'answer' => $answer->answer,
                        'answer_info' => $answer->answer_info,
                        'position' => $answer->position,
                        'already_answered' => $alreadyAnswered,
                    ];
                }
            }

            return [
                'status' => 'success',
                'message' => 'Answers processed',
                'data' => [
                    'challenge_complete' => $challengeComplete,
                    'answers' => $returnAnswers,
                ]
            ];
        });
    }

    public function getUserAnswers(int $userId, int $challengeId)
    {
        $answers = UserChallengeAnswer::where('user_id', $userId)
            ->where('challenge_id', $challengeId)
            ->with(['challengeAnswer'])
            ->orderBy('position')
            ->get();

        return [
            'status' => 'success',
            'data' => [
                'answered_positions' => $answers->pluck('position')->map(fn($v) => (int)$v),
                'answered_values' => $answers->map(fn($item) => [
                    'answer' => $item->challengeAnswer->answer ?? null,
                    'info' => $item->challengeAnswer->answer_info ?? null
                ])
            ]
        ];
    }

    public function finishChallenge(int $userId, int $challengeId)
    {
        return DB::transaction(function () use ($userId, $challengeId) {
            $totalAnswers = ChallengeAnswer::where('challenge_id', $challengeId)->count();
            $correctCount = UserChallengeAnswer::where([
                'user_id' => $userId,
                'challenge_id' => $challengeId
            ])->count();

            $score = $totalAnswers > 0 ? round(($correctCount / $totalAnswers) * 100) : 0;

            UserChallengeCompletion::updateOrCreate(
                ['user_id' => $userId, 'challenge_id' => $challengeId],
                ['score' => $score, 'completed_at' => now(), 'challenge_type' => 'challenge', 'time_taken' => 0]
            );

            return [
                'status' => 'success',
                'data' => [
                    'score' => $score,
                    'total_answers' => $totalAnswers,
                    'correct_answers' => $correctCount
                ]
            ];
        });
    }
}
