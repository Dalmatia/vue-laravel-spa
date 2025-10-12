<?php

namespace App\Services;

use App\Domain\ClothingAdvice\PromptBuilder;
use App\Domain\ClothingAdvice\AiClient;
use App\Domain\ClothingAdvice\ItemMatcher;
use App\Domain\ClothingAdvice\AdviceCache;
use App\Enums\MainCategory;
use App\Models\User;
use Carbon\Carbon;

class ClothingAdviceService
{
  public function __construct(
    private PromptBuilder $promptBuilder,
    private AiClient $aiClient,
    private ItemMatcher $itemMatcher,
    private AdviceCache $adviceCache
  ) {}

  public function suggestClothing(array $weatherData, int $userId, ?string $targetDate = null, ?string $tpo = null): array
  {
    $date = $targetDate ?? Carbon::today()->toDateString();
    $cached = $this->adviceCache->get($userId, $date . "_{$tpo}");
    // すでにキャッシュされていれば返す
    if ($cached) {
      return $cached;
    }

    $user = User::find($userId);
    $text = $this->generateAdvice($weatherData, $user, $tpo);
    $excludeConfig = $this->buildExclusionConfig($userId);

    $matchedItems = $this->itemMatcher->matchItems(
      $text,
      $userId,
      $excludeConfig['colors'],
      $excludeConfig['ids']
    );

    // キャッシュに保存（期限は1日）
    $result = [
      'advice' => $text,
      'category' => 'AIによる提案',
      'outfit_suggestion' => $matchedItems,
    ];

    $this->storeResult($userId, $date . "_{$tpo}", $result, $matchedItems);

    return $result;
  }

  private function generateAdvice(array $weatherData, User $user, ?string $tpo = null): string
  {
    $prompt = $this->promptBuilder->build($weatherData, $user, $tpo);
    return $this->aiClient->getClothingAdvice($prompt);
  }

  /**
   * 前日着用アイテムの除外条件を構築
   */
  private function buildExclusionConfig(int $userId): array
  {
    $yesterdayKey = Carbon::yesterday()->toDateString();

    $excludeItemIds = $this->adviceCache->getUsedItems($userId, $yesterdayKey);
    $alreadyUsedColors = [
      MainCategory::outer => [],
      MainCategory::tops => [],
      MainCategory::bottoms => [],
      MainCategory::shoes => [],
    ];

    $yesterday = $this->adviceCache->get($userId, $yesterdayKey);
    if ($yesterday && isset($yesterday['outfit_suggestion'])) {
      foreach ($yesterday['outfit_suggestion'] as $cat => $data) {
        if (!empty($data['item']['color'])) {
          $alreadyUsedColors[$cat][] = $data['item']['color'];
        }
      }
    }

    return [
      'ids'    => $excludeItemIds,
      'colors' => $alreadyUsedColors,
    ];
  }

  /**
   * キャッシュ & 使用アイテムを保存
   */
  private function storeResult(int $userId, string $date, array $result, array $matchedItems, ?string $tpo = null): void
  {
    $this->adviceCache->put($userId, $date, $result, $tpo);

    $usedItemIds = array_filter(
      array_map(fn($d) => $d['item']['id'] ?? null, $matchedItems)
    );

    $this->adviceCache->putUsedItems($userId, $date, $usedItemIds);
  }
}
