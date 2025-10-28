<?php
namespace App\Http\Controllers;

use App\Models\Competition;
use Illuminate\Http\JsonResponse;

class CompetitionController extends Controller
{
    public function index(): JsonResponse
    {
        $competitions = Competition::orderBy('date_added', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $competitions]);
    }

    public function show($id): JsonResponse
    {
        $competition = Competition::find($id);
        if (!$competition) {
            return response()->json(['status' => 'error', 'message' => 'Competition not found'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $competition]);
    }
}
