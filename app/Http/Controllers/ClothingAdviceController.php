<?php

namespace App\Http\Controllers;

use App\Services\ClothingAdviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClothingAdviceController extends Controller
{
    public function __construct(
        private ClothingAdviceService $clothingAdviceService
    ) {}

    /**
     * AI服装アドバイスを生成
     * POST /api/clothing_advice
     */
    public function generateAdvice(Request $request)
    {
        $validated = $request->validate([
            'weatherData' => 'required|array',
            'tpo' => 'nullable|string',
            'targetDate' => 'nullable|date',
        ]);

        $userId = Auth::id();

        try {
            $advice = $this->clothingAdviceService->suggestClothing(
                $validated['weatherData'],
                $userId,
                $validated['targetDate'] ?? now()->toDateString(),
                $validated['tpo'] ?? null
            );

            return response()->json($advice);
        } catch (\Throwable $e) {
            Log::error('服装アドバイス生成エラー: ' . $e->getMessage(), [
                'user_id' => $userId,
                'tpo' => $validated['tpo'] ?? null,
            ]);

            return response()->json([
                'error' => '服装アドバイスの生成に失敗しました。',
            ], 500);
        }
    }
}
