<?php

namespace App\Domain\ClothingAdvice;

class ItemEvaluationResult
{
  public function __construct(
    public readonly bool $canUse,
    public readonly array $reasons = [],
    public readonly float $score,
  ) {}
}
