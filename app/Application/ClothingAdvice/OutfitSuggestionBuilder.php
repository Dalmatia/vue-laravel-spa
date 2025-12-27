<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\FallbackOutfitBuilder;
use App\Domain\ClothingAdvice\ItemMatcher;
use App\Domain\ClothingAdvice\OuterPolicyResolver;
use App\Domain\Weather\ThermalLevelResolver;

final class OutfitSuggestionBuilder
{
  public function __construct(
    private ItemMatcher $matcher,
    private FallbackOutfitBuilder $fallback,
    private OuterPolicyResolver $outerPolicyResolver,
    private ThermalLevelResolver $thermalLevelResolver
  ) {}

  public function outfitSuggestion(array $items, int $userId, array $exclude, ?string $tpo, string $date, float $feelsLike): array
  {
    $thermalLevel = $this->thermalLevelResolver->resolve($feelsLike);
    $outerPolicy  = $this->outerPolicyResolver->resolve($thermalLevel, $tpo);

    $matched = $this->matcher->matchItemsFromJson(
      $items,
      $userId,
      $exclude['colors'],
      $exclude['ids'],
      $outerPolicy,
      $tpo,
      $date
    );

    $filled = $this->fallback->fillMissingItems(
      $matched,
      $userId,
      $outerPolicy,
      $tpo,
      $date
    );

    return [$filled, $outerPolicy];
  }
}
