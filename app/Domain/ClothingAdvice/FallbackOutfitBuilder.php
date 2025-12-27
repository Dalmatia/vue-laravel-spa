<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\MainCategory;
use App\Models\Item;

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
    if (
      $outerPolicy === OuterPolicy::REQUIRED &&
      empty($matchedItems[MainCategory::outer]['item'] ?? null)
    ) {
      $outer = $this->findBestCandidate(
        $matchedItems,
        $userId,
        MainCategory::outer,
        $tpo,
        $targetDate
      );

      if ($outer) {
        $matchedItems[MainCategory::outer] = [
          'source' => 'fallback',
          'item'   => $outer,
        ];
      }
    }

    foreach ($matchedItems as $categoryValue => $data) {
      if (!empty($data['item'])) {
        continue;
      }

      $filled = $this->findBestCandidate(
        $matchedItems,
        $userId,
        $categoryValue,
        $tpo,
        $targetDate
      );

      if ($filled) {
        $matchedItems[$categoryValue] = [
          'source' => 'fallback',
          'item'   => $filled,
        ];
      }
    }

    return $matchedItems;
  }

  /**
   * 指定カテゴリの最適候補を探す
   */
  private function findBestCandidate(array $currentItems, int $userId, int $category, ?string $tpo, ?string $targetDate): ?Item
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

    $candidates = $query->get()->shuffle();

    foreach ($candidates as $candidate) {
      if ($this->ruleEvaluator->canUseItem(
        $candidate,
        $currentItems,
        $tpo,
        $this->ruleEvaluator->getColorTolerance($tpo),
        $this->ruleEvaluator->getPatternAllowance($tpo)
      )) {
        return $candidate;
      }
    }

    return null;
  }
}
