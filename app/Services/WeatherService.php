<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
  public function getWeatherData($lat, $lon)
  {
    $cacheKey = $this->generateCacheKey($lat, $lon);

    return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($lat, $lon) {
      $url = $this->buildWeatherApiUrl($lat, $lon);
      $response = Http::get($url);

      if ($response->failed()) {
        Log::error("Weather API エラー: ", [
          'url' => $url,
          'status' => $response->status(),
          'body' => $response->body(),
        ]);
        return null;
      }

      return $response->json();
    });
  }

  protected function generateCacheKey(float $lat, float $lon): string
  {
    return 'weather_' . hash('sha256', "{$lat}_{$lon}");
  }

  protected function buildWeatherApiUrl(float $lat, float $lon): string
  {
    return "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}"
      . "&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,precipitation_probability_mean,weathercode"
      . "&hourly=weathercode,relative_humidity_2m,windspeed_10m"
      . "&timezone=Asia/Tokyo";
  }
}
