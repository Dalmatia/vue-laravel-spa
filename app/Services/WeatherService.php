<?php

namespace App\Services;

use App\Enums\WeatherCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
  public function getWeatherData($lat, $lon)
  {
    $cacheKey = "weather_" . hash('sha256', "{$lat}_{$lon}");

    return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($lat, $lon) {
      $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}"
        . "&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_probability_mean,weathercode"
        . "&hourly=weathercode,relativehumidity_2m,windspeed_10m"
        . "&timezone=Asia/Tokyo";

      $response = Http::get($url);

      if ($response->failed()) {
        Log::error("Weather API エラー: " . $response->body());
        return null;
      }

      return $response->json();
    });
  }

  // 指定された日の天気データをフォーマットして返すメソッド
  public function formatWeather($data, $index)
  {
    if (!isset($data['daily']['time'][$index])) return null;

    $dateStr = now()->addDays($index)->format('Y-m-d');
    $weatherCode = $this->getDominantWeatherCodeForDate($data['hourly'], $dateStr)
      ?? ($data['daily']['weathercode'][$index] ?? null);

    $weatherDescribe = WeatherCode::weatherDescribe($weatherCode);
    $weatherIcon = WeatherCode::weatherIcon($weatherCode);

    return [
      'date' => date('n/j', strtotime($data['daily']['time'][$index])),
      'description' => $weatherDescribe,
      'weather_icon' => $weatherIcon,
      'max_temp' => isset($data['daily']['temperature_2m_max'][$index]) ? round($data['daily']['temperature_2m_max'][$index]) : '不明',
      'min_temp' => isset($data['daily']['temperature_2m_min'][$index]) ? round($data['daily']['temperature_2m_min'][$index]) : '不明',
      'precipitation_probability' => $data['daily']['precipitation_probability_max'][$index] ?? '不明',
      'precipitation_avg' => $data['daily']['precipitation_probability_mean'][$index] ?? null,
    ];
  }

  // 取得した天気データから、服装アドバイスに必要な情報を抽出するメソッド
  public function WIForAdvice($data, $index)
  {
    $date = $data['daily']['time'][$index] ?? now()->addDays($index)->format('Y-m-d');
    // 湿度と風速をhourlyから平均で取得
    [$humidityAvg, $windAvg] = $this->calculateAverageHourly($data['hourly'], $date);

    // 天気（最高/最低気温、降水確率、湿度、風速）を取得
    return [
      'max' => $data['daily']['temperature_2m_max'][$index] ?? null,
      'min' => $data['daily']['temperature_2m_min'][$index] ?? null,
      'pop' => $data['daily']['precipitation_probability_max'][$index] ?? null,
      'avgPop' => $data['daily']['precipitation_probability_mean'][$index] ?? null,
      'humidityAvg' => $humidityAvg ?? null,
      'windAvg' => $windAvg ?? null,
    ];
  }

  // 指定された日の湿度と風速の平均を計算するメソッド
  private function calculateAverageHourly($hourlyData, $targetDate)
  {
    $humidityTotal = 0;
    $windTotal = 0;
    $count = 0;

    foreach ($hourlyData['time'] as $i => $timestamp) {
      // 対象日（文字列がその日付で始まる）に一致するデータを抽出
      if (str_starts_with($timestamp, $targetDate)) {
        // 湿度と風速を合計
        $humidityTotal += $hourlyData['relativehumidity_2m'][$i] ?? 0;
        $windTotal += $hourlyData['windspeed_10m'][$i] ?? 0;
        $count++;
      }
    }

    if ($count === 0) return [null, null];

    return [
      round($humidityTotal / $count, 1),
      round($windTotal / $count, 1),
    ];
  }

  // 指定日の1時間ごとの天気コードから、最も頻度の高い天気コードを返す
  private function getDominantWeatherCodeForDate($hourlyData, $targetDate)
  {
    if (!isset($hourlyData['time'], $hourlyData['weathercode'])) {
      return null;
    }

    $counts = [];
    // $hourlyDataからtimeに一致する日付のデータをフィルタ
    foreach ($hourlyData['time'] as $i => $timestamp) {
      if (str_starts_with($timestamp, $targetDate)) {
        // 該当する時間のweathercodeをカウント
        $code = $hourlyData['weathercode'][$i];
        $counts[$code] = ($counts[$code] ?? 0) + 1;
      }
    }

    if (empty($counts)) return null;
    arsort($counts);
    return array_key_first($counts);
  }
}
