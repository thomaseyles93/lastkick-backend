<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WhoAmI;
use Illuminate\Support\Facades\Validator;

class WhoAmIController extends Controller
{
    // GET /api/who-am-i
    public function index()
    {
        $challenges = WhoAmI::orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $challenges
        ]);
    }

    // GET /api/who-am-i/{id}
    public function show($id)
    {
        $challenge = WhoAmI::find($id);

        if (!$challenge) {
            return response()->json([
                'status' => 'error',
                'message' => 'Challenge not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $challenge
        ]);
    }

    // POST /api/who-am-i/bulk-add
    public function storeBulk(Request $request)
    {
        $challenges = $request->input('challenges', []);

        if (!is_array($challenges) || empty($challenges)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing challenges array or invalid format'
            ], 400);
        }

        $created = [];
        $duplicates = [];

        foreach ($challenges as $item) {
            $validator = Validator::make($item, [
                'title' => 'required|string|unique:who_am_i,title',
                'clue1' => 'required|string',
                'clue2' => 'required|string',
                'clue3' => 'required|string',
                'clue4' => 'required|string',
                'clue5' => 'required|string',
                'answer' => 'required|string',
            ]);

            if ($validator->fails()) {
                if (isset($item['title'])) {
                    $duplicates[] = $item['title'];
                }
                continue;
            }

            $created[] = WhoAmI::create($item);
        }

        $response = [
            'status' => 'success',
            'message' => count($created) . ' challenges created successfully',
        ];

        if (!empty($duplicates)) {
            $response['duplicates_skipped'] = $duplicates;
        }

        if (!empty($created)) {
            $response['data'] = $created;
        }

        return response()->json($response);
    }
}
