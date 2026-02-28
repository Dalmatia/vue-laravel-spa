<?php

namespace App\Domain\ClothingAdvice;

enum OuterPolicy: string
{
  case REQUIRED = 'required';
  case OPTIONAL = 'optional';
  case AVOID    = 'avoid';
}
