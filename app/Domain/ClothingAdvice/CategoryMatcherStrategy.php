<?php

namespace App\Domain\ClothingAdvice;

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
  ): ItemMatchResult;
}
