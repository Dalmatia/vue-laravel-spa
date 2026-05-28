<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
  private const API_URL = 'https://api.open-meteo.com/v1/forecast';

  public function getWeatherData(float $lat, float $lon): ?array
  {
    $cacheKey = $this->generateCacheKey($lat, $lon);

    return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($lat, $lon) {
      $query = $this->buildWeatherQuery($lat, $lon);
      try {
        $response = Http::retry(3, 500)
          ->timeout(10)
          ->get(self::API_URL, $query)
          ->throw();

        return $response->json();
      } catch (\Throwable $e) {

        Log::error('Weather API connection error', [
          'url' => self::API_URL,
          'query' => $query,
          'message' => $e->getMessage(),
        ]);

        return null;
      }
    });
  }

  protected function generateCacheKey(float $lat, float $lon): string
  {
    return 'weather_' . hash('sha256', "{$lat}_{$lon}");
  }

  protected function buildWeatherQuery(float $lat, float $lon): array
  {
    return [
      'latitude' => $lat,
      'longitude' => $lon,
      'daily' =>
      'temperature_2m_max,temperature_2m_min,' .
        'precipitation_probability_max,' .
        'precipitation_probability_mean,' .
        'weathercode',

      'hourly' =>
      'weathercode,' .
        'relative_humidity_2m,' .
        'windspeed_10m',

      'timezone' => 'Asia/Tokyo',
    ];
  }
}
