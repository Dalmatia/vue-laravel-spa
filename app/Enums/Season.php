<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class Season extends Enum
{
    const spring = 1;
    const summer = 2;
    const fall = 3;
    const winter = 4;

    protected static $labels = [
        self::spring => '春',
        self::summer => '夏',
        self::fall => '秋',
        self::winter => '冬',
    ];

    public static function getDescription($value): string
    {
        return static::$labels[$value] ?? '存在しない季節です';
    }

    public static function getValue($key): ?int
    {
        $keyValueMap = [
            '春' => self::spring,
            '夏' => self::summer,
            '秋' => self::fall,
            '冬' => self::winter,
        ];

        return $keyValueMap[$key] ?? null;
    }

    public static function toSelectArray(): array
    {
        $selectArray = [];

        foreach (static::getValues() as $value) {
            $selectArray[$value] = static::getDescription($value);
        }

        return $selectArray;
    }
}
