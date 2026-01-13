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
}
