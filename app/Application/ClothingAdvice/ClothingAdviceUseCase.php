<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\AdviceCache;
use App\Models\User;

final class ClothingAdviceUseCase
{
  public function __construct(
    private AiAdviceCoordinator $aiCoordinator,
    private OutfitSuggestionBuilder $outfitBuilder,
    private AdviceResultPersister $persister,
    private AdviceCache $adviceCache,
  ) {}

  public function handle(array $weatherData, int $userId, ?string $date, ?string $tpo, ?string $cityId): array
  {
    $date ??= now()->toDateString();
    $user = User::findOrFail($userId);

    if ($cached = $this->adviceCache->get(
      $userId,
      $date,
      $tpo,
      $cityId,
      $user->profile_hash
    )) {
      return $cached;
    }

    [$adviceText, $items, $isAiAvailable] =
      $this->aiCoordinator->generate($weatherData, $user, $tpo, $date, $cityId);

    $result = [
      'category' => $isAiAvailable ? 'AIによる提案' : '手持ちアイテムからの提案',
      'advice' => $adviceText,
      'outfit_suggestion' => $items,
    ];

    $this->persister->storeResult(
      $user->id,
      $date,
      $result,
      $items,
      $tpo,
      $cityId,
      $user->profile_hash,
      $isAiAvailable
    );

    return $result;
  }
}
