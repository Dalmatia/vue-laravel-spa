<?php

namespace App\Domain\ClothingAdvice;

use App\Models\Item;

class ItemMatchResult
{
  public function __construct(
    public readonly ?Item $primary,
    public readonly ?ItemEvaluationResult $primaryEvaluation = null,
    public readonly array $alternatives = []
  ) {}

  public function hasPrimary(): bool
  {
    return $this->primary !== null;
  }

  public static function noMatch(OutfitDecisionReason $reason): self
  {
    return new self(
      primary: null,
      primaryEvaluation: new ItemEvaluationResult(
        canUse: false,
        reasons: [$reason],
        score: 0.0,
      ),
      alternatives: [],
    );
  }
}
