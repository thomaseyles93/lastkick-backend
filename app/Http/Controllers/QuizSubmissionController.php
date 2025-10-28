<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\UserQuizAnswer;
use App\Models\UserChallengeCompletion;
use DB;

class QuizSubmissionController extends Controller
{
    // POST /quiz/answer
    public function storeAnswer(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'quiz_id' => 'required|integer',
            'question_id' => 'required|integer',
            'selected_answer_id' => 'required|integer'
        ]);

        $user = User::find($request->user_id);
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Invalid user ID'], 400);

        $quiz = Quiz::find($request->quiz_id);
        if (!$quiz) return response()->json(['status' => 'error', 'message' => 'Invalid quiz ID'], 400);

        $question = Question::where('quiz_id', $quiz->id)
            ->find($request->question_id);
        if (!$question) return response()->json(['status' => 'error', 'message' => 'Invalid question or not part of this quiz'], 400);

        $answer = Answer::where('question_id', $question->id)
            ->find($request->selected_answer_id);
        if (!$answer) return response()->json(['status' => 'error', 'message' => 'Invalid answer ID'], 400);

        $is_correct = $answer->correct_answer;

        $userAnswer = UserQuizAnswer::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
            'selected_answer_id' => $answer->id,
            'is_correct' => $is_correct
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Answer stored successfully',
            'data' => ['is_correct' => $is_correct]
        ]);
    }

    // GET /score?user_id=
    public function userTotalScore(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);
        $user = User::find($request->user_id);
        if (!$user) return response()->json(['status' => 'error', 'message' => 'Invalid user ID'], 400);

        $stats = UserChallengeCompletion::where('user_id', $user->id)
            ->selectRaw('COUNT(*) as challenges_completed, SUM(score) as total_score, SUM(time_taken) as total_time_taken')
            ->first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_score' => (int)$stats->total_score,
                'challenges_completed' => (int)$stats->challenges_completed,
                'total_time_taken' => (int)$stats->total_time_taken
            ]
        ]);
    }

    // GET /quiz/results?user_id=&quiz_id=
    public function userQuizResults(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'quiz_id' => 'required|integer'
        ]);

        $completion = UserChallengeCompletion::where([
            ['user_id', $request->user_id],
            ['challenge_id', $request->quiz_id],
            ['challenge_type', 'quiz']
        ])->first();

        if (!$completion) {
            return response()->json([
                'status' => 'success',
                'data' => ['completed' => false]
            ]);
        }

        $answers = UserQuizAnswer::where([
            ['user_id', $request->user_id],
            ['quiz_id', $request->quiz_id]
        ])->with(['question', 'selectedAnswer'])->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'completed' => true,
                'completion' => [
                    'score' => $completion->score,
                    'time_taken' => $completion->time_taken,
                    'completed_at' => $completion->completed_at
                ],
                'answers' => $answers->map(fn($a) => [
                    'question_id' => $a->question_id,
                    'question' => $a->question->question,
                    'selected_answer_id' => $a->selected_answer_id,
                    'selected_answer' => $a->selectedAnswer->answer,
                    'is_correct' => $a->is_correct,
                    'answered_at' => $a->answered_at
                ])
            ]
        ]);
    }
}
