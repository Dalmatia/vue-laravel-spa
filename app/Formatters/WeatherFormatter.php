<?php

namespace App\Formatters;

use App\Analyzers\WeatherAnalyzer;
use App\Enums\WeatherCode;

class WeatherFormatter
{
  public function __construct(
    protected WeatherAnalyzer $analyzer
  ) {}

  // 指定された日の天気データをフォーマットして返すメソッド
  public function formatWeather(array $data, int $index, array $weatherInfo)
  {
    $weatherCode = $this->getWeatherCodeForDisplay($data, $index);

    return [
      'date' => $weatherInfo['date'] ?? date('Y-m-d', strtotime($data['daily']['time'][$index])),

      'description' => WeatherCode::weatherDescribe($weatherCode),
      'weather_icon' => WeatherCode::weatherIcon($weatherCode),

      'max_temp' => $weatherInfo['max'],
      'min_temp' => $weatherInfo['min'],
      'feels_like' => $weatherInfo['feels_like'],

      'humidity_avg' => $weatherInfo['humidityAvg'],
      'wind_avg' => $weatherInfo['windAvg'],

      'precipitation_probability' => $weatherInfo['pop'],
      'precipitation_avg' => $weatherInfo['avgPop'],
    ];
  }

  private function getWeatherCodeForDisplay(array $data, int $index): ?int
  {
    $date = now()->addDays($index)->format('Y-m-d');
    return $this->analyzer->getDominantWeatherCodeForDate($data['hourly'], $date)
      ?? ($data['daily']['weathercode'][$index] ?? null);
  }
}
