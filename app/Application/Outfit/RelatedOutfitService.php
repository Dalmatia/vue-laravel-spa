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
  public function getByCategories(int $viewerUserId, array $categories, ?int $season, CarbonImmutable $baseDate, int $limit = 5): Collection
  {
    if (empty($categories)) {
      return collect();
    }

    return Outfit::query()
      ->excludeUser($viewerUserId)
      ->usesCategories($categories)
      ->preferSeason($season, $baseDate)
      ->with(['user'])
      ->withCount('likes')
      ->limit($limit)
      ->get()
      ->map(fn($outfit) => RelatedOutfitDto::fromModel($outfit));
  }
}
