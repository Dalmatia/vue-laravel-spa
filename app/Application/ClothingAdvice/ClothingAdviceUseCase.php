<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\AdviceCache;
use App\Models\User;

final class ClothingAdviceUseCase
{
  public function __construct(
    private AiAdviceCoordinator $aiCoordinator,
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

    $items = $this->normalizeOutfitReasonsForUi($items);
    $items = $this->translateOutfitReasons($items);

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

  private function normalizeOutfitReasonsForUi(array $items): array
  {
    foreach ($items as $category => &$entry) {
      if (!isset($entry['alternatives'])) {
        continue;
      }

      foreach ($entry['alternatives'] as &$alt) {
        if (!empty($alt['reasons'])) {
          $alt['reasons'] = OutfitReasonSelector::selectForUi(
            $alt['reasons']
          );
        }
      }
    }

    return $items;
  }

  private function translateOutfitReasons(array $items): array
  {
    foreach ($items as &$entry) {
      if (!isset($entry['alternatives'])) {
        continue;
      }

      foreach ($entry['alternatives'] as &$alt) {
        if (!isset($alt['reasons'])) {
          continue;
        }

        $alt['reasons'] = array_map(
          fn($reason) => OutfitReasonTranslator::toUiText($reason),
          $alt['reasons']
        );
      }
    }

    return $items;
  }
}
