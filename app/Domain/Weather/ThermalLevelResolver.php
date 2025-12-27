<?php

namespace App\Domain\Weather;

class ThermalLevelResolver
{
  public function resolve(float $feelsLike): ThermalLevel
  {
    return match (true) {
      $feelsLike <= 5   => ThermalLevel::COLD,
      $feelsLike <= 12  => ThermalLevel::COOL,
      $feelsLike <= 20  => ThermalLevel::MILD,
      default           => ThermalLevel::WARM,
    };
  }
}
