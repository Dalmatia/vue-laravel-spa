<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class MainCategory extends Enum
{
    const outer = 1;
    const tops = 2;
    const bottoms = 3;
    const shoes = 4;
    const accessories = 5;

    protected static $labels = [
        self::outer => 'アウター',
        self::tops => 'トップス',
        self::bottoms => 'ボトムス',
        self::shoes => 'シューズ',
        self::accessories => 'アクセサリー'
    ];

    public static function getDescription($value): string
    {
        return static::$labels[$value] ?? '存在しないメインカテゴリーです';
    }

    public static function getValue($key): ?int
    {
        $keyValueMap = [
            'アウター' => self::outer,
            'トップス' => self::tops,
            'ボトムス' => self::bottoms,
            'シューズ' => self::shoes,
            'アクセサリー' => self::accessories
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
