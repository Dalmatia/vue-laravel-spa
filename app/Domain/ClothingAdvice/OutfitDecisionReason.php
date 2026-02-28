<?php

namespace App\Domain\ClothingAdvice;

enum OutfitDecisionReason: string
{
    // ポジティブ（採用理由）
    case GOOD_COLOR_MATCH = 'good_color_match';
    case TPO_APPROPRIATE = 'tpo_appropriate';
    case SEASON_APPROPRIATE = 'season_appropriate';

        // ネガティブ（代替理由）
    case COLOR_CONFLICT = 'color_conflict';
    case TPO_MISMATCH = 'tpo_mismatch';
    case SEASON_MISMATCH = 'season_mismatch';
    case BETTER_OPTION_SELECTED = 'better_option_selected';

        // ネガティブ（マッチング失敗理由）
    case NO_MATCH_FOUND = 'no_match_found';

    /**
     * UI表示用ラベル
     */
    public function label(): string
    {
        return match ($this) {
            // OK理由
            self::GOOD_COLOR_MATCH =>
            '色の組み合わせが自然で統一感があります',
            self::TPO_APPROPRIATE =>
            '選択したシーンに適した服装です',
            self::SEASON_APPROPRIATE =>
            '現在の季節に合ったアイテムです',

            // NG / 比較理由
            self::COLOR_CONFLICT =>
            '色の相性があまり良くありません',
            self::TPO_MISMATCH =>
            'シーンに対して不向きな印象になります',
            self::SEASON_MISMATCH =>
            '季節感が合っていません',
            self::BETTER_OPTION_SELECTED =>
            'より条件に合うアイテムが見つかりました',

            self::NO_MATCH_FOUND =>
            '条件に合うアイテムが見つかりませんでした',
        };
    }
}
