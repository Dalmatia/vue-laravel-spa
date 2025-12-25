<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\Season;
use Carbon\Carbon;

class SeasonResolver
{
  public function resolve(?string $date = null): int
  {
    $month = Carbon::parse($date ?? now())->month;

    return match (true) {
      $month >= 3 && $month <= 5 => Season::spring,
      $month >= 6 && $month <= 8 => Season::summer,
      $month >= 9 && $month <= 11 => Season::fall,
      default => Season::winter,
    };
  }
}
