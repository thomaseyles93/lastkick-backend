<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeCategory;
use App\Models\User;
use App\Models\UserChallengeCompletion;
use App\Models\UserQuizAnswer;
use App\Models\Quiz;
use App\Models\WhoAmI;
use Illuminate\Support\Facades\DB;

class ChallengeService
{
    public function createChallenge(array $data)
    {
        return DB::transaction(function () use ($data) {
            $category = ChallengeCategory::find($data['parent_id']);
            if (!$category) {
                throw new Exception('Category not found');
            }

            $challenge = Challenge::create([
                'title' => $data['title'],
                'challenge_question' => $data['challenge_question'],
                'challenge_id' => $data['parent_id'],
                'answers' => count($data['answers']),
                'time' => $data['time'] ?? null,
                'description' => $data['description'] ?? null,
            ]);

            $answers = collect($data['answers'])->map(function ($answer, $index) {
                return [
                    'answer' => is_array($answer) ? $answer['answer'] : $answer,
                    'answer_info' => is_array($answer) ? ($answer['info'] ?? null) : null,
                    'position' => $index + 1
                ];
            })->toArray();

            $challenge->answers()->createMany($answers);

            return $challenge->load('answers', 'category');
        });
    }

    public function completeChallenge(array $data)
    {
        $user = User::find($data['user_id']);
        if (!$user) {
            return ['status' => 'error', 'message' => 'Invalid user ID'];
        }

        $challenge = $this->findChallenge($data['challenge_id'], $data['challenge_type']);
        if (!$challenge) {
            return ['status' => 'error', 'message' => 'Invalid challenge ID'];
        }

        try {
            $completion = UserChallengeCompletion::create([
                'user_id' => $data['user_id'],
                'challenge_id' => $data['challenge_id'],
                'challenge_type' => $data['challenge_type'],
                'score' => $data['score'],
                'time_taken' => $data['time_taken'],
            ]);

            return [
                'status' => 'success',
                'message' => 'Challenge.php completed successfully',
                'data' => $completion->only(['score', 'time_taken'])
            ];
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return ['status' => 'error', 'message' => 'Challenge.php already completed'];
            }

            return ['status' => 'error', 'message' => 'Database error'];
        }
    }

    public function getUserScore($userId, $type = null)
    {
        $query = UserChallengeCompletion::where('user_id', $userId);
        if ($type) $query->where('challenge_type', $type);

        $stats = $query->selectRaw('
            COUNT(*) as challenges_completed,
            COALESCE(SUM(score), 0) as total_score,
            COALESCE(SUM(time_taken), 0) as total_time_taken
        ')->first();

        return [
            'status' => 'success',
            'data' => [
                'total_score' => (int)$stats->total_score,
                'challenges_completed' => (int)$stats->challenges_completed,
                'total_time_taken' => (int)$stats->total_time_taken,
                'challenge_type' => $type ?? 'all'
            ]
        ];
    }

    public function getChallengeResults($userId, $challengeId, $type)
    {
        $completion = UserChallengeCompletion::where(compact('userId', 'challengeId', 'type'))
            ->first();

        if (!$completion) {
            return ['status' => 'success', 'data' => ['completed' => false]];
        }

        $data = [
            'completed' => true,
            'completion' => [
                'score' => $completion->score,
                'time_taken' => $completion->time_taken,
                'completed_at' => $completion->created_at,
            ]
        ];

        // If it's a quiz, include answers
        if ($type === 'quiz') {
            $answers = UserQuizAnswer::with(['question', 'selectedAnswer'])
                ->where('user_id', $userId)
                ->where('quiz_id', $challengeId)
                ->get()
                ->map(function ($a) {
                    return [
                        'question_id' => $a->question_id,
                        'question' => $a->question->question,
                        'selected_answer_id' => $a->selected_answer_id,
                        'selected_answer' => $a->selectedAnswer->answer,
                        'is_correct' => $a->is_correct,
                        'answered_at' => $a->answered_at,
                    ];
                });

            $data['answers'] = $answers;
        }

        return ['status' => 'success', 'data' => $data];
    }

    public function getRandomUnansweredChallenge($userId, $type)
    {
        $table = $type === 'quiz' ? Quiz::class : WhoAmI::class;

        $challenge = $table::whereNotIn('id', function ($query) use ($userId, $type) {
            $query->select('challenge_id')
                ->from('user_challenges_completions')
                ->where('user_id', $userId)
                ->where('challenge_type', $type);
        })->inRandomOrder()->first();

        if (!$challenge) {
            return ['status' => 'success', 'data' => null, 'message' => 'No unanswered challenges available'];
        }

        if ($type === 'quiz') {
            $challenge->load('questions.answers');
        }

        return ['status' => 'success', 'data' => $challenge];
    }

    public function searchChallengeByName($term)
    {
        $term = trim($term);
        if ($term === '') {
            return ['status' => 'error', 'message' => 'Search term is required'];
        }

        $categories = DB::table('challenge_categories')
            ->select('id', 'title AS name', DB::raw("'category' AS type"))
            ->where('title', 'LIKE', "%{$term}%")
            ->limit(20)
            ->get();

        $challenges = DB::table('challenge')
            ->select('id', 'challenge_question AS name', DB::raw("'challenge' AS type"))
            ->where('challenge_question', 'LIKE', "%{$term}%")
            ->limit(20)
            ->get();

        return ['status' => 'success', 'data' => $categories->merge($challenges)];
    }

    private function findChallenge($id, $type)
    {
        if ($type === 'quiz') return Quiz::find($id);
        if ($type === 'who_am_i') return WhoAmI::find($id);
        return null;
    }
}
