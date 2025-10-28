<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DailyChallenge;
use App\Models\DailyChallengeAnswer;
use App\Models\UserDailyChallengeAnswer;
use App\Models\DailyStreak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DailyChallengeSubmissionController extends Controller
{
    public function submitAnswer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'term' => 'required|string|max:255',
        ]);

        $userId = $request->user_id;
        $term = $request->term;
        $today = Carbon::today()->toDateString();

        $challenge = DailyChallenge::with('answers')
            ->whereDate('daily_date', $today)
            ->first();

        if (!$challenge) {
            return response()->json([
                'status' => 'error',
                'message' => 'No daily challenge found for today'
            ], 404);
        }

        $userAnswers = UserDailyChallengeAnswer::where('user_id', $userId)
            ->where('daily_challenge_id', $challenge->id)
            ->get();

        $livesRemaining = $userAnswers->first()->lives_remaining ?? 3;
        $answeredCount = $userAnswers->count();

        if ($livesRemaining <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'No lives remaining for today',
                'data' => ['lives_remaining' => 0, 'game_over' => true]
            ]);
        }

        // Check if the submitted term matches any answer
        $matchedAnswer = $challenge->answers->first(function ($a) use ($term) {
            return strcasecmp($a->answer_title, $term) === 0;
        });

        DB::beginTransaction();

        try {
            if ($matchedAnswer) {
                $alreadyAnswered = $userAnswers->where('answer_id', $matchedAnswer->id)->count() > 0;

                if (!$alreadyAnswered) {
                    UserDailyChallengeAnswer::create([
                        'user_id' => $userId,
                        'daily_challenge_id' => $challenge->id,
                        'answer_id' => $matchedAnswer->id,
                        'lives_remaining' => $livesRemaining
                    ]);
                    $answeredCount++;
                }

                $challengeComplete = ($answeredCount == $challenge->answers->count());

                if ($challengeComplete) {
                    $this->updateDailyStreak($userId);
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => $alreadyAnswered ? 'Already answered this one!' : 'Correct answer!',
                    'data' => [
                        'is_correct' => true,
                        'already_answered' => $alreadyAnswered,
                        'answer_title' => $matchedAnswer->answer_title,
                        'answer_info' => $matchedAnswer->answer_info,
                        'challenge_complete' => $challengeComplete,
                        'lives_remaining' => $livesRemaining,
                    ]
                ]);
            } else {
                // Wrong answer, lose a life
                $livesRemaining--;

                if ($answeredCount > 0) {
                    $userAnswer = $userAnswers->first();
                    $userAnswer->update(['lives_remaining' => $livesRemaining]);
                } else {
                    UserDailyChallengeAnswer::create([
                        'user_id' => $userId,
                        'daily_challenge_id' => $challenge->id,
                        'answer_id' => null,
                        'lives_remaining' => $livesRemaining
                    ]);
                }

                $gameOver = $livesRemaining <= 0;

                if ($gameOver) {
                    $this->updateDailyStreak($userId);
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Incorrect answer',
                    'data' => [
                        'is_correct' => false,
                        'challenge_complete' => false,
                        'lives_remaining' => $livesRemaining,
                        'game_over' => $gameOver
                    ]
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private function updateDailyStreak($userId)
    {
        $today = Carbon::today()->toDateString();
        $lastStreak = DailyStreak::where('user_id', $userId)->latest('date')->first();

        $newCount = 1;
        if ($lastStreak && Carbon::parse($lastStreak->date)->addDay()->isToday()) {
            $newCount = $lastStreak->streak_count + 1;
        }

        DailyStreak::updateOrCreate(
            ['user_id' => $userId, 'date' => $today],
            ['streak_count' => $newCount]
        );

        return $newCount;
    }

    public function currentGameState($userId)
    {
        $today = Carbon::today()->toDateString();

        $challenge = DailyChallenge::with('answers')->whereDate('daily_date', $today)->first();

        if (!$challenge) {
            return response()->json([
                'status' => 'error',
                'message' => 'No daily challenge found for today'
            ], 404);
        }

        $userAnswers = UserDailyChallengeAnswer::where('user_id', $userId)
            ->where('daily_challenge_id', $challenge->id)
            ->get();

        $livesRemaining = $userAnswers->first()->lives_remaining ?? 3;
        $answeredCount = $userAnswers->count();
        $gameOver = $livesRemaining <= 0;

        $answeredQuestions = $userAnswers->load('answer')->map(function ($a) {
            return [
                'answer_title' => $a->answer->answer_title ?? null,
                'answer_info' => $a->answer->answer_info ?? null
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'challenge_title' => $challenge->title,
                'lives_remaining' => $livesRemaining,
                'game_over' => $gameOver,
                'answered_count' => $answeredCount,
                'answered_questions' => $answeredQuestions
            ]
        ]);
    }
}
