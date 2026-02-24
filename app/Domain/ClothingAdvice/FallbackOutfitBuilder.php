<?php

namespace App\Domain\ClothingAdvice;

use App\Application\ClothingAdvice\OutfitReasonSelector;
use App\Domain\ClothingAdvice\PrimaryItemSelector;
use App\Domain\Weather\ThermalLevelResolver;
use App\Domain\Weather\WeatherDto;
use App\Enums\MainCategory;
use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;

class FallbackOutfitBuilder
{
  public function __construct(
    private OutfitRuleEvaluator $ruleEvaluator,
    private PrimaryItemSelector $primaryItemSelector,
    private ThermalLevelResolver $thermalLevelResolver
  ) {}

  /**
   * null のカテゴリをユーザーの手持ちアイテムで補完する
   */
  public function fillMissingItems(array $matchedItems, int $userId, OuterPolicy $outerPolicy, WeatherDto $weatherDto, ?string $tpo = null, ?string $targetDate = null): array
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
        $weatherDto
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

  private function findCandidate(int $userId, int $category, WeatherDto $weatherDto): Collection
  {
    $query = Item::where('user_id', $userId)
      ->where('main_category', $category);

    // 季節フィルタ
    $season = $weatherDto->thermalSeason();
    $query->where(function ($q) use ($season) {
      $q->whereNull('season')
        ->orWhere('season', 0)
        ->orWhere('season', $season);
    });

    return $query->inRandomOrder()->get();
  }

  /**
   * 指定カテゴリの最適候補を探す
   */
  private function matchFallbackItem(int $userId, int $category, array $currentItems, ?string $tpo, WeatherDto $weatherDto): ItemMatchResult
  {
    $candidates = $this->findCandidate($userId, $category, $weatherDto);
    $evaluatedItems = [];
    $thermalLevel = $this->thermalLevelResolver->resolve($weatherDto->feelsLike());

    foreach ($candidates as $candidate) {
      $evaluation = $this->ruleEvaluator->evaluateItem(
        $candidate,
        $currentItems,
        $tpo,
        $this->ruleEvaluator->getColorTolerance($tpo),
        $this->ruleEvaluator->getPatternAllowance($tpo),
        $thermalLevel
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
