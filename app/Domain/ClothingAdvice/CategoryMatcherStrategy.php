<?php

namespace App\Domain\ClothingAdvice;

use App\Models\Item;

interface CategoryMatcherStrategy
{
  public function match(
    int $userId,
    int $category,
    array $keywords,
    array $excludeItemIds,
    array $excludeColors,
    array $currentItems,
    ?string $tpo,
    ?string $targetDate,
    OuterPolicy $outerPolicy
  ): ?Item;
}
