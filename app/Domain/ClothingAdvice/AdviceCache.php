<?php

namespace App\Domain\ClothingAdvice;

use Illuminate\Support\Facades\Cache;

class AdviceCache
{
  public function get(string $userId, string $date, ?string $tpo = null): ?array
  {
    return Cache::get($this->buildKey($userId, $date, $tpo));
  }

  public function put(string $userId, string $date, array $data, ?string $tpo = null): void
  {
    Cache::put($this->buildKey($userId, $date, $tpo), $data, now()->endOfDay());
  }

  public function getUsedItems(string $userId, string $date): array
  {
    return Cache::get("clothing_advice_items_{$userId}_{$date}", []);
  }

  public function putUsedItems(string $userId, string $date, array $itemIds): void
  {
    Cache::put("clothing_advice_items_{$userId}_{$date}", $itemIds, now()->endOfDay());
  }

  private function buildKey(string $userId, string $date, ?string $tpo = null): string
  {
    $suffix = $tpo ? "_{$tpo}" : '';
    return "clothing_advice_{$userId}_{$date}{$suffix}";
  }
}
