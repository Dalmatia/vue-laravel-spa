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
            'useJson' => 'nullable|boolean',
        ]);

        $userId = Auth::id();

        $result = !empty($validated['useJson'])
            ? $this->clothingAdviceService->suggestClothingJson(
                $validated['weatherData'],
                $userId,
                $validated['tpo'] ?? null
            )
            : $this->clothingAdviceService->suggestClothing(
                $validated['weatherData'],
                $userId,
                null,
                $validated['tpo'] ?? null
            );

        return response()->json($result);
    }
}
