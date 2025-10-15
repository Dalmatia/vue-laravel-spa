<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\MainCategory;
use App\Enums\Color;
use App\Models\KeywordMapping;
use App\Models\Item;

class ItemMatcher
{
  /**
   * テキスト解析結果をもとに、ユーザーのアイテムをマッチングする。
   */
  public function matchItems(string $text, int $userId, array $excludeColorsByCategory = [], array $excludeItemIds = [], ?string $tpo = null): array
  {
    $matchedItems = [
      MainCategory::outer => null,
      MainCategory::tops => null,
      MainCategory::bottoms => null,
      MainCategory::shoes => null,
    ];

    $keywords = KeywordMapping::all();

    foreach ($keywords as $kw) {
      if (mb_stripos($text, $kw->keyword) === false) {
        continue;
      }

      $query = Item::where('user_id', $userId);

      if ($kw->main_category) {
        $query->where('main_category', $kw->main_category);
      }
      if ($kw->sub_category) {
        $query->where('sub_category', $kw->sub_category);
      }
      if ($kw->color) {
        $query->where('color', $kw->color);
      }
      if (!empty($excludeItemIds)) {
        $query->whereNotIn('id', $excludeItemIds);
      }

      // カテゴリ別の除外色を適用
      $category = $kw->main_category;
      if (!empty($excludeColorsByCategory[$category])) {
        $query->whereNotIn('color', $excludeColorsByCategory[$category]);
      }

      $userItem = $query->inRandomOrder()->first();

      $patternAllowance = $this->getPatternAllowance($tpo);
      if ($userItem && $this->shouldSkipCombination($matchedItems, $userItem, $patternAllowance)) {
        continue;
      }

      if ($category && !$matchedItems[$category]) {
        $matchedItems[$category] = [
          'keyword' => $kw->keyword,
          'item'    => $userItem,
        ];
      }
    }

    return $matchedItems;
  }

  private function shouldSkipCombination(array $matchedItems, Item $candidate, int $patternAllowance = 1): bool
  {
    $candidateColor = $candidate->color;
    $isCandidatePattern = Color::isPattern($candidateColor);
    $isCandidateAccent = Color::isAccentColor($candidateColor);

    $patternCount = 0;

    foreach ($matchedItems as $matched) {
      if (!isset($matched['item'])) continue;
      $matchedColor = $matched['item']->color;

      if (Color::isPattern($matchedColor)) {
        $patternCount++;
      }

      // 柄×柄禁止
      if ($isCandidatePattern && Color::isPattern($matchedColor)) {
        return true;
      }

      // 柄×強調色禁止（どちらが先でも禁止）
      if (
        ($isCandidatePattern && Color::isAccentColor($matchedColor)) ||
        (Color::isPattern($matchedColor) && $isCandidateAccent)
      ) {
        return true;
      }
    }
    // 柄許容数を超えたらスキップ
    if ($isCandidatePattern && $patternCount >= $patternAllowance) {
      return true;
    }

    return false;
  }

  // 柄許容度の判定メソッドを追加
  private function getPatternAllowance(?string $tpo): int
  {
    return match ($tpo) {
      'office' => 0,
      'casual', 'date' => 1,
      'outdoor' => 2,
      default => 1,
    };
  }
}
