<?php

namespace App\Domain\ClothingAdvice;

use App\Enums\Color;
use App\Enums\SubCategory;
use App\Models\Item;

class OutfitRuleEvaluator
{
  public function canUseItem(Item $candidate, array $currentItems, ?string $tpo, int $colorTolerance, int $patternAllowance): bool
  {
    if (!$this->applyTpoFilter($candidate, $tpo)) {
      return false;
    }

    if ($this->shouldSkipCombination(
      $currentItems,
      $candidate,
      $colorTolerance,
      $patternAllowance,
      $tpo
    )) {
      return false;
    }

    return true;
  }

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

      $matchedColor = $matched['item']->color;

      if ($matchedColor === $candidateColor) {
        $sameColorCount++;
        if (!$this->allowSameColorCombination($tpo, $candidateColor)) {
          return true;
        }
      }

      if ($isCandidatePattern && Color::isPattern($matchedColor)) {
        return true;
      }

      if (
        ($isCandidatePattern && Color::isAccentColor($matchedColor)) ||
        (Color::isPattern($matchedColor) && $isCandidateAccent)
      ) {
        continue;
      }

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


  public function getColorTolerance(?string $tpo): int
  {
    return match ($tpo) {
      'formal'  => 0,
      'office'  => 1,
      'casual', 'outdoor', 'date' => 2,
      default   => 1,
    };
  }

  // 柄許容度の判定メソッドを追加
  public function getPatternAllowance(?string $tpo): int
  {
    return match ($tpo) {
      'office' => 0,
      'casual', 'date' => 1,
      'outdoor' => 2,
      default => 1,
    };
  }
}
