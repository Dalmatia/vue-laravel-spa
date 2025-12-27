<?php

namespace App\Domain\Weather;

enum ThermalLevel: string
{
  case COLD = 'cold';
  case COOL = 'cool';
  case MILD = 'mild';
  case WARM = 'warm';
}
