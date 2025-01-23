<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherAPIController extends Controller
{
    public function geocoding(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string|max:255',
        ]);

        $city = $validated['city'];
        $apiKey = env('WEATHER_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'WEATHER_API_KEYが設定されていません。'], 500);
        }

        $url = "http://api.openweathermap.org/geo/1.0/direct?q={$city},JP&limit=1&appid={$apiKey}";

        try {
            $client = new Client();
            $response = $client->get($url);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('ジオコーディングAPIのリクエストに失敗しました。');
            }

            $data = json_decode($response->getBody(), true);

            if (empty($data)) {
                return response()->json(['error' => '指定された地域が見つかりません。'], 404);
            }

            return response()->json([
                'lat' => $data[0]['lat'],
                'lon' => $data[0]['lon'],
            ]);
        } catch (\Exception $e) {
            Log::error('ジオコーディングAPIエラー: ' . $e->getMessage());
            return response()->json(['error' => 'ジオコーディングに失敗しました。'], 500);
        }
    }

    public function getWeather(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
        ]);

        $apiKey = env('WEATHER_API_KEY');
        if (!$apiKey) {
            throw new \RuntimeException('WEATHER_API_KEY が設定されていません。');
        }

        $lat = round($validated['lat'] ?? 35.6897, 2); // デフォルトは東京（緯度小数点以下2桁に丸める）
        $lon = round($validated['lon'] ?? 139.6917, 2);
        $url = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&appid={$apiKey}&units=metric&lang=ja";

        try {
            $cacheKey = "weather_" . hash('sha256', "{$lat}_{$lon}");
            $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($url) {
                $client = new Client();
                $response = $client->get($url);

                if ($response->getStatusCode() !== 200) {
                    throw new \Exception("APIリクエスト失敗: {$response->getReasonPhrase()}");
                }

                return json_decode($response->getBody()->getContents(), true);
            });

            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));

            return response()->json([
                'status' => 'success',
                'weather' => [
                    'today' => $this->getDailyData($data['list'], $today),
                    'tomorrow' => $this->getDailyData($data['list'], $tomorrow),
                ],
                'message' => '天気情報の取得に成功しました。',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Weather API エラー: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => '天気情報の取得に失敗しました。',
            ], 500);
        }
    }

    private function getDailyData(array $forecastList, string $date): array
    {
        $dailyData = $this->filterDaytimeData($forecastList, $date);
        $statistics = $this->calculateDailyStatistics($dailyData);

        // 日付を追加
        return array_merge($statistics, [
            'date' => date('n/j', strtotime($date)),
        ]);
    }

    private function filterDaytimeData(array $forecastList, string $date): array
    {
        return array_filter($forecastList, function ($item) use ($date) {
            return strpos($item['dt_txt'], $date) === 0;
        });
    }

    private function calculateDailyStatistics(array $daytimeData): array
    {
        $weatherCounts = [];
        $maxTemp = PHP_FLOAT_MIN;
        $minTemp = PHP_FLOAT_MAX;
        $precipitationProbability = 0;
        $dataCount = count($daytimeData);

        foreach ($daytimeData as $forecast) {
            $icon = $this->convertIconToDay($forecast['weather'][0]['icon']);
            $weatherKey = $forecast['weather'][0]['description'] . '|' . $icon;

            $weatherCounts[$weatherKey] = ($weatherCounts[$weatherKey] ?? 0) + 1;
            // 最高気温と最低気温
            $maxTemp = max($maxTemp, $forecast['main']['temp_max']);
            $minTemp = min($minTemp, $forecast['main']['temp_min']);
            // 降水確率
            if (isset($forecast['pop']) && is_numeric($forecast['pop'])) {
                $precipitationProbability += $forecast['pop'];
            }
        }

        // 降水確率を平均に変換（0〜1の範囲を%に変換）
        $averagePrecipitationProbability = $dataCount > 0
            ? round(($precipitationProbability / $dataCount) * 100)
            : 0;

        if (!empty($weatherCounts)) {
            $mostFrequentWeather = array_keys($weatherCounts, max($weatherCounts))[0];
            [$description, $icon] = explode('|', $mostFrequentWeather);
        } else {
            $description = 'データなし';
            $icon = '01d'; // 晴れのアイコン
        }

        return [
            'description' => $description,
            'icon' => $icon,
            'max_temp' => round($maxTemp),
            'min_temp' => round($minTemp),
            'precipitation_probability' => $averagePrecipitationProbability,
        ];
    }

    private function convertIconToDay(string $icon): string
    {
        return str_replace('n', 'd', $icon);
    }
}
