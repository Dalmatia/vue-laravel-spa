<?php

namespace App\Domain\ClothingAdvice;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AdviceCache
{
  public function get(string $userId, string $date): ?array
  {
    return Cache::get("clothing_advice_{$userId}_{$date}");
  }

  public function put(string $userId, string $date, array $data): void
  {
    Cache::put("clothing_advice_{$userId}_{$date}", $data, now()->endOfDay());
  }

  public function getUsedItems(string $userId, string $date): array
  {
    return Cache::get("clothing_advice_items_{$userId}_{$date}", []);
  }

  public function putUsedItems(string $userId, string $date, array $itemIds): void
  {
    Cache::put("clothing_advice_items_{$userId}_{$date}", $itemIds, now()->endOfDay());
  }
}
