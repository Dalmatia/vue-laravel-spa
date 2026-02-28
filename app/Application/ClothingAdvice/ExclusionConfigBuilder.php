<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\AdviceCache;
use App\Enums\MainCategory;
use Carbon\Carbon;

final class ExclusionConfigBuilder
{
  public function __construct(
    private AdviceCache $cache
  ) {}

  public function exclusionConfig(int $userId, string $date, ?string $tpo, ?string $cityId, ?string $profileHash): array
  {
    $yesterdayKey = Carbon::parse($date)->subDay()->toDateString();

    $excludeItemIds = $this->cache->getUsedItems($userId, $yesterdayKey, $tpo, $cityId, $profileHash);
    // getUsedItems がカテゴリ別に返す場合は統合して id の平坦配列にする
    if (!empty($excludeItemIds) && is_array($excludeItemIds)) {
      $flat = [];
      array_walk_recursive($excludeItemIds, function ($v) use (&$flat) {
        $flat[] = $v;
      });
      $excludeItemIds = array_values(array_filter(array_unique($flat)));
    }

    $alreadyUsedColors = [
      MainCategory::outer => [],
      MainCategory::tops => [],
      MainCategory::bottoms => [],
      MainCategory::shoes => [],
    ];

    $yesterday = $this->cache->get($userId, $yesterdayKey, $tpo, $cityId, $profileHash) ?? [];
    if (!empty($yesterday['outfit_suggestion'])) {
      foreach ($yesterday['outfit_suggestion'] as $cat => $data) {
        if (!empty($data['item']['color'])) {
          $alreadyUsedColors[$cat][] = $data['item']['color'];
        }
      }
    }

    return [
      'ids'    => $excludeItemIds,
      'colors' => $alreadyUsedColors,
    ];
  }
}
