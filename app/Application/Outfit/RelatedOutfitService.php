<?php

namespace App\Application\Outfit;

use App\Application\Outfit\Dto\RelatedOutfitDto;
use App\Models\Outfit;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class RelatedOutfitService
{
  /**
   * 提案カテゴリを使っている人気コーデを取得
   *
   * @param int   $viewerUserId 表示対象ユーザー（自分の投稿除外用）
   * @param array $categories ['tops', 'outer', ...]
   * @param int $limit
   * @return Collection
   */
  public function getByCategories(int $viewerUserId, array $categories, ?int $season, string $tempBand, CarbonImmutable $baseDate, int $limit = 5): Collection
  {
    if (empty($categories)) {
      return collect();
    }

    return Outfit::query()
      ->withCount([
        'likes as likes_count' => fn($q) => $q->where('like', 1)
      ])
      ->excludeUser($viewerUserId)
      ->usesCategories($categories)
      ->preferSeason($season, $baseDate)
      ->preferTemperatureBand($tempBand)

      ->orderByDesc('likes_count')
      ->inRandomOrder()

      ->with(['user'])

      ->limit($limit)
      ->get()
      ->map(fn($outfit) => RelatedOutfitDto::fromModel($outfit));
  }
}
