<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\MainCategory;
use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

class FallbackOutfitBuilder
{
  public function __construct(
    private OutfitRuleEvaluator $ruleEvaluator,
    private SeasonResolver $seasonResolver
  ) {}

  /**
   * null のカテゴリをユーザーの手持ちアイテムで補完する
   */
  public function fillMissingItems(array $matchedItems, int $userId, OuterPolicy $outerPolicy, ?string $tpo = null, ?string $targetDate = null): array
  {
    foreach ($matchedItems as $categoryValue => $data) {
      if (!empty($data['item'] ?? null)) {
        continue;
      }

      if (
        $categoryValue === MainCategory::outer &&
        $outerPolicy === OuterPolicy::AVOID
      ) {
        continue;
      }

      $result = $this->matchFallbackItem(
        $userId,
        $categoryValue,
        $matchedItems,
        $tpo,
        $targetDate
      );

      if ($result->primary) {
        $matchedItems[$categoryValue] = [
          'source' => 'fallback',
          'item'   => $result->primary,
          'alternatives' => $result->alternatives,
        ];
      }
    }

    return $matchedItems;
  }

  private function findCandidate(int $userId, int $category, ?string $targetDate): Collection
  {
    $query = Item::where('user_id', $userId)
      ->where('main_category', $category);

    // 季節フィルタ
    $season = $this->seasonResolver->resolve($targetDate);
    $query->where(function ($q) use ($season) {
      $q->whereNull('season')
        ->orWhere('season', 0)
        ->orWhere('season', $season);
    });

    return $query->get()->shuffle();
  }

  /**
   * 指定カテゴリの最適候補を探す
   */
  private function matchFallbackItem(int $userId, int $category, array $currentItems, ?string $tpo, ?string $targetDate): ItemMatchResult
  {
    $candidates = $this->findCandidate($userId, $category, $targetDate);

    $primary = null;
    $alternatives = [];

    foreach ($candidates as $candidate) {
      if ($this->ruleEvaluator->canUseItem(
        $candidate,
        $currentItems,
        $tpo,
        $this->ruleEvaluator->getColorTolerance($tpo),
        $this->ruleEvaluator->getPatternAllowance($tpo)
      )) {
        if ($primary === null) {
          $primary = $candidate;
        } else {
          $alternatives[] = $candidate;
        }
      }
    }

    return new ItemMatchResult($primary, $alternatives);
  }
}
