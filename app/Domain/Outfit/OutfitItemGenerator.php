<?php

namespace App\Domain\Outfit;

use App\Domain\ClothingAdvice\ItemCategoryPolicy;
use App\Enums\Gender;
use App\Enums\MainCategory;
use App\Enums\Season;
use App\Models\Item;
use App\Models\Outfit;
use App\Models\User;
use Illuminate\Support\Collection;

class OutfitItemGenerator
{
  public function generate(User $user, Outfit $outfit): Collection
  {
    $userId = $user->id;
    $gender = $user->gender;

    // ===== 基本アイテム =====
    $tops = $this->getItem($userId, MainCategory::tops, $gender);
    $bottoms = $this->getItem($userId, MainCategory::bottoms, $gender);
    $shoes = $this->getItem($userId, MainCategory::shoes, $gender);

    $items = collect([$tops, $bottoms, $shoes])->filter();

    // ===== outer =====
    if ($this->shouldAddOuter($outfit->season)) {
      $outer = $this->getItem($userId, MainCategory::outer, $gender);

      if ($outer) {
        $items->push($outer);
      }
    }

    // ===== accessories =====
    if ($this->shouldAddAccessory()) {
      $accessory = $this->getItem($userId, MainCategory::accessories, $gender);

      if ($accessory) {
        $items->push($accessory);
      }
    }

    return $items;
  }

  private function getItem(int $userId, int $mainCategory, ?Gender $gender): ?Item
  {
    $query = Item::where('user_id', $userId)
      ->where('main_category', $mainCategory);

    if ($gender) {
      // allowed優先
      $allowed = ItemCategoryPolicy::getAllowed($mainCategory, $gender);
      if ($allowed) {
        $query->whereIn('sub_category', $allowed);
      } else {
        // excluded適用
        $excluded = ItemCategoryPolicy::getExcluded($mainCategory, $gender);
        if (!empty($excluded)) {
          $query->whereNotIn('sub_category', $excluded);
        }
      }
    }

    return $query->inRandomOrder()->first();
  }

  private function shouldAddOuter(?int $season): bool
  {
    $probability = match ($season) {
      Season::summer => 0,
      Season::spring => 40,
      Season::fall => 60,
      Season::winter => 90,
      default => 50,
    };

    return rand(1, 100) <= $probability;
  }

  private function shouldAddAccessory(): bool
  {
    return rand(0, 100) < 30;
  }
}
