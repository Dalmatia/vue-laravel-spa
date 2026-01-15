<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\AdviceCache;
use App\Domain\ClothingAdvice\OutfitDecisionReason;
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

    $items = $this->normalizeAndTranslateReasons($items);

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

  private function normalizeAndTranslateReasons(array $items): array
  {
    foreach ($items as &$entry) {
      // primary reasons（採用理由）
      if (!empty($entry['primaryReasons'])) {
        $entry['primaryReasons'] = array_map(
          fn(OutfitDecisionReason $r) => $r->label(),
          OutfitReasonSelector::selectForUi($entry['primaryReasons'])
        );
      }

      // alternative reasons（代替理由）
      if (!empty($entry['alternatives'])) {
        foreach ($entry['alternatives'] as &$alt) {
          if (empty($alt['reasons'])) {
            continue;
          }

          $alt['reasons'] = array_map(
            fn(OutfitDecisionReason $r) => $r->label(),
            OutfitReasonSelector::selectForUi($alt['reasons'])
          );
        }
      }
    }

    return $items;
  }
}
