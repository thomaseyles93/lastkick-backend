<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DailyChallenge;
use App\Models\DailyChallengeAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DailyChallengeController extends Controller
{
    public function index()
    {
        $challenges = DailyChallenge::withCount('answers')
            ->orderByDesc('daily_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $challenges
        ]);
    }

    public function showByDate($date)
    {
        $challenge = DailyChallenge::with('answers')
            ->whereDate('daily_date', $date)
            ->first();

        if (!$challenge) {
            return response()->json([
                'status' => 'error',
                'message' => 'No daily challenge found for this date'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $challenge
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'daily_date' => 'required|date|unique:daily_challenges,daily_date',
            'answers' => 'required|array|min:1',
            'answers.*.answer_title' => 'required|string|max:255',
            'answers.*.answer_info' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated, &$challenge) {
            $challenge = DailyChallenge::create([
                'title' => $validated['title'],
                'daily_date' => $validated['daily_date'],
            ]);

            foreach ($validated['answers'] as $answer) {
                $challenge->answers()->create([
                    'answer_title' => $answer['answer_title'],
                    'answer_info' => $answer['answer_info'] ?? null,
                ]);
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Daily challenge added successfully',
            'data' => $challenge->load('answers')
        ]);
    }

    public function update(Request $request, $id)
    {
        $challenge = DailyChallenge::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'daily_date' => 'sometimes|required|date|unique:daily_challenges,daily_date,' . $challenge->id,
            'answers' => 'sometimes|array|min:1',
            'answers.*.answer_title' => 'required|string|max:255',
            'answers.*.answer_info' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($challenge, $validated) {
            $challenge->update($validated);

            if (isset($validated['answers'])) {
                $challenge->answers()->delete();

                foreach ($validated['answers'] as $answer) {
                    $challenge->answers()->create([
                        'answer_title' => $answer['answer_title'],
                        'answer_info' => $answer['answer_info'] ?? null,
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Daily challenge updated successfully',
            'data' => $challenge->load('answers')
        ]);
    }

    public function destroy($id)
    {
        $challenge = DailyChallenge::findOrFail($id);
        $challenge->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Daily challenge deleted successfully'
        ]);
    }
}
