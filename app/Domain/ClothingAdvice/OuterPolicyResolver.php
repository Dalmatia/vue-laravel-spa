<?php

namespace App\Domain\ClothingAdvice;

use App\Domain\Weather\ThermalLevel;

class OuterPolicyResolver
{
  public function resolve(ThermalLevel $level): OuterPolicy
  {
    return match ($level) {
      ThermalLevel::COLD => OuterPolicy::REQUIRED,
      ThermalLevel::COOL => OuterPolicy::OPTIONAL,
      ThermalLevel::MILD => OuterPolicy::OPTIONAL,
      ThermalLevel::WARM => OuterPolicy::AVOID,
    };
  }
}
