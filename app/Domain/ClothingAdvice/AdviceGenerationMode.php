<?php

namespace App\Domain\ClothingAdvice;

enum AdviceGenerationMode: string
{
  case OUTFIT_BASED = 'outfit_based';
  case GENERAL_ADVICE = 'general_advice';
}
