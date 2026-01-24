<?php

namespace App\Domain\ClothingAdvice;

use App\Application\ClothingAdvice\OutfitReasonSelector;
use App\Domain\ClothingAdvice\PrimaryItemSelector;
use App\Enums\MainCategory;
use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

class FallbackOutfitBuilder
{
  public function __construct(
    private OutfitRuleEvaluator $ruleEvaluator,
    private SeasonResolver $seasonResolver,
    private PrimaryItemSelector $primaryItemSelector,
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
          'primaryReasons' => OutfitReasonSelector::selectForPrimary($result->primaryEvaluation?->reasons ?? []),
          'alternatives' => [
            [
              'item' => null,
              'reasons' => [
                OutfitDecisionReason::BETTER_OPTION_SELECTED,
              ],
            ],
          ],
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

    $evaluatedItems = [];

    foreach ($candidates as $candidate) {
      $evaluation = $this->ruleEvaluator->evaluateItem(
        $candidate,
        $currentItems,
        $tpo,
        $this->ruleEvaluator->getColorTolerance($tpo),
        $this->ruleEvaluator->getPatternAllowance($tpo)
      );

      $evaluatedItems[] = [
        'item' => $candidate,
        'evaluation' => $evaluation,
      ];
    }

    if (empty($evaluatedItems)) {
      return ItemMatchResult::noMatch(
        OutfitDecisionReason::NO_MATCH_FOUND
      );
    }

    return $this->primaryItemSelector->select($evaluatedItems);
  }
}
