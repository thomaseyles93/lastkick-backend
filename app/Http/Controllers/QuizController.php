<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\User;
use DB;

class QuizController extends Controller
{
    // GET /quizzes
    public function index(Request $request)
    {
        if ($request->has('id')) {
            return response()->json([
                'status' => 'success',
                'data' => Quiz::with('questions.answers')->find($request->id)
            ]);
        }

        if ($request->has('competition_id')) {
            return response()->json([
                'status' => 'success',
                'data' => Quiz::with('questions.answers')
                    ->where('competition_id', $request->competition_id)
                    ->get()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => Quiz::with('questions.answers')->orderByDesc('date_added')->get()
        ]);
    }

    // GET /questions?quiz_id=
    public function getQuestions(Request $request)
    {
        $request->validate(['quiz_id' => 'required|integer']);

        return response()->json([
            'status' => 'success',
            'data' => Question::where('quiz_id', $request->quiz_id)->get()
        ]);
    }

    // GET /answers?question_id=
    public function getAnswers(Request $request)
    {
        $request->validate(['question_id' => 'required|integer']);

        return response()->json([
            'status' => 'success',
            'data' => Answer::where('question_id', $request->question_id)->get()
        ]);
    }

    // GET /questions-with-answers?quiz_id=
    public function getQuestionsWithAnswers(Request $request)
    {
        $request->validate(['quiz_id' => 'required|integer']);

        $quiz = Quiz::with('questions.answers')->find($request->quiz_id);

        return response()->json([
            'status' => 'success',
            'data' => $quiz
        ]);
    }

    // POST /questions/bulk-add
    public function bulkAddQuestions(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|integer',
            'questions' => 'required|array'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->questions as $q) {
                $question = Question::create([
                    'quiz_id' => $request->quiz_id,
                    'question' => $q['question']
                ]);

                if (isset($q['answers']) && is_array($q['answers'])) {
                    foreach ($q['answers'] as $a) {
                        Answer::create([
                            'question_id' => $question->id,
                            'answer' => $a['answer'],
                            'correct_answer' => $a['correct_answer']
                        ]);
                    }
                }
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Questions and answers added successfully']);
    }

    // POST /quizzes/bulk-add
    public function bulkAddQuizzes(Request $request)
    {
        $request->validate([
            'quizzes' => 'required|array',
            'competition_id' => 'nullable|integer'
        ]);

        $created_quizzes = [];

        DB::transaction(function () use ($request, &$created_quizzes) {
            foreach ($request->quizzes as $q) {
                $quiz = Quiz::create([
                    'title' => $q['title'],
                    'competition_id' => $request->competition_id ?? null
                ]);

                $quiz_data = ['id' => $quiz->id, 'title' => $quiz->title, 'questions' => []];

                if (isset($q['questions']) && is_array($q['questions'])) {
                    foreach ($q['questions'] as $quest) {
                        $question = Question::create([
                            'quiz_id' => $quiz->id,
                            'question' => $quest['question']
                        ]);

                        $question_data = ['id' => $question->id, 'question' => $question->question, 'answers' => []];

                        if (isset($quest['answers']) && is_array($quest['answers'])) {
                            foreach ($quest['answers'] as $ans) {
                                $answer = Answer::create([
                                    'question_id' => $question->id,
                                    'answer' => $ans['answer'],
                                    'correct_answer' => $ans['correct_answer']
                                ]);
                                $question_data['answers'][] = $answer;
                            }
                        }

                        $quiz_data['questions'][] = $question_data;
                    }
                }

                $created_quizzes[] = $quiz_data;
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Quizzes, questions and answers added successfully',
            'data' => $created_quizzes
        ]);
    }

    // GET /quiz/random-unanswered?user_id=
    public function randomUnanswered(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Invalid user ID'], 400);
        }

        $quiz = Quiz::whereNotIn('id', function ($q) use ($user) {
            $q->select('challenge_id')->from('user_challenges_completions')->where('user_id', $user->id);
        })->inRandomOrder()->with('questions.answers')->first();

        if (!$quiz) {
            return response()->json(['status' => 'success', 'data' => null, 'message' => 'No unanswered quizzes available']);
        }

        return response()->json(['status' => 'success', 'data' => $quiz]);
    }
}
