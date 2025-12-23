<?php

namespace App\Http\Controllers;

use App\Application\ClothingAdvice\ClothingAdviceUseCase;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClothingAdviceController extends Controller
{
    public function __construct(
        private ClothingAdviceUseCase $useCase
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
            'targetDate' => 'required|date',
            'cityId' => 'nullable|integer',
        ]);

        $userId = Auth::id();

        $result = $this->useCase->handle(
            $validated['weatherData'],
            $userId,
            $validated['targetDate'],
            $validated['tpo'] ?? null,
            $validated['cityId'] ?? null
        );

        return response()->json($result);
    }
}
