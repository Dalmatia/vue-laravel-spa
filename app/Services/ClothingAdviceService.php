<?php

namespace App\Services;

use App\Application\ClothingAdvice\ClothingAdviceUseCase;

class ClothingAdviceService
{
  public function __construct(
    private ClothingAdviceUseCase $useCase
  ) {}

  public function suggestClothing(array $weatherData, int $userId, ?string $targetDate = null, ?string $tpo = null, ?string $cityId = null): array
  {
    return $this->useCase->handle(
      $weatherData,
      $userId,
      $targetDate,
      $tpo,
      $cityId
    );
  }
}
