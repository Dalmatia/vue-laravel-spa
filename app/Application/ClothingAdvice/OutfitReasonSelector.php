<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\OutfitDecisionReason;

class OutfitReasonSelector
{
  /**
   * UI表示用に理由を最大2件に絞る
   *
   * @param OutfitDecisionReason[] $reasons
   * @return OutfitDecisionReason[]
   */
  public static function selectForUi(array $reasons): array
  {
    if (empty($reasons)) {
      return [];
    }

    $negative = [];
    $positive = [];

    foreach ($reasons as $reason) {
      if (self::isNegative($reason)) {
        $negative[] = $reason;
      } else {
        $positive[] = $reason;
      }
    }

    // ネガティブ理由があれば最優先
    if (!empty($negative)) {
      return array_slice(
        self::sortByPriority($negative),
        0,
        2
      );
    }

    // ポジティブ理由のみ
    return array_slice(
      self::sortByPriority($positive),
      0,
      2
    );
  }

  /**
   * ネガティブ理由かどうか
   */
  private static function isNegative(OutfitDecisionReason $reason): bool
  {
    return self::priority($reason) <= 3;
  }

  /**
   * 理由を優先度順にソート
   *
   * @param OutfitDecisionReason[] $reasons
   * @return OutfitDecisionReason[]
   */
  private static function sortByPriority(array $reasons): array
  {
    usort($reasons, function ($a, $b) {
      return self::priority($a) <=> self::priority($b);
    });

    return $reasons;
  }

  /**
   * 優先度（数値が小さいほど重要）
   */
  private static function priority(OutfitDecisionReason $reason): int
  {
    return match ($reason) {
      // ミスマッチ
      OutfitDecisionReason::TPO_MISMATCH,
      OutfitDecisionReason::SEASON_MISMATCH => 1,

      // 見た目・組み合わせ
      OutfitDecisionReason::COLOR_CONFLICT => 2,

      // 比較理由（代替専用）
      OutfitDecisionReason::BETTER_OPTION_SELECTED => 3,

      // ポジティブ
      OutfitDecisionReason::TPO_APPROPRIATE,
      OutfitDecisionReason::SEASON_APPROPRIATE,
      OutfitDecisionReason::GOOD_COLOR_MATCH => 4,
    };
  }
}
