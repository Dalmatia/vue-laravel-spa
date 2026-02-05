<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\AdviceGenerationMode;
use App\Domain\ClothingAdvice\AiClient;
use App\Domain\ClothingAdvice\OuterPolicy;
use App\Domain\ClothingAdvice\PromptBuilder;
use App\Domain\Weather\WeatherDto;
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

  public function generate(WeatherDto $weather, User $user, ?string $tpo, string $date, ?string $cityId): array
  {
    $json = null;

    try {
      $prompt = $this->promptBuilder->buildJson($weather, $user, $tpo);
      $json = $this->aiClient->getClothingAdviceJson($prompt);

      if (!$this->isValidAiResponse($json)) {
        throw new \RuntimeException('Invalid AI response structure');
      }
    } catch (\Throwable $e) {
      Log::warning('AI unavailable', [
        'user_id' => $user->id,
        'error' => $e->getMessage(),
      ]);
    }

    $exclude = $this->exclusionBuilder->exclusionConfig($user->id, $date, $tpo, $cityId, $user->profile_hash);

    $baseSummary = $json
      ? ($json['summary'] ?? '本日の服装アドバイスです。')
      : 'AI提案が利用できなかったため、手持ちアイテムから組み合わせを提案しました。';

    $feelsLike = $weather->feelsLike();

    [$items, $outerPolicy] = $this->outfitBuilder->outfitSuggestion(
      $json['items'] ?? [],
      $user->id,
      $exclude,
      $tpo,
      $date,
      $feelsLike
    );

    $notes = $json['notes'] ?? [];

    $mode = $this->outfitBuilder->hasEnoughItems($items)
      ? AdviceGenerationMode::OUTFIT_BASED
      : AdviceGenerationMode::GENERAL_ADVICE;

    $outerSentence = match ($mode) {
      AdviceGenerationMode::OUTFIT_BASED => match ($outerPolicy) {
        OuterPolicy::REQUIRED =>
        '体感温度が低いため、防寒を意識してアウターを含めた服装にしています。',
        OuterPolicy::OPTIONAL =>
        '体感温度を考慮し、軽めのアウターを想定した服装にしています。',
        OuterPolicy::AVOID =>
        '体感温度が高いため、アウターなしでも快適な服装を提案しています。',
      },

      AdviceGenerationMode::GENERAL_ADVICE =>
      '気温や天候を踏まえた一般的な服装のポイントをお伝えしています。',
    };

    $adviceText = $baseSummary . ' ' . $outerSentence;

    return [
      'advice' => $adviceText,
      'items' => $items,
      'notes' => $notes,
      'mode' => $mode->value,
      'ai_used' => $json !== null,
    ];
  }

  private function isValidAiResponse(array $json): bool
  {
    if (!isset($json['items']) || !is_array($json['items'])) {
      return false;
    }

    return true;
  }
}
