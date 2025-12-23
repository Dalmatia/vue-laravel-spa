<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\AiClient;
use App\Domain\ClothingAdvice\PromptBuilder;
use App\Models\User;
use Illuminate\Support\Facades\Log;

final class AiAdviceCoordinator
{
  public function __construct(
    private PromptBuilder $promptBuilder,
    private AiClient $aiClient,
    private OutfitSuggestionBuilder $outfitBuilder,
    private ExclusionConfigBuilder $exclusionBuilder,
  ) {}

  public function generate(array $weather, User $user, ?string $tpo, string $date, ?string $cityId): array
  {
    $json = null;

    try {
      $prompt = $this->promptBuilder->buildJson($weather, $user, $tpo);
      $json = $this->aiClient->getClothingAdviceJson($prompt);
    } catch (\Throwable $e) {
      Log::warning('AI unavailable', [
        'user_id' => $user->id,
        'error' => $e->getMessage(),
      ]);
    }

    $exclude = $this->exclusionBuilder->exclusionConfig($user->id, $date, $tpo, $cityId, $user->profile_hash);

    $items = $this->outfitBuilder->outfitSuggestion(
      $json['items'] ?? [],
      $user->id,
      $exclude,
      $tpo,
      $date
    );

    $adviceText = $json
      ? ($json['summary'] ?? '本日の服装アドバイスです。')
      : 'AI提案が利用できなかったため、手持ちアイテムから組み合わせを提案しました。';

    return [$adviceText, $items, $json !== null];
  }
}
