<?php

namespace App\Application\Outfit;

use App\Application\Outfit\Dto\RelatedOutfitDto;
use App\Models\Outfit;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RelatedOutfitService
{
  private const STRICT_MATCH_THRESHOLD = 3;
  private const STRICT_MATCH_COUNT = 2;
  private const FALLBACK_MATCH_COUNT = 1;

  public function getBySubCategories(
    int $viewerUserId,
    array $subCategoryIds,
    ?int $season,
    string $tempBand,
    CarbonImmutable $baseDate,
    int $limit = 5
  ): Collection {
    if (empty($subCategoryIds)) {
      return collect();
    }

    $query = $this->buildBaseQuery(
      $viewerUserId,
      $subCategoryIds,
      $season,
      $tempBand,
      $baseDate
    );

    $outfits = $this->findRelatedOutfits($query, $subCategoryIds, $limit);

    return $outfits->map(fn($outfit) => RelatedOutfitDto::fromModel($outfit));
  }

  private function buildBaseQuery(
    int $viewerUserId,
    array $subCategoryIds,
    ?int $season,
    string $tempBand,
    CarbonImmutable $baseDate
  ): Builder {
    return Outfit::query()
      ->with(['user', 'items'])
      ->withCount([
        'likes as likes_count' => fn($q) =>
        $q->where('like', 1),

        'items as matching_sub_category_count' =>
        fn($q) => $q->whereIn(
          'sub_category',
          $subCategoryIds
        ),
      ])
      ->excludeUser($viewerUserId)
      ->usesSubCategories($subCategoryIds)
      ->preferSeason($season, $baseDate)
      ->preferTemperatureBand($tempBand);
  }

  private function findRelatedOutfits($query, array $subCategoryIds, int $limit): Collection
  {
    $requiredMatchCount = count($subCategoryIds) >= self::STRICT_MATCH_THRESHOLD
      ? self::STRICT_MATCH_COUNT
      : self::FALLBACK_MATCH_COUNT;

    $outfits = (clone $query)
      ->having(
        'matching_sub_category_count',
        '>=',
        $requiredMatchCount
      )
      ->orderByDesc('matching_sub_category_count')
      ->orderByDesc('likes_count')
      ->limit($limit)
      ->get();

    if ($outfits->count() >= $limit) {
      return $outfits;
    }

    return (clone $query)
      ->orderByDesc('matching_sub_category_count')
      ->orderByDesc('likes_count')
      ->limit($limit)
      ->get();
  }
}
