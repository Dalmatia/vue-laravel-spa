<?php

namespace App\Http\Controllers;

use App\Analyzers\WeatherAnalyzer;
use App\Formatters\WeatherFormatter;
use Illuminate\Http\Request;
use App\Services\WeatherService;
use App\Services\ClothingAdviceService;
use Illuminate\Support\Facades\Log;

class WeatherAPIController extends Controller
{
    private WeatherService $weatherService;
    private ClothingAdviceService $clothingAdviceService;
    private WeatherAnalyzer $weatherAnalyzer;
    private WeatherFormatter $weatherFormatter;

    public function __construct(
        WeatherService $weatherService,
        ClothingAdviceService $clothingAdviceService,
        WeatherAnalyzer $weatherAnalyzer,
        WeatherFormatter $weatherFormatter
    ) {
        $this->weatherService = $weatherService;
        $this->clothingAdviceService = $clothingAdviceService;
        $this->weatherAnalyzer = $weatherAnalyzer;
        $this->weatherFormatter = $weatherFormatter;
    }

    public function fetchWeatherWithAdvice(Request $request)
    {
        $coordinates = $this->validateCoordinates($request);
        $weatherData = $this->weatherService->getWeatherData($coordinates['latitude'], $coordinates['longitude']);

        if (!$weatherData) {
            Log::error('天気情報の取得に失敗しました。', [
                'latitude' => $coordinates['latitude'],
                'longitude' => $coordinates['longitude'],
            ]);
            return $this->weatherFetchErrorResponse();
        }

        $weatherAdviceByDay = $this->generateAdvicePerDay($weatherData);

        return response()->json([
            'status' => 'success',
            'weather' => $weatherAdviceByDay,
            'message' => '天気情報の取得に成功しました。',
        ]);
    }

    private function generateAdvicePerDay($weatherData): array
    {
        $result = [];
        foreach (['today' => 0, 'tomorrow' => 1] as $label => $index) {
            $formatted = $this->weatherFormatter->formatWeather($weatherData, $index);
            if (!$formatted) {
                Log::warning("天気データのフォーマットに失敗しました。", ['label' => $label, 'index' => $index]);
                continue;
            }
            $weatherInfo = $this->weatherAnalyzer->extractWeatherInfoForAdvice($weatherData, $index);
            $advice = $this->clothingAdviceService->suggestClothing($weatherInfo);

            $result[$label] = array_merge($formatted, $advice);
        }
        return $result;
    }

    private function validateCoordinates(Request $request): array
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        return [
            'latitude' => round($validated['latitude'], 6),
            'longitude' => round($validated['longitude'], 6),
        ];
    }

    private function weatherFetchErrorResponse()
    {
        return response()->json([
            'status' => 'error',
            'message' => '天気情報の取得に失敗しました。',
        ], 500);
    }
}
