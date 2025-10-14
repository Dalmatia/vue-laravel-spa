<?php

namespace App\Domain\ClothingAdvice;

use Illuminate\Support\Facades\Cache;

class AdviceCache
{
  public function get(string $userId, string $date, ?string $tpo = null, ?string $cityId = null, ?string $profileHash = null): ?array
  {
    return Cache::get($this->buildKey($userId, $date, $tpo, $cityId, $profileHash));
  }

  public function put(string $userId, string $date, array $data, ?string $tpo = null, ?string $cityId = null, ?string $profileHash = null): void
  {
    Cache::put($this->buildKey($userId, $date, $tpo, $cityId, $profileHash), $data, now()->endOfDay());
  }

  public function getUsedItems(string $userId, string $date, ?string $cityId = null): array
  {
    return Cache::get($this->buildUsedItemsKey($userId, $date, $cityId), []);
  }

  public function putUsedItems(string $userId, string $date, array $itemIds, ?string $cityId = null): void
  {
    Cache::put($this->buildUsedItemsKey($userId, $date, $cityId), $itemIds, now()->endOfDay());
  }

  private function buildKey(string $userId, string $date, ?string $tpo = null, $cityId = null, ?string $profileHash = null): string
  {
    $parts = [
      'clothing_advice',
      $userId,
      $date,
    ];

    if ($cityId) $parts[] = "city_{$cityId}";
    if ($tpo) $parts[] = "tpo_{$tpo}";
    if ($profileHash) $parts[] = "hash_" . substr($profileHash, 0, 10);
    return implode('_', $parts);
  }

  private function buildUsedItemsKey(string $userId, string $date, ?string $cityId = null): string
  {
    return "clothing_advice_items_{$userId}_{$date}" . ($cityId ? "_city_{$cityId}" : '');
  }
}
