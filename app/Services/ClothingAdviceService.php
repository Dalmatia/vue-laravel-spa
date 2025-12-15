<?php

namespace App\Services;

use App\Domain\ClothingAdvice\PromptBuilder;
use App\Domain\ClothingAdvice\AiClient;
use App\Domain\ClothingAdvice\ItemMatcher;
use App\Domain\ClothingAdvice\AdviceCache;
use App\Enums\MainCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClothingAdviceService
{
  public function __construct(
    private PromptBuilder $promptBuilder,
    private AiClient $aiClient,
    private ItemMatcher $itemMatcher,
    private AdviceCache $adviceCache
  ) {}

  public function suggestClothing(array $weatherData, int $userId, ?string $targetDate = null, ?string $tpo = null, ?string $cityId = null): array
  {
    $date = $targetDate ?? Carbon::today()->toDateString();

    $user = User::findOrFail($userId);
    $profileHash = $user->profile_hash;

    // $cached = $this->adviceCache->get($userId, $date, $tpo, $cityId, $profileHash);
    // // すでにキャッシュされていれば返す
    // if ($cached) {
    //   return $cached;
    // }

    $text = $this->generateAiResponse($weatherData, $user, $tpo);
    $excludeConfig = $this->buildExclusionConfig($userId, $cityId);

    $matchedItems = $this->itemMatcher->matchItems(
      $text,
      $userId,
      $excludeConfig['colors'],
      $excludeConfig['ids'],
      $tpo,
      $date
    );

    // キャッシュに保存（期限は1日）
    $result = [
      'advice' => $text,
      'category' => 'AIによる提案',
      'outfit_suggestion' => $matchedItems,
    ];

    $this->storeResult($userId, $date, $result, $matchedItems, $tpo, $cityId, $profileHash);

    return $result;
  }

  private function generateAiResponse(array $weatherData, User $user, ?string $tpo = null): string
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
      // ログに詳細を残す（コントローラでも記録しているがここでも）
      Log::error('AI服装アドバイス取得エラー: ' . $e->getMessage(), [
        'user_id' => $user->id,
        'tpo' => $tpo,
        'stack' => $e->getTraceAsString(),
      ]);

      // フォールバックメッセージ（短め）
      return '服装アドバイスを取得できませんでした。基本的な季節にあった服装をおすすめします：天候に合わせてアウターの有無を調整してください。';
    }
  }

  public function suggestClothingJson(array $weatherData, int $userId, ?string $tpo = null): array
  {
    $user = User::findOrFail($userId);

    $prompt = $this->promptBuilder->buildJson($weatherData, $user, $tpo);
    $json = $this->aiClient->getClothingAdviceJson($prompt);

    $excludeConfig = $this->buildExclusionConfig($userId);

    $matchedItems = $this->itemMatcher->matchItemsFromJson(
      $json['items'] ?? [],
      $userId,
      $excludeConfig['colors'],
      $excludeConfig['ids'],
      $tpo,
      now()->toDateString()
    );

    return [
      'category' => 'AIによる提案',
      'advice' => $json,
      'outfit_suggestion' => $matchedItems,
    ];
  }

  /**
   * 前日着用アイテムの除外条件を構築
   */
  private function buildExclusionConfig(int $userId, ?string $cityId = null): array
  {
    $yesterdayKey = Carbon::yesterday()->toDateString();

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
  private function storeResult(int $userId, string $date, array $result, array $matchedItems, ?string $tpo = null, ?string $cityId = null, ?string $profileHash = null): void
  {
    // $this->adviceCache->put($userId, $date, $result, $tpo, $cityId, $profileHash);

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
