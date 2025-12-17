<?php

namespace App\Services;

use App\Domain\ClothingAdvice\PromptBuilder;
use App\Domain\ClothingAdvice\AiClient;
use App\Domain\ClothingAdvice\ItemMatcher;
use App\Domain\ClothingAdvice\AdviceCache;
use App\Domain\ClothingAdvice\FallbackOutfitBuilder;
use App\Enums\MainCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClothingAdviceService
{
  public function __construct(
    private PromptBuilder $promptBuilder,
    private AiClient $aiClient,
    private FallbackOutfitBuilder $fallbackOutfitBuilder,
    private ItemMatcher $itemMatcher,
    private AdviceCache $adviceCache
  ) {}

  public function suggestClothing(array $weatherData, int $userId, ?string $targetDate = null, ?string $tpo = null, ?string $cityId = null): array
  {
    $date = $targetDate ?? Carbon::today()->toDateString();

    $user = User::findOrFail($userId);
    $profileHash = $user->profile_hash;

    $cached = $this->adviceCache->get($userId, $date, $tpo, $cityId, $profileHash);
    // すでにキャッシュされていれば返す
    if ($cached) {
      return $cached;
    }

    $text = $this->generateAiResponse($weatherData, $user, $tpo);
    $excludeConfig = $this->buildExclusionConfig($userId, $date, $cityId);

    $matchedItems = $this->itemMatcher->matchItems(
      $text,
      $userId,
      $excludeConfig['colors'],
      $excludeConfig['ids'],
      $tpo,
      $date
    );

    $before = array_map(
      fn($v) => empty($v['item'] ?? null),
      $matchedItems
    );

    $matchedItems = $this->fallbackOutfitBuilder->fillMissingItems(
      $matchedItems,
      $userId,
      $tpo,
      $date
    );

    $after = array_map(
      fn($v) => empty($v['item'] ?? null),
      $matchedItems
    );

    Log::info('FALLBACK CHECK', [
      'before' => $before,
      'after'  => $after,
    ]);

    $adviceText = $text
      ?? '本日はAI提案が利用できなかったため、手持ちアイテムから最適な組み合わせを提案しました。';

    $category = $text === null
      ? '手持ちアイテムからの提案'
      : 'AIによる提案';

    // キャッシュに保存（期限は1日）
    $result = [
      'advice' => $adviceText,
      'category' => $category,
      'outfit_suggestion' => $matchedItems,
    ];

    $isAiAvailable = $text !== null;
    $this->storeResult($userId, $date, $result, $matchedItems, $tpo, $cityId, $profileHash, $isAiAvailable);

    return $result;
  }

  private function generateAiResponse(array $weatherData, User $user, ?string $tpo = null): ?string
  {
    $prompt = $this->promptBuilder->build($weatherData, $user, $tpo);
    try {
      $reply = $this->aiClient->getClothingAdvice($prompt);
      if (!is_string($reply) || trim($reply) === '') {
        // 空応答は例外扱いしてフォールバック
        throw new \RuntimeException('AI returned empty response');
      }
      return $reply;
    } catch (\Throwable $e) {
      Log::warning('Gemini unavailable, fallback only', [
        'user_id' => $user->id,
        'tpo' => $tpo,
        'error' => $e->getMessage(),
      ]);

      return null;
    }
  }

  public function suggestClothingJson(array $weatherData, int $userId, ?string $targetDate = null, ?string $tpo = null, ?string $cityId = null): array
  {
    $date = $targetDate ?? now()->toDateString();
    $user = User::findOrFail($userId);
    $profileHash = $user->profile_hash;

    $cached = $this->adviceCache->get($userId, $date, $tpo, $cityId, $profileHash);
    // すでにキャッシュされていれば返す
    if ($cached) {
      return $cached;
    }

    $prompt = $this->promptBuilder->buildJson($weatherData, $user, $tpo);
    try {
      $json = $this->aiClient->getClothingAdviceJson($prompt);
    } catch (\Throwable $e) {
      Log::warning('Gemini unavailable, fallback only', [
        'user_id' => $user->id,
        'tpo' => $tpo,
        'error' => $e->getMessage(),
      ]);

      $json = null;
    }

    $excludeConfig = $this->buildExclusionConfig($userId, $date, $cityId);

    $matchedItems = $this->itemMatcher->matchItemsFromJson(
      $json['items'] ?? [],
      $userId,
      $excludeConfig['colors'],
      $excludeConfig['ids'],
      $tpo,
      $date
    );

    $before = array_map(
      fn($v) => empty($v['item'] ?? null),
      $matchedItems
    );

    $matchedItems = $this->fallbackOutfitBuilder->fillMissingItems(
      $matchedItems,
      $userId,
      $tpo,
      $date
    );

    $after = array_map(
      fn($v) => empty($v['item'] ?? null),
      $matchedItems
    );

    Log::info('FALLBACK CHECK', [
      'before' => $before,
      'after'  => $after,
    ]);

    $isAiAvailable = $json !== null;

    $adviceText = $isAiAvailable
      ? ($json['summary'] ?? '本日の服装アドバイスです。')
      : 'AI提案が利用できなかったため、手持ちアイテムから組み合わせを提案しました。';

    $result = [
      'category' => $isAiAvailable ? 'AIによる提案' : '手持ちアイテムからの提案',
      'advice' => $adviceText,
      'outfit_suggestion' => $matchedItems,
    ];

    $this->storeResult(
      $userId,
      $date,
      $result,
      $matchedItems,
      $tpo,
      $cityId,
      $profileHash,
      $isAiAvailable
    );

    return $result;
  }

  /**
   * 前日着用アイテムの除外条件を構築
   */
  private function buildExclusionConfig(int $userId, string $date, ?string $cityId = null): array
  {
    $yesterdayKey = Carbon::parse($date)->subDay()->toDateString();

    $excludeItemIds = $this->adviceCache->getUsedItems($userId, $yesterdayKey, $cityId);
    // getUsedItems がカテゴリ別に返す場合は統合して id の平坦配列にする
    if (!empty($excludeItemIds) && is_array($excludeItemIds)) {
      $flat = [];
      array_walk_recursive($excludeItemIds, function ($v) use (&$flat) {
        $flat[] = $v;
      });
      $excludeItemIds = array_values(array_filter(array_unique($flat)));
    }

    $alreadyUsedColors = [
      MainCategory::outer => [],
      MainCategory::tops => [],
      MainCategory::bottoms => [],
      MainCategory::shoes => [],
    ];

    $yesterday = $this->adviceCache->get($userId, $yesterdayKey, null, $cityId) ?? [];
    if (!empty($yesterday['outfit_suggestion'])) {
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
  private function storeResult(int $userId, string $date, array $result, array $matchedItems, ?string $tpo = null, ?string $cityId = null, ?string $profileHash = null, bool $isAiAvailable = true): void
  {
    $ttl = $isAiAvailable
      ? now()->addDay()
      : now()->addMinutes(10);
    $this->adviceCache->put($userId, $date, $result, $tpo, $cityId, $profileHash, $ttl);

    $usedItemIds = [];
    foreach ($matchedItems as $cat => $data) {
      $item = $data['item'] ?? null;

      if (is_object($item) && isset($item->id)) {
        $usedItemIds[] = $item->id;
      } elseif (is_array($item) && isset($item['id'])) {
        $usedItemIds[] = $item['id'];
      }
    }

    if (empty($usedItemIds)) {
      Log::info("USED ITEMS NOT SAVED (no match)", [
        'date' => $date,
        'matchedItems' => $matchedItems,
      ]);
      return;
    }

    $this->adviceCache->putUsedItems($userId, $date, $usedItemIds, $cityId);
    Log::info("USED ITEMS SAVED", [
      'date' => $date,
      'usedItemIds' => $usedItemIds,
    ]);
  }
}
