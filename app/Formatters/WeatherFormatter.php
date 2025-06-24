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
  public function formatWeather($data, $index)
  {
    if (!isset($data['daily']['time'][$index])) return null;

    $weatherCode = $this->getWeatherCodeForDisplay($data, $index);
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

  private function getWeatherCodeForDisplay(array $data, int $index): ?int
  {
    $date = now()->addDays($index)->format('Y-m-d');
    return $this->analyzer->getDominantWeatherCodeForDate($data['hourly'], $date)
      ?? ($data['daily']['weathercode'][$index] ?? null);
  }
}
