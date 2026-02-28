<?php

namespace App\Domain\Weather;

enum ThermalLevel: string
{
  case COLD = 'cold';
  case COOL = 'cool';
  case MILD = 'mild';
  case WARM = 'warm';

  public function toIndex(): int
  {
    return match ($this) {
      self::COLD => 0,
      self::COOL => 1,
      self::MILD => 2,
      self::WARM => 3,
    };
  }
}
