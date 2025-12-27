<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\AiClient;
use App\Domain\ClothingAdvice\OuterPolicy;
use App\Domain\ClothingAdvice\OuterPolicyResolver;
use App\Domain\ClothingAdvice\PromptBuilder;
use App\Domain\Weather\ThermalLevelResolver;
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

    $baseSummary = $json
      ? ($json['summary'] ?? '本日の服装アドバイスです。')
      : 'AI提案が利用できなかったため、手持ちアイテムから組み合わせを提案しました。';

    $feelsLike = $weather['feels_like'] ?? null;

    if ($feelsLike === null) {
      Log::warning('feels_like missing, fallback used', ['weather' => $weather]);
      $feelsLike = $weather['max_temp'] ?? 20.0;
    }

    [$items, $outerPolicy] = $this->outfitBuilder->outfitSuggestion(
      $json['items'] ?? [],
      $user->id,
      $exclude,
      $tpo,
      $date,
      $feelsLike
    );

    $adviceText = $baseSummary . ' ' . match ($outerPolicy) {
      OuterPolicy::REQUIRED =>
      '体感温度が低いため、防寒を意識してアウターを含めた服装にしています。',
      OuterPolicy::OPTIONAL =>
      '体感温度を考慮し、軽めのアウターを想定した服装にしています。',
      OuterPolicy::AVOID =>
      '体感温度が高いため、アウターなしでも快適な服装を提案しています。',
    };
    return [$adviceText, $items, $json !== null];
  }
}
