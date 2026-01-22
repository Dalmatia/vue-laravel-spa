<?php

namespace App\Domain\ClothingAdvice;

use App\Application\ClothingAdvice\OutfitReasonSelector;
use App\Enums\MainCategory;
use App\Domain\ClothingAdvice\PrimaryItemSelector;

class ItemMatcher
{
  public function __construct(
    private CategoryMatcherStrategy $categoryMatcher,
    private PrimaryItemSelector $primaryItemSelector,
  ) {}

  public function matchItemsFromJson(
    array $itemsByCategory,
    int $userId,
    array $excludeColorsByCategory = [],
    array $excludeItemIds = [],
    OuterPolicy $outerPolicy,
    ?string $tpo = null,
    ?string $targetDate = null
  ): array {
    $matchedItems = [
      MainCategory::outer   => null,
      MainCategory::tops    => null,
      MainCategory::bottoms => null,
      MainCategory::shoes   => null,
    ];

    foreach ($itemsByCategory as $categoryKey => $keywords) {
      $category = $this->resolveMainCategory($categoryKey);
      if ($category === null || $matchedItems[$category] !== null) {
        continue;
      }

      if (
        $category === MainCategory::outer &&
        $outerPolicy === OuterPolicy::AVOID
      ) {
        continue;
      }

      $evaluatedItems = $this->categoryMatcher->evaluateCandidates(
        $userId,
        $category,
        $keywords,
        $excludeItemIds,
        $excludeColorsByCategory[$category] ?? [],
        $matchedItems,
        $tpo,
        $targetDate,
      );

      $result = $this->primaryItemSelector->select($evaluatedItems);

      if ($result->primary) {
        $matchedItems[$category] = [
          'item' => $result->primary,
          'primaryReasons' => OutfitReasonSelector::selectForPrimary($result->primaryEvaluation->reasons),
          'alternatives' => [
            [
              'item' => null,
              'reasons' => [
                OutfitDecisionReason::BETTER_OPTION_SELECTED,
              ],
            ],
          ],
          'source' => 'json',
        ];
      }
    }

    return $matchedItems;
  }

  private function resolveMainCategory(string $key): ?int
  {
    return match ($key) {
      'outer'   => MainCategory::outer,
      'tops'    => MainCategory::tops,
      'bottoms' => MainCategory::bottoms,
      'shoes'   => MainCategory::shoes,
      default   => null,
    };
  }
}
