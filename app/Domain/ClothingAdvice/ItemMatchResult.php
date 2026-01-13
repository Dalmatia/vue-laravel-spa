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
}
