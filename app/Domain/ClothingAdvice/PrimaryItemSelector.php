<?php

namespace App\Domain\ClothingAdvice;

use App\Domain\ClothingAdvice\ItemMatchResult;

final class PrimaryItemSelector
{
  private const TIE_THRESHOLD = 0.5;

  public function select(array $evaluatedItems): ItemMatchResult
  {
    if (empty($evaluatedItems)) {
      return new ItemMatchResult(null);
    }

    $usable = array_filter(
      $evaluatedItems,
      fn($e) => $e['evaluation']?->canUse === true
    );

    if (empty($usable)) {
      return new ItemMatchResult(null);
    }

    // score 未定義対策
    $scores = array_map(
      fn($e) => $e['evaluation']->score ?? 0,
      $usable
    );

    $maxScore = max($scores);

    $pool = array_filter(
      $usable,
      fn($e) => ($e['evaluation']->score ?? 0) >= $maxScore - self::TIE_THRESHOLD
    );

    if (empty($pool)) {
      return new ItemMatchResult(null);
    }

    $primaryEntry = $pool[array_rand($pool)];

    $alternatives = array_values(array_filter(
      $usable,
      fn($e) => $e !== $primaryEntry
    ));

    return new ItemMatchResult(
      primary: $primaryEntry['item'],
      primaryEvaluation: $primaryEntry['evaluation'],
      alternatives: $alternatives,
    );
  }
}
