<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\MainCategory;
use App\Enums\SubCategory;
use App\Enums\Color;
use App\Enums\Season;
use App\Models\Item;
use Carbon\Carbon;

class FallbackOutfitBuilder
{
  /**
   * null のカテゴリをユーザーの手持ちアイテムで補完する
   */
  public function fillMissingItems(array $matchedItems, int $userId, ?string $tpo = null, ?string $targetDate = null): array
  {
    foreach ($matchedItems as $categoryValue => $data) {
      if (!empty($data['item'])) {
        continue;
      }

      $filled = $this->findBestCandidate(
        $matchedItems,
        $userId,
        $categoryValue,
        $tpo,
        $targetDate
      );

      if ($filled) {
        $matchedItems[$categoryValue] = [
          'source' => 'fallback',
          'item'   => $filled,
        ];
      }
    }

    return $matchedItems;
  }

  /**
   * 指定カテゴリの最適候補を探す
   */
  private function findBestCandidate(array $currentItems, int $userId, int $category, ?string $tpo, ?string $targetDate): ?Item
  {
    $query = Item::where('user_id', $userId)
      ->where('main_category', $category);

    // 季節フィルタ
    $season = $this->seasonFromDate($targetDate);
    $query->where(function ($q) use ($season) {
      $q->whereNull('season')
        ->orWhere('season', 0)
        ->orWhere('season', $season);
    });

    $candidates = $query->get()->shuffle();

    foreach ($candidates as $candidate) {
      if (!$this->applyTpoFilter($candidate, $tpo)) {
        continue;
      }

      if ($this->shouldSkipCombination(
        $currentItems,
        $candidate,
        $this->getColorTolerance($tpo),
        $this->getPatternAllowance($tpo),
        $tpo
      )) {
        continue;
      }

      return $candidate;
    }

    return null;
  }

  /* =========================
       以下は ItemMatcher と同等ロジック
       ========================= */

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

  private function shouldSkipCombination(array $matchedItems, Item $candidate, int $colorTolerance, int $patternAllowance, ?string $tpo): bool
  {
    $candidateColor = $candidate->color;
    $isCandidatePattern = Color::isPattern($candidateColor);
    $isCandidateAccent  = Color::isAccentColor($candidateColor);

    $patternCount = 0;
    $sameColorCount = 0;

    foreach ($matchedItems as $matched) {
      if (!isset($matched['item'])) continue;

      $item = $matched['item'];

      $matchedColor = is_object($item)
        ? $item->color
        : ($item['color'] ?? null);

      if ($matchedColor === null) continue;

      // 同色制御
      if ($matchedColor === $candidateColor) {
        $sameColorCount++;
        if (!$this->allowSameColorCombination($tpo, $candidateColor)) {
          return true;
        }
      }

      // 柄 × 柄
      if ($isCandidatePattern && Color::isPattern($matchedColor)) {
        return true;
      }

      // 柄 × 強調色
      if (
        ($isCandidatePattern && Color::isAccentColor($matchedColor)) ||
        (Color::isPattern($matchedColor) && $isCandidateAccent)
      ) {
        continue;
      }

      // 柄 × カラー制限
      if (
        $isCandidatePattern &&
        !Color::isNeutralColor($matchedColor) &&
        $colorTolerance <= 1
      ) {
        return true;
      }

      if (Color::isPattern($matchedColor)) {
        $patternCount++;
      }
    }

    if ($isCandidatePattern && $patternCount >= $patternAllowance) {
      return true;
    }

    if ($sameColorCount >= 2) {
      return true;
    }

    return false;
  }

  private function allowSameColorCombination(?string $tpo, int $color): bool
  {
    $isNeutral = Color::isNeutralColor($color);

    return match ($tpo) {
      'formal', 'office' => $isNeutral,
      'date'             => false,
      'casual'           => $isNeutral || $color === Color::denim,
      'outdoor'          => false,
      default            => false,
    };
  }

  private function getColorTolerance(?string $tpo): int
  {
    return match ($tpo) {
      'formal'  => 0,
      'office'  => 1,
      'casual', 'outdoor', 'date' => 2,
      default   => 1,
    };
  }

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
      $month >= 3 && $month <= 5  => Season::spring,
      $month >= 6 && $month <= 8  => Season::summer,
      $month >= 9 && $month <= 11 => Season::fall,
      default                     => Season::winter,
    };
  }
}
