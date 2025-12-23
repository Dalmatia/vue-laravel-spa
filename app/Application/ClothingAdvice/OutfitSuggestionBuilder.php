<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\FallbackOutfitBuilder;
use App\Domain\ClothingAdvice\ItemMatcher;

final class OutfitSuggestionBuilder
{
  public function __construct(
    private ItemMatcher $matcher,
    private FallbackOutfitBuilder $fallback,
  ) {}

  public function outfitSuggestion(array $items, int $userId, array $exclude, ?string $tpo, string $date): array
  {
    $matched = $this->matcher->matchItemsFromJson(
      $items,
      $userId,
      $exclude['colors'],
      $exclude['ids'],
      $tpo,
      $date
    );

    return $this->fallback->fillMissingItems(
      $matched,
      $userId,
      $tpo,
      $date
    );
  }
}
