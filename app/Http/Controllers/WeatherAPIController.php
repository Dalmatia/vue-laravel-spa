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

        return [
            'date' => date('n/j', strtotime($data['daily']['time'][$index])),
            'description' => $this->getWeatherDescription($weatherCode),
            'max_temp' => isset($data['daily']['temperature_2m_max'][$index]) ? round($data['daily']['temperature_2m_max'][$index]) : '不明',
            'min_temp' => isset($data['daily']['temperature_2m_min'][$index]) ? round($data['daily']['temperature_2m_min'][$index]) : '不明',
            'precipitation_probability' => $data['daily']['precipitation_probability_max'][$index] ?? '不明',
            'weather_icon' => $this->getWeatherIcon($data['daily']['weathercode'][$index] ?? null),
        ];
    }

    private function getWeatherDescription($weatherCode)
    {
        $descriptions = [
            0 => '晴れ',
            1 => '主に晴れ',
            2 => '部分的に曇り',
            3 => '曇り',
            45 => '霧',
            48 => '霧（霜）',
            51 => '小雨',
            53 => '中雨',
            55 => '強雨',
            61 => '小雨',
            63 => '中雨',
            65 => '大雨',
            71 => '小雪',
            73 => '中雪',
            75 => '大雪',
            77 => 'みぞれ',
            80 => '局地的な小雨',
            81 => '局地的な中雨',
            82 => '局地的な大雨',
            85 => '吹雪（小雪）',
            86 => '吹雪（大雪）',
            95 => '雷雨',
            96 => '雷雨（小氷雹）',
            99 => '雷雨（大氷雹）',
        ];

        return $descriptions[$weatherCode] ?? '不明';
    }

    private function getWeatherIcon($weatherCode)
    {
        $icons = [
            0 => '☀️', // 晴れ
            1 => '🌤', // 主に晴れ
            2 => '⛅', // 部分的に曇り
            3 => '☁️', // 曇り
            45 => '🌫', // 霧
            48 => '🌫', // 霧（霜）
            51 => '🌦', // 小雨
            53 => '🌦', // 中雨
            55 => '🌦', // 強雨
            61 => '🌧', // 小雨
            63 => '🌧', // 中雨
            65 => '🌧', // 大雨
            71 => '🌨', // 小雪
            73 => '🌨', // 中雪
            75 => '❄️', // 大雪
            77 => '🌧❄️', // みぞれ
            80 => '🌦', // 局地的な小雨
            81 => '🌧', // 局地的な中雨
            82 => '⛈', // 局地的な大雨
            85 => '🌨🌬', // 吹雪（小雪）
            86 => '🌨🌬', // 吹雪（大雪）
            95 => '⛈', // 雷雨
            96 => '⛈', // 雷雨（小氷雹）
            99 => '⛈', // 雷雨（大氷雹）
        ];

        return $icons[$weatherCode] ?? '❓'; // 未知の天気コード
    }
}
