<?php

namespace App\Domain\ClothingAdvice;

use App\Domain\ClothingAdvice\ItemMatchResult;

final class PrimaryItemSelector
{
  private const TIE_THRESHOLD = 0.5;

  public function select(array $evaluatedItems): ItemMatchResult
  {
    $usable = array_filter(
      $evaluatedItems,
      fn($e) => $e['evaluation']->canUse
    );

    if (empty($usable)) {
      return new ItemMatchResult(null, null, []);
    }

    $maxScore = max(
      array_map(fn($e) => $e['evaluation']->score, $usable)
    );

    $pool = array_filter(
      $usable,
      fn($e) =>
      $e['evaluation']->score >= $maxScore - self::TIE_THRESHOLD
    );

    $pool = array_values($pool);
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
