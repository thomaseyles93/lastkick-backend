<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlayerController extends Controller
{
    /**
     * Search players by term
     */
    public function search(Request $request)
    {
        $term = trim($request->input('term'));

        if (empty($term)) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        $normalizedTerm = $this->normalizeText($term);
        $searchTerm = "%{$normalizedTerm}%";
        $startTerm = "{$normalizedTerm}%";

        $players = Player::select(
            'id',
            'name',
            'full_name',
            DB::raw("
                    CASE
                        WHEN LOWER(full_name) = LOWER(?) THEN 100
                        WHEN LOWER(full_name) LIKE LOWER(?) THEN 90
                        WHEN LOWER(full_name) LIKE LOWER(?) THEN 80
                        WHEN LOWER(name) = LOWER(?) THEN 85
                        WHEN LOWER(name) LIKE LOWER(?) THEN 75
                        WHEN LOWER(name) LIKE LOWER(?) THEN 65
                        ELSE 50
                    END as relevance
                ", [
                $normalizedTerm, $startTerm, $searchTerm,
                $normalizedTerm, $startTerm, $searchTerm
            ])
        )
            ->where(function ($query) use ($searchTerm) {
                $query->where(DB::raw('LOWER(full_name)'), 'like', strtolower($searchTerm))
                    ->orWhere(DB::raw('LOWER(name)'), 'like', strtolower($searchTerm));
            })
            ->orderByDesc('relevance')
            ->orderBy('full_name')
            ->limit(10)
            ->get()
            ->map(function ($player) {
                return [
                    'id' => (int)$player->id,
                    'name' => $player->name,
                    'full_name' => $player->full_name
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $players
        ]);
    }

    /**
     * Normalize text by removing accents and special characters
     */
    private function normalizeText($text)
    {
        $text = strtolower($text);
        $text = $this->removeAccents($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    /**
     * Remove accents from text
     */
    private function removeAccents($text)
    {
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ñ' => 'n', 'ç' => 'c',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Ñ' => 'N', 'Ç' => 'C'
        ];
        return strtr($text, $accents);
    }
}
