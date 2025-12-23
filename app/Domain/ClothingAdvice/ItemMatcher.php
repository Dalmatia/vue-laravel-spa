<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\MainCategory;
use App\Enums\SubCategory;
use App\Enums\Color;
use App\Enums\Season;
use App\Models\KeywordMapping;
use App\Models\Item;
use Carbon\Carbon;

use function Symfony\Component\Clock\now;

class ItemMatcher
{
  public function matchItemsFromJson(array $itemsByCategory, int $userId, array $excludeColorsByCategory = [], array $excludeItemIds = [], ?string $tpo = null, ?string $targetDate = null): array
  {
    $matchedItems = [
      MainCategory::outer   => null,
      MainCategory::tops    => null,
      MainCategory::bottoms => null,
      MainCategory::shoes   => null,
    ];

    foreach ($itemsByCategory as $categoryKey => $keywords) {
      $categoryValue = $this->resolveMainCategory($categoryKey);
      if (!$categoryValue === null) {
        logger()->warning('Invalid category key from AI', [
          'categoryKey' => $categoryKey,
        ]);
        continue;
      }

      if ($matchedItems[$categoryValue] !== null) {
        continue;
      }

      $query = Item::where('user_id', $userId)
        ->where('main_category', $categoryValue);

      // キーワードベースで絞り込み
      // $query->where(function ($q) use ($keywords) {
      //   foreach ($keywords as $kw) {
      //     $q->orWhereHas(
      //       'keywordMappings',
      //       fn($m) =>
      //       $m->where('keyword', 'LIKE', "%{$kw}%")
      //     );
      //   }
      // });

      if (!empty($excludeItemIds)) {
        $query->whereNotIn('id', $excludeItemIds);
      }

      if (!empty($excludeColorsByCategory[$categoryValue])) {
        $query->whereNotIn('color', $excludeColorsByCategory[$categoryValue]);
      }

      // 季節
      $season = $this->seasonFromDate($targetDate);
      $query->where(function ($q) use ($season) {
        $q->whereNull('season')
          ->orWhere('season', 0)
          ->orWhere('season', $season);
      });

      $candidate = $query->inRandomOrder()->first();

      if (!$candidate) continue;
      if (!$this->applyTpoFilter($candidate, $tpo)) continue;

      if ($this->shouldSkipCombination(
        $matchedItems,
        $candidate,
        $this->getColorTolerance($tpo),
        $this->getPatternAllowance($tpo),
        $tpo
      )) {
        continue;
      }

      $matchedItems[$categoryValue] = [
        'source' => 'json',
        'keywords' => $keywords,
        'item' => $candidate,
      ];
    }

    return $matchedItems;
  }

  private function resolveMainCategory(string $key): ?int
  {
    return match ($key) {
      'outer'   => MainCategory::outer,
      'tops'    => MainCategory::tops,
      'bottoms' => MainCategory::bottoms,
      'shoes'   => MainCategory::shoes,
      default   => null,
    };
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

  private function shouldSkipCombination(array $matchedItems, Item $candidate, int $colorTolerance = 1, int $patternAllowance = 1, ?string $tpo = null): bool
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
      if ($isCandidatePattern && Color::isPattern($matchedColor)) return true;
      // 柄×強調色禁止
      if (($isCandidatePattern && Color::isAccentColor($matchedColor)) ||
        (Color::isPattern($matchedColor) && $isCandidateAccent)
      ) continue;
      // 柄×カラー制限
      if ($isCandidatePattern && !Color::isNeutralColor($matchedColor) && $colorTolerance <= 1) return true;

      if (Color::isPattern($matchedColor)) {
        $patternCount++;
      }
    }
    // 柄許容数を超えたらスキップ
    if ($isCandidatePattern && $patternCount >= $patternAllowance) {
      return true;
    }
    // 同色が多すぎる場合の最終チェック（任意の閾値、ここでは2）
    if ($sameColorCount >= 2) {
      return true;
    }

    return false;
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

  private function seasonFromDate(?string $date): int
  {
    $month = Carbon::parse($date ?? now())->month;

    return match (true) {
      $month >= 3 && $month <= 5 => Season::spring,
      $month >= 6 && $month <= 8 => Season::summer,
      $month >= 9 && $month <= 11 => Season::fall,
      default => Season::winter,
    };
  }
}
