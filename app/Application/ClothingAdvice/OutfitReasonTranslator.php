<?php

namespace App\Application\ClothingAdvice;

use App\Domain\ClothingAdvice\OutfitDecisionReason;

class OutfitReasonTranslator
{
  public static function toUiText(OutfitDecisionReason $reason): string
  {
    return match ($reason) {
      OutfitDecisionReason::GOOD_COLOR_MATCH =>
      '他のアイテムと色の相性が良いため',

      OutfitDecisionReason::TPO_APPROPRIATE =>
      'シーンに適しているため',

      OutfitDecisionReason::SEASON_APPROPRIATE =>
      '季節感に合っているため',

      OutfitDecisionReason::COLOR_CONFLICT =>
      '他のアイテムと色が競合するため',

      OutfitDecisionReason::TPO_MISMATCH =>
      '今回はシーンに合わないため',

      OutfitDecisionReason::SEASON_MISMATCH =>
      '季節感が合わないため',

      OutfitDecisionReason::BETTER_OPTION_SELECTED =>
      'よりバランスの良いアイテムがあるため',
    };
  }
}
