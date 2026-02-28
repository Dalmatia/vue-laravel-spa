<?php

namespace App\Domain\ClothingAdvice;

use App\Domain\Weather\WeatherDto;

interface CategoryMatcherStrategy
{
  public function evaluateCandidates(
    int $userId,
    int $category,
    array $keywords,
    array $excludeItemIds,
    array $excludeColors,
    array $currentItems,
    WeatherDto $weatherDto,
    ?string $tpo,
    ?string $targetDate
  ): array;
}
