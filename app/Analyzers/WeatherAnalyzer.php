<?php

namespace App\Analyzers;

class WeatherAnalyzer
{
  // 指定された日の湿度と風速の平均を計算するメソッド
  public function calculateAverageHourly($hourlyData, $targetDate)
  {
    $humidityTotal = 0;
    $windTotal = 0;
    $count = 0;

    foreach ($hourlyData['time'] as $i => $timestamp) {
      // 対象日（文字列がその日付で始まる）に一致するデータを抽出
      if (isset($hourlyData['relativehumidity_2m'][$i], $hourlyData['windspeed_10m'][$i])) {
        // 湿度と風速を合計
        $humidityTotal += $hourlyData['relativehumidity_2m'][$i];
        $windTotal += $hourlyData['windspeed_10m'][$i];
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
  public function getDominantWeatherCodeForDate($hourlyData, $targetDate)
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

  // 体感温度の平均を計算するメソッド
  public function calculateAverageFeelsLike($hourlyData, string $targetDate): ?float
  {
    if (!isset($hourlyData['time'], $hourlyData['apparent_temperature'])) {
      return null;
    }

    $total = 0;
    $count = 0;

    foreach ($hourlyData['time'] as $i => $timestamp) {
      if (
        str_starts_with($timestamp, $targetDate) &&
        isset($hourlyData['apparent_temperature'][$i])
      ) {
        $total += $hourlyData['apparent_temperature'][$i];
        $count++;
      }
    }

    return $count > 0 ? round($total / $count, 1) : null;
  }

  // 取得した天気データから、服装アドバイスに必要な情報を抽出するメソッド
  public function extractWeatherInfoForAdvice($data, $index)
  {
    $date = $data['daily']['time'][$index] ?? now()->addDays($index)->format('Y-m-d');
    // 湿度と風速をhourlyから平均で取得
    [$humidityAvg, $windAvg] = $this->calculateAverageHourly($data['hourly'], $date);
    $feelsLike = $this->calculateAverageFeelsLike($data['hourly'], $date);

    // 天気（最高/最低気温、降水確率、湿度、風速）を取得
    return [
      'max' => isset($data['daily']['temperature_2m_max'][$index])
        ? round($data['daily']['temperature_2m_max'][$index])
        : null,

      'min' => isset($data['daily']['temperature_2m_min'][$index])
        ? round($data['daily']['temperature_2m_min'][$index])
        : null,

      'pop' => $data['daily']['precipitation_probability_max'][$index] ?? null,
      'avgPop' => $data['daily']['precipitation_probability_mean'][$index] ?? null,
      'humidityAvg' => $humidityAvg,
      'windAvg' => $windAvg,
      'feels_like' => $feelsLike,
    ];
  }
}
