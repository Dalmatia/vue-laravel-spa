<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\MainCategory;
use App\Enums\SubCategory;
use App\Enums\Color;
use App\Models\KeywordMapping;
use App\Models\Item;

class ItemMatcher
{
  // テキスト解析結果をもとに、ユーザーのアイテムをマッチングする。
  public function matchItems(string $text, int $userId, array $excludeColorsByCategory = [], array $excludeItemIds = [], ?string $tpo = null): array
  {
    $matchedItems = array_fill_keys(
      [MainCategory::outer, MainCategory::tops, MainCategory::bottoms, MainCategory::shoes],
      null
    );

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

      // TPOフィルター適用
      if ($userItem && !$this->applyTpoFilter($userItem, $tpo)) {
        continue;
      }

      // 柄・アクセントの制御
      $colorTolerance = $this->getColorTolerance($tpo);
      $patternAllowance = $this->getPatternAllowance($tpo);
      if ($userItem && $this->shouldSkipCombination($matchedItems, $userItem, $colorTolerance, $patternAllowance)) {
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

  // TPOに応じたフィルター
  private function applyTpoFilter(Item $item, ?string $tpo): bool
  {
    if (!$tpo) return true;

    $restricted = match ($tpo) {
      'office' => [
        SubCategory::parka,
        SubCategory::sweatshirt,
        SubCategory::sweatPants,
        SubCategory::shorts,
        SubCategory::sandals,
        SubCategory::slippers,
        SubCategory::cap,
        SubCategory::beanie,
      ],
      'date' => [
        SubCategory::sweatPants,
        SubCategory::sandals,
        SubCategory::slippers,
      ],
      'outdoor' => [
        SubCategory::formal_suit,
        SubCategory::heels,
        SubCategory::pumps,
      ],
      'formal' => [
        SubCategory::denimPants,
        SubCategory::parka,
        SubCategory::sweatshirt,
        SubCategory::sandals,
        SubCategory::cap,
      ],
      default => [],
    };

    return !in_array($item->sub_category, $restricted, true);
  }

  // 同色組み合わせを許可するかどうか判定
  private function allowSameColorCombination(?string $tpo, int $color): bool
  {
    $isNeutral = Color::isNeutralColor($color);

    return match ($tpo) {
      'formal' => $isNeutral, // モノトーンOK
      'office' => $isNeutral, // 落ち着いた色味OK
      'date'   => false,      // 同色で沈みすぎるとNG
      'casual' => $isNeutral || $color === Color::denim, // デニム×中立色はOK
      'outdoor' => false,     // 同色だと野暮ったく見えるためNG
      default => false,
    };
  }

  private function shouldSkipCombination(array $matchedItems, Item $candidate, int $patternAllowance = 1, ?string $tpo = null, int $colorTolerance = 1): bool
  {
    $candidateColor = $candidate->color;
    $isCandidatePattern = Color::isPattern($candidateColor);
    $isCandidateAccent = Color::isAccentColor($candidateColor);

    $patternCount = 0;
    $sameColorCount = 0;

    foreach ($matchedItems as $matched) {
      if (!isset($matched['item'])) continue;
      $matchedColor = $matched['item']->color;

      // 同色同士の組み合わせ判定
      if ($matchedColor === $candidateColor) {
        $sameColorCount++;
        // TPOに応じて同色制限
        if (!$this->allowSameColorCombination($tpo, $candidateColor)) {
          return true; // 同色禁止
        }
      }
      // 柄×柄禁止
      if ($isCandidatePattern && Color::isPattern($matchedColor)) continue;
      // 柄×強調色禁止
      if (($isCandidatePattern && Color::isAccentColor($matchedColor)) ||
        (Color::isPattern($matchedColor) && $isCandidateAccent)
      ) continue;
      // 柄×カラー制限
      if ($isCandidatePattern && !Color::isNeutralColor($matchedColor) && $colorTolerance <= 1) continue;

      if (Color::isPattern($matchedColor)) {
        $patternCount++;
      }
    }
    // 柄許容数を超えたらスキップ
    return $isCandidatePattern && $patternCount >= $patternAllowance;
  }

  private function getColorTolerance(?string $tpo): int
  {
    return match ($tpo) {
      'formal' => 0,     // 派手色NG
      'office' => 1,     // 控えめ
      'casual', 'outdoor' => 2, // 柔軟
      'date' => 2,
      default => 1,
    };
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
