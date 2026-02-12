<?php

namespace App\Domain\Weather;

use App\Enums\Season;
use Carbon\CarbonImmutable;

final class WeatherDto
{
  public function __construct(
    private readonly CarbonImmutable $date,
    private readonly float $max,
    private readonly float $min,
    private readonly int $pop,
    private readonly int $humidityAvg,
    private readonly float $windAvg,
    private readonly float $feelsLike,
  ) {}

  public static function fromApi(array $data, CarbonImmutable $date): self
  {
    $max = (float) $data['max'];
    $min = (float) $data['min'];

    // 日中の代表気温として平均を使う
    $temp = ($max + $min) / 2;

    $humidity = (int) ($data['humidityAvg'] ?? 60);
    $wind = (float) ($data['windAvg'] ?? 2);

    $feelsLike = self::calculateFeelsLike($temp, $humidity, $wind);

    return new self(
      date: $date,
      max: $max,
      min: $min,
      pop: (int) $data['pop'],
      humidityAvg: $humidity,
      windAvg: $wind,
      feelsLike: $feelsLike,
    );
  }

  private static function calculateFeelsLike(
    float $tempC,
    int $humidity,
    float $windMs
  ): float {
    // 寒い場合：風冷指数
    if ($tempC <= 10 && $windMs > 1.3) {
      $windKmh = $windMs * 3.6;

      return round(
        13.12
          + 0.6215 * $tempC
          - 11.37 * pow($windKmh, 0.16)
          + 0.3965 * $tempC * pow($windKmh, 0.16),
        1
      );
    }

    // 暑い場合：簡易 Heat Index
    if ($tempC >= 27 && $humidity >= 40) {
      return round(
        -8.784695
          + 1.61139411 * $tempC
          + 2.338549 * $humidity
          - 0.14611605 * $tempC * $humidity,
        1
      );
    }

    // それ以外は実温度
    return round($tempC, 1);
  }

  /** 最高気温 */
  public function max(): float
  {
    return $this->max;
  }

  /** 最低気温 */
  public function min(): float
  {
    return $this->min;
  }

  /** 降水確率 */
  public function pop(): int
  {
    return $this->pop;
  }

  /** 平均湿度 */
  public function humidityAvg(): int
  {
    return $this->humidityAvg;
  }

  /** 平均風速 */
  public function windAvg(): float
  {
    return $this->windAvg;
  }

  /** 体感温度 */
  public function feelsLike(): float
  {
    return $this->feelsLike;
  }

  public function season(): int
  {
    $month = $this->date->month;

    return match (true) {
      in_array($month, [3, 4, 5])  => Season::spring,
      in_array($month, [6, 7, 8])  => Season::summer,
      in_array($month, [9, 10, 11])  => Season::fall,
      default                      => Season::winter,
    };
  }
}
