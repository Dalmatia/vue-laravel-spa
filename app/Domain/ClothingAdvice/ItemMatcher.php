<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\MainCategory;

class ItemMatcher
{
  public function __construct(
    private CategoryMatcherStrategy $categoryMatcher,
  ) {}

  public function matchItemsFromJson(array $itemsByCategory, int $userId, array $excludeColorsByCategory = [], array $excludeItemIds = [], ?string $tpo = null, ?string $targetDate = null): array
  {
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

      $item = $this->categoryMatcher->match(
        $userId,
        $category,
        $keywords,
        $excludeItemIds,
        $excludeColorsByCategory[$category] ?? [],
        $matchedItems,
        $tpo,
        $targetDate
      );

      if ($item) {
        $matchedItems[$category] = [
          'source' => 'json',
          'keywords' => $keywords,
          'item' => $item,
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
