<?php

namespace App\Domain\ClothingAdvice;

use App\Models\Item;
use App\Models\KeywordMapping;

class KeywordBasedCategoryMatcher implements CategoryMatcherStrategy
{
  public function __construct(
    private OutfitRuleEvaluator $ruleEvaluator,
    private SeasonResolver $seasonResolver
  ) {}

  public function match(
    int $userId,
    int $category,
    array $keywords,
    array $excludeItemIds,
    array $excludeColors,
    array $currentItems,
    ?string $tpo,
    ?string $targetDate,
  ): ItemMatchResult {
    $conditions = $this->buildConditionsFromKeywords($keywords);

    $query = Item::where('user_id', $userId)
      ->where('main_category', $category);

    if ($conditions['sub_categories']->isNotEmpty()) {
      $query->whereIn('sub_category', $conditions['sub_categories']);
    }

    if ($conditions['colors']->isNotEmpty()) {
      $query->whereIn('color', $conditions['colors']);
    }

    if ($conditions['styles']->isNotEmpty()) {
      $query->where(function ($q) use ($conditions) {
        foreach ($conditions['styles'] as $style) {
          $q->orWhere('memo', 'LIKE', "%{$style}%");
        }
      });
    }

    if (!empty($excludeItemIds)) {
      $query->whereNotIn('id', $excludeItemIds);
    }

    if (!empty($excludeColors)) {
      $query->whereNotIn('color', $excludeColors);
    }

    $season = $this->seasonResolver->resolve($targetDate);
    $query->where(function ($q) use ($season) {
      $q->whereNull('season')
        ->orWhere('season', 0)
        ->orWhere('season', $season);
    });

    $candidates = $query->limit(5)->get();

    if ($candidates->isEmpty()) {
      return new ItemMatchResult(null);
    }

    $primary = null;
    $primaryEvaluation = null;
    $alternatives = [];

    foreach ($candidates as $item) {
      $evaluation = $this->ruleEvaluator->evaluateItem(
        $item,
        $currentItems,
        $tpo,
        $this->ruleEvaluator->getColorTolerance($tpo),
        $this->ruleEvaluator->getPatternAllowance($tpo)
      );

      if ($evaluation->canUse) {
        if ($primary === null) {
          $primary = $item;
          $primaryEvaluation = $evaluation;
        } else {
          $alternatives[] = [
            'item' => $item,
            'reasons' => [
              OutfitDecisionReason::BETTER_OPTION_SELECTED
            ],
          ];
        }
      }
    }
    return new ItemMatchResult($primary, $primaryEvaluation, $alternatives);
  }

  private function buildConditionsFromKeywords(array $keywords): array
  {
    $mappings = KeywordMapping::where(function ($q) use ($keywords) {
      foreach ($keywords as $kw) {
        $q->orWhere('keyword', 'LIKE', "%{$kw}%");
      }
    })->get();

    return [
      'sub_categories' => $mappings->pluck('sub_category')->filter()->unique(),
      'colors'         => $mappings->pluck('color')->filter()->unique(),
      'styles'         => $mappings->pluck('style')->filter()->unique(),
    ];
  }
}
