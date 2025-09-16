<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\MainCategory;
use App\Models\KeywordMapping;
use App\Models\Item;

class ItemMatcher
{
  public function matchItems(string $text, int $userId, array $excludeColorsByCategory = [], array $excludeItemIds = []): array
  {
    $matchedItems = [
      MainCategory::outer => null,
      MainCategory::tops => null,
      MainCategory::bottoms => null,
      MainCategory::shoes => null,
    ];

    $keywords = KeywordMapping::all();

    foreach ($keywords as $kw) {
      if (mb_stripos($text, $kw->keyword) !== false) {
        // ユーザーが持っている該当アイテムを検索
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

        if ($category && !$matchedItems[$category]) {
          $matchedItems[$category] = [
            'keyword' => $kw->keyword,
            'item'    => $userItem,
          ];
        }
      }
    }

    return $matchedItems;
  }
}
