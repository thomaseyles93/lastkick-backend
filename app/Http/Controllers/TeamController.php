<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Competition;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    // GET /teams?competition_id=
    public function index(Request $request)
    {
        $competition_id = $request->query('competition_id');

        $teams = Team::with('competition')
            ->when($competition_id, fn($q) => $q->where('competition_id', $competition_id))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($team) => [
                'id' => $team->id,
                'title' => $team->title,
                'competition_id' => $team->competition_id,
                'competition_title' => $team->competition->title ?? null,
                'date_created' => $team->created_at,
            ]);

        return response()->json(['status' => 'success', 'data' => $teams]);
    }

    // POST /teams
    public function store(Request $request)
    {
        $request->validate([
            'teams' => 'required|array',
            'teams.*.title' => 'required|string',
            'teams.*.competition_id' => 'required|integer|exists:competitions,id'
        ]);

        $created_teams = [];
        $duplicates = [];

        DB::beginTransaction();
        try {
            foreach ($request->teams as $teamData) {
                $exists = Team::where('title', $teamData['title'])
                    ->where('competition_id', $teamData['competition_id'])
                    ->first();

                if ($exists) {
                    $duplicates[] = $teamData['title'];
                    continue;
                }

                $team = Team::create([
                    'title' => $teamData['title'],
                    'competition_id' => $teamData['competition_id']
                ]);

                $created_teams[] = $team;
            }

            DB::commit();

            $response = [
                'status' => 'success',
                'message' => count($created_teams) . ' teams created successfully',
                'data' => $created_teams
            ];

            if (!empty($duplicates)) {
                $response['duplicates_skipped'] = $duplicates;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
