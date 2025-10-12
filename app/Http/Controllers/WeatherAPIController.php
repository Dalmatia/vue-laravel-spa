<?php

namespace App\Http\Controllers;

use App\Formatters\WeatherFormatter;
use Illuminate\Http\Request;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Log;

class WeatherAPIController extends Controller
{
    private WeatherService $weatherService;
    private WeatherFormatter $weatherFormatter;

    public function __construct(
        WeatherService $weatherService,
        WeatherFormatter $weatherFormatter
    ) {
        $this->weatherService = $weatherService;
        $this->weatherFormatter = $weatherFormatter;
    }

    public function fetchWeather(Request $request)
    {
        $coordinates = $this->validateCoordinates($request);
        $weatherData = $this->weatherService->getWeatherData($coordinates['latitude'], $coordinates['longitude']);

        if (!$weatherData) {
            Log::error('天気情報の取得に失敗しました。', $coordinates);
            return $this->weatherFetchErrorResponse();
        }

        $result = [];
        foreach (['today' => 0, 'tomorrow' => 1] as $label => $index) {
            $formatted = $this->weatherFormatter->formatWeather($weatherData, $index);
            $result[$label] = $formatted;
        }

        return response()->json([
            'status' => 'success',
            'weather' => $result,
        ]);
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
