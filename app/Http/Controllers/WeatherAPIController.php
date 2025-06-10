<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WeatherService;
use App\Services\ClothingAdviceService;

class WeatherAPIController extends Controller
{
    // 緯度経度を基に今日・明日の天気情報と服装アドバイスを取得するメソッド
    public function getWeather(Request $request, WeatherService $weatherService, ClothingAdviceService $clothingAdviceService)
    {
        // 緯度経度のバリデーション
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $lat = round($validated['latitude'], 6);
        $lon = round($validated['longitude'], 6);

        $weatherData = $weatherService->getWeatherData($lat, $lon);

        if (!$weatherData) {
            return response()->json([
                'status' => 'error',
                'message' => '天気情報の取得に失敗しました。',
            ], 500);
        }

        $result = [];
        foreach (['today' => 0, 'tomorrow' => 1] as $label => $index) {
            $formatted = $weatherService->formatWeather($weatherData, $index);
            $weatherForPrompt = $weatherService->WIForAdvice($weatherData, $index);

            $advice = $clothingAdviceService->suggestClothing($weatherForPrompt);

            $result[$label] = array_merge($formatted, $advice);
        }

        return response()->json([
            'status' => 'success',
            'weather' => $result,
            'message' => '天気情報の取得に成功しました。',
        ]);
    }
}
