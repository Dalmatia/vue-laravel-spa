<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Gender extends Enum
{
    const NotSet = 0;
    const Male = 1;
    const Female = 2;
    const Kids = 3;

    // ラベルを返す（必要に応じて）
    public function label(): string
    {
        return match ($this->value) {
            self::NotSet => '指定なし',
            self::Male => '男性',
            self::Female => '女性',
            self::Kids => 'キッズ',
            default => parent::getDescription($this->value),
        };
    }

    public static function values(): array
    {
        return self::getValues();
    }
}
