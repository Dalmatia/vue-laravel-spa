<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherAPIController extends Controller
{
    public function getWeather(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ], [
            'latitude.required' => '緯度を指定してください。',
            'longitude.required' => '経度を指定してください。',
        ]);

        $lat = round($validated['latitude'], 6);
        $lon = round($validated['longitude'], 6);
        // キャッシュキーの生成
        $cacheKey = "weather_" . hash('sha256', "{$lat}_{$lon}");

        // 30分間キャッシュする
        $weatherData = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($lat, $lon) {
            $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,weathercode&timezone=Asia/Tokyo";
            $response = Http::get($url);

            if ($response->failed()) {
                Log::error("Weather API エラー: " . $response->body());
                return null;
            }

            return $response->json();
        });

        return response()->json([
            'status' => 'success',
            'weather' => [
                'today' => $this->formatWeather($weatherData, 0),
                'tomorrow' => $this->formatWeather($weatherData, 1),
            ],
            'message' => '天気情報の取得に成功しました。',
        ]);
    }

    private function formatWeather($data, $index)
    {
        if (!isset($data['daily']['time'][$index])) {
            return null;
        }

        $weatherCode = $data['daily']['weathercode'][$index] ?? null;
        $weatherDetails = $this->getWeatherDetails($weatherCode);

        return [
            'date' => date('n/j', strtotime($data['daily']['time'][$index])),
            'description' => $weatherDetails['description'],
            'weather_icon' => $weatherDetails['icon'],
            'max_temp' => isset($data['daily']['temperature_2m_max'][$index]) ? round($data['daily']['temperature_2m_max'][$index]) : '不明',
            'min_temp' => isset($data['daily']['temperature_2m_min'][$index]) ? round($data['daily']['temperature_2m_min'][$index]) : '不明',
            'precipitation_probability' => $data['daily']['precipitation_probability_max'][$index] ?? '不明',
        ];
    }

    private function getWeatherDetails($weatherCode)
    {
        if ($weatherCode === null) {
            return ['description' => '不明', 'icon' => '✨'];
        }

        $weatherDetails = [
            0 => ['description' => '快晴', 'icon' => '☀️'],
            1 => ['description' => '晴れ', 'icon' => '🌤'],
            2 => ['description' => '晴れ時々曇り', 'icon' => '⛅'],
            3 => ['description' => '曇り', 'icon' => '☁️'],
            45 => ['description' => '霧', 'icon' => '🌫'],
            48 => ['description' => '霧（霜）', 'icon' => '🌫'],
            50 => ['description' => '霧雨', 'icon' => '🌧'],
            55 => ['description' => '小雨', 'icon' => '☔'],
            60 => ['description' => '雨', 'icon' => '☔'],
            70 => ['description' => '雪', 'icon' => '☃'],
            80 => ['description' => 'にわか雨', 'icon' => '🌧'],
            90 => ['description' => '雪・雹', 'icon' => '❄️'],
            95 => ['description' => '雷雨', 'icon' => '⛈'],
            99 => ['description' => '雷雨（大氷雹）', 'icon' => '⛈'],
        ];

        foreach ($weatherDetails as $maxCode => $details) {
            if ($weatherCode <= $maxCode) {
                return $details;
            }
        }
        return ['description' => '不明', 'icon' => '✨'];
    }
}
