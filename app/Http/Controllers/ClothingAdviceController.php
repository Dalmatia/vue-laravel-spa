<?php

namespace App\Http\Controllers;

use App\Application\ClothingAdvice\ClothingAdviceUseCase;
use App\Domain\Weather\WeatherDto;
use Carbon\CarbonImmutable;
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
            'weather' => 'required|array',
            'selectedTab' => 'required|in:today,tomorrow',
            'tpo' => 'nullable|string',
            'targetDate' => 'required|date',
            'cityId' => 'nullable|integer',
        ]);

        $userId = Auth::id();

        $w = $validated['weather'][$validated['selectedTab']] ?? null;

        if (!$w) {
            return response()->json(['message' => '天気データが不正です'], 422);
        }

        $date = CarbonImmutable::parse($validated['targetDate']);

        $weatherDto = WeatherDto::fromApi(
            [
                'max'         => $w['max_temp'] ?? null,
                'min'         => $w['min_temp'] ?? null,
                'pop'         => $w['precipitation_probability'] ?? null,
                'humidityAvg' => $w['humidity'] ?? 60,
                'windAvg'     => $w['wind_speed'] ?? 2,
            ],
            $date
        );

        $result = $this->useCase->handle(
            $weatherDto,
            $userId,
            $validated['targetDate'],
            $validated['tpo'] ?? null,
            $validated['cityId'] ?? null
        );

        return response()->json($result);
    }
}
