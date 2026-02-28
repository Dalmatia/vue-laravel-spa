<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\AdviceCache;
use Illuminate\Support\Facades\Log;

final class AdviceResultPersister
{
  public function __construct(
    private AdviceCache $cache
  ) {}

  /**
   * キャッシュ & 使用アイテムを保存
   */
  public function storeResult(int $userId, string $date, array $result, array $matchedItems, ?string $tpo = null, ?string $cityId = null, ?string $profileHash = null, bool $isAiAvailable = true): void
  {
    $this->cache->put($userId, $date, $result, $tpo, $cityId, $profileHash);

    $alreadySaved = $this->cache->getUsedItems(
      $userId,
      $date,
      $tpo,
      $cityId,
      $profileHash
    );

    if (!empty($alreadySaved)) {
      return;
    }

    $usedItemIds = [];
    foreach ($matchedItems as $data) {
      $item = $data['item'] ?? null;

      if (is_object($item) && isset($item->id)) {
        $usedItemIds[] = $item->id;
      } elseif (is_array($item) && isset($item['id'])) {
        $usedItemIds[] = $item['id'];
      }
    }

    if (empty($usedItemIds)) {
      return;
    }

    $this->cache->putUsedItems($userId, $date, $usedItemIds, $tpo, $cityId, $profileHash);
  }
}
